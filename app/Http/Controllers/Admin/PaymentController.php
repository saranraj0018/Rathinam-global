<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentController extends Controller
{
    public function index()
    {
        $student = Auth::guard('student')->user();
        $slotId  = session('selected_slot_id');

        if (!$slotId) {
            return redirect()->route('account_slots')
                ->with('error', 'Please select a slot first.');
        }

        $slot = Slot::with('exam')->findOrFail($slotId);
        $payment = Payment::where('student_id', $student->id)
            ->where('slot_id', $slotId)
            ->where('status', 'pending')
            ->latest()
            ->first();
        if (!$payment) {
            $orderId = 'ORD_' . strtoupper(uniqid());
            $payment = Payment::create([
                'order_id' => $orderId,
                'amount'   => 1,
                'exam_id'  => $slot->exam->id,
                'slot_id'  => $slot->id,
                'student_id'  => $student->id,
                'status'   => 'pending',
                'type'     => 'exam',
            ]);
        }

        $this->data['slot']     = $slot;
        $this->data['order_id'] = $payment->order_id;

        return view('frontend.payment')->with($this->data);
    }

    public function initiate(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:payments,order_id',
        ]);

        $payment = Payment::where('order_id', $request->order_id)->first();
        $student = Auth::guard('student')->user();

        $mode = str_contains(config('services.cashfree.base_url'), 'sandbox')
            ? 'sandbox'
            : 'production';

        $phone = preg_replace('/\D/', '', $student->phone ?? '9999999999');
        if (strlen($phone) === 12 && str_starts_with($phone, '91')) {
            $phone = substr($phone, 2);
        }
        if (strlen($phone) !== 10) {
            $phone = '9999999999';
        }

        // Always create a FRESH Cashfree order — never reuse old session_id
        // Cashfree session IDs expire quickly and cause "payment_session_id_invalid"
        // Generate a new unique order_id for each initiation attempt
        $newOrderId = 'ORD_' . strtoupper(uniqid());

        // Update the payment record with the new order_id and clear old gateway response
        $payment->update([
            'order_id'         => $newOrderId,
            'gateway_response' => null,
        ]);

        $payload = [
            "order_id"       => $newOrderId,
            "order_amount"   => (float) number_format($payment->amount, 2, '.', ''),
            "order_currency" => "INR",
            "customer_details" => [
                "customer_id"    => "STU_" . (string) $student->id,
                "customer_name"  => $student->full_name ?? "Student",
                "customer_email" => $student->email     ?? "student@example.com",
                "customer_phone" => $phone,
            ],
            "order_meta" => [
                "return_url" => str_replace('http://', 'https://', route('payment.success')) . "?order_id={order_id}",
                "notify_url" => str_replace('http://', 'https://', route('payment.webhook')),
            ],
        ];

        $response = $this->cashfreeHttp()->post(
            config('services.cashfree.base_url') . '/orders',
            $payload
        );

        Log::info('Cashfree initiate', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        if (!$response->successful()) {
            return back()->with('error', 'Payment initiation failed: ' . $response->json('message', $response->body()));
        }

        $data = $response->json();

        if (empty($data['payment_session_id'])) {
            return back()->with('error', 'Payment session could not be created. Please try again.');
        }

        // Save fresh session to DB
        $payment->update([
            'gateway_response' => json_encode($data),
            'status'           => 'pending',
        ]);

        return view('frontend.cashfree-checkout', [
            'sessionId' => $data['payment_session_id'],
            'orderId'   => $newOrderId,
            'mode'      => $mode,
        ]);
    }

    public function webhook(Request $request)
    {
        Log::info('Cashfree webhook received', $request->all());

        $data = $request->all();

        if (
            isset($data['data']['payment']['payment_status']) &&
            $data['data']['payment']['payment_status'] === 'SUCCESS'
        ) {
            $orderId = $data['data']['order']['order_id'];
            $payment = Payment::where('order_id', $orderId)->first();

            if ($payment && $payment->status !== 'success') {
                $payment->update([
                    'status'         => 'success',
                    'transaction_id' => $data['data']['payment']['cf_payment_id'] ?? null,
                ]);

                $student = \App\Models\Student::find($payment->user_id);
                if ($student) {
                    SlotBooking::firstOrCreate(
                        [
                            'student_id' => $student->id,
                            'slot_id'    => $payment->slot_id,
                        ],
                        [
                            'status'       => 'confirmed',
                            'reserved_at'  => now(),
                            'confirmed_at' => now(),
                            'expires_at'   => null,
                        ]
                    );
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    public function success(Request $request)
    {
        $orderId = $request->order_id;
        $payment = Payment::where('order_id', $orderId)->first();
        $student = Auth::guard('student')->user();

        $response = $this->cashfreeHttp()
            ->get(config('services.cashfree.base_url') . "/orders/{$orderId}/payments");

        $data = $response->json();

        Log::info('Cashfree success verify', [
            'order_id' => $orderId,
            'response' => $data,
        ]);

        if (!empty($data[0]) && $data[0]['payment_status'] === 'SUCCESS') {

            $payment->update([
                'status'         => 'success',
                'transaction_id' => $data[0]['cf_payment_id'] ?? null,
            ]);

            SlotBooking::firstOrCreate(
                [
                    'student_id' => $student->id,
                    'slot_id'    => $payment->slot_id,
                ],
                [
                    'status'       => 'confirmed',
                    'confirmed_at' => now(),
                    'expires_at'   => null,
                ]
            );

            return redirect()->route('registration_confirm')
                ->with('success', 'Payment successful');
        }

        return redirect('/payment')->with('error', 'Payment failed or pending.');
    }

    private function cashfreeHttp()
    {
        $http = Http::withHeaders([
            'x-client-id'     => config('services.cashfree.app_id'),
            'x-client-secret' => config('services.cashfree.secret_key'),
            'x-api-version'   => '2022-09-01',
            'Content-Type'    => 'application/json',
            'Accept'          => 'application/json',
        ]);

        if (app()->environment('local')) {
            $http = $http->withoutVerifying();
        }

        return $http;
    }
}
