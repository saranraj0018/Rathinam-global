<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    /**
     * Renders the Cashfree checkout page for a pending application payment.
     * Reached via route('payment.application', ['payment_id' => ...]).
     */
    public function paymentApplication(Request $request, string $payment_id)
    {
        $user = Auth::guard('user')->user();
        abort_unless($user, 401);

        // Load the payment and make sure it belongs to this user.
        $payment = Payment::where('payment_id', $payment_id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Already paid? Don't re-charge — send back to the application.
        if ($payment->status === 'success') {
            return redirect()
                ->route('scholar.create')
                ->with('payment_success', true);
        }

        // Resolve the applicant's phone: application first, then user.
        $app   = Application::find($payment->application_id);
        $phone = $this->normalizePhone(
            ($app->mobile ?? null) ?: ($user->mobile ?? null) ?: ($user->phone ?? null)
        );

        if (!$phone) {
            return redirect()
                ->route('scholar.create')
                ->with('payment_failed', 'A valid 10-digit mobile number (starting 6-9) is required before payment. Please update it in your application.');
        }

        $baseUrl = rtrim(config('services.cashfree.base_url'), '/');

        // 1) Reuse an existing ACTIVE Cashfree order for this id, if any.
        $sessionId = null;

        $existing = $this->cashfreeHttp()->get($baseUrl . "/orders/{$payment->payment_id}");
        if ($existing->successful()) {
            $existingData = $existing->json();
            if (
                !empty($existingData['payment_session_id']) &&
                ($existingData['order_status'] ?? null) === 'ACTIVE'
            ) {
                $sessionId = $existingData['payment_session_id'];
            }
        }

        // 2) Otherwise create a fresh Cashfree order.
        if (!$sessionId) {
            $cf = $this->cashfreeHttp()->post($baseUrl . '/orders', [
                'order_id'       => $payment->payment_id,   // Cashfree's required field name
                'order_amount'   => (float) $payment->amount,
                'order_currency' => 'INR',
                'customer_details' => [
                    'customer_id'    => (string) $user->id,
                    'customer_name'  => $user->name ?? 'Applicant',
                    'customer_email' => $user->email ?? 'noemail@example.com',
                    'customer_phone' => $phone,
                ],
                'order_meta' => [
                    'return_url' => route('payment.success', ['payment_id' => $payment->payment_id]),
                ],
            ]);

            $data = $cf->json();

            if (!$cf->successful() || empty($data['payment_session_id'])) {
                Log::error('Cashfree order create failed', [
                    'payment_id' => $payment->payment_id,
                    'status'     => $cf->status(),
                    'response'   => $data,
                ]);
                return redirect()
                    ->route('scholar.create')
                    ->with('payment_failed', 'Could not start payment. Please try again.');
            }

            $sessionId = $data['payment_session_id'];
        }

        return view('scholar.payment', [
            'paymentSessionId' => $sessionId,
            'orderId'          => $payment->payment_id,
            'mode'             => $this->cashfreeMode(),
        ]);
    }

    public function initiatePayment(Request $request)
    {
        try {
            $user = Auth::guard('user')->user();
            abort_unless($user, 401);

            $app = Application::where('user_id', $user->id)
                ->where('status', 'draft')
                ->latest('id')
                ->first();

            abort_unless($app, 404, 'No application found.');

            $app->current_step   = 'payment';
            $app->payment_status = 'payment_pending';
            $app->save();

            $payment = Payment::where('application_id', $app->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            if (!$payment) {
                $payment = Payment::create([
                    'payment_id'     => 'APPL_' . strtoupper(uniqid()),
                    'amount'         => 2000,
                    'application_id' => $app->id,
                    'user_id'        => $user->id,
                    'status'         => 'pending',
                    'type'           => 'application',
                ]);
            }

            return response()->json([
                'success'    => true,
                'payment_id' => $payment->payment_id,
                'redirect'   => route('payment.application', ['payment_id' => $payment->payment_id]),
            ]);
        } catch (\Throwable $e) {
            Log::error('initiatePayment failed', ['msg' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),   // remove in production
            ], 500);
        }
    }

    /** Called by payment gateway after success (return_url). */
    public function paymentSuccess(Request $request, string $payment_id)
    {
        $payment = Payment::where('payment_id', $payment_id)->first();
        abort_unless($payment, 404);

        $response = $this->cashfreeHttp()
            ->get(rtrim(config('services.cashfree.base_url'), '/') . "/orders/{$payment_id}/payments");
        $data = $response->json();

        if (!empty($data[0]) && ($data[0]['payment_status'] ?? null) === 'SUCCESS') {
            $payment->update([
                'status'         => 'success',
                'transaction_id' => $data[0]['cf_payment_id'] ?? null,
            ]);

            $app = Application::find($payment->application_id);
            if ($app) {
                $completed = $app->completed_steps ?? [];
                if (!in_array('payment', $completed)) {
                    $completed[] = 'payment';
                }
                $app->completed_steps = $completed;
                $app->current_step    = 'preview';
                $app->payment_status  = 'paid';
                $app->status          = 'draft'; // still draft until final submit
                $app->save();
            }

            return redirect()->route('scholar.create')->with('payment_success', true);
        }

        Log::warning('Payment not successful on verify', [
            'payment_id' => $payment_id,
            'response'   => $data,
        ]);

        return redirect()->route('scholar.create')->with('payment_failed', true);
    }

    /* ───────────────────────── helpers ───────────────────────── */

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

    /**
     * Derive the SDK mode from the configured base_url so it can never
     * disagree with the credentials/endpoint actually in use.
     */
    private function cashfreeMode(): string
    {
        $base = (string) config('services.cashfree.base_url');
        return str_contains($base, 'sandbox') ? 'sandbox' : 'production';
    }

    /**
     * Clean and validate an Indian mobile number.
     * Returns a 10-digit string starting 6-9, or null if invalid.
     */
    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        // strip a leading country code / trunk prefix
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return preg_match('/^[6-9]\d{9}$/', $digits) ? $digits : null;
    }
}
