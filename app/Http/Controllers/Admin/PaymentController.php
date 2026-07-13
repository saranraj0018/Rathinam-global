<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Mail\ApplicationSubmitted;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PaymentController extends Controller
{
    public function paymentApplication(Request $request, string $payment_id)
    {
        $user = Auth::guard('user')->user();

        $payment = Payment::where('payment_id', $payment_id)
            ->where('user_id', $user->id)
            ->first();
        if ($payment->status === 'success') {
            return redirect()
                ->route('scholar.create')
                ->with('payment_success', true);
        }
        $app   = Application::find($payment->application_id);
        $phone = $this->normalizePhone(
            ($app->mobile ?? null) ?: ($user->mobile ?? null) ?: ($user->phone ?? null)
        );

        if (!$phone) {
            return redirect()
                ->route('scholar.create')
                ->with('payment_failed', 'A valid 10-digit mobile number (starting 6-9) is required before payment. Please update it in your application.');
        }

        // Prevent concurrent double-clicks / duplicate tabs from racing
        // each other into the same order-create call.
        $lockKey = "cashfree-order-lock:{$payment->id}";
        $lock    = Cache::lock($lockKey, 15);

        if (!$lock->get()) {
            return redirect()
                ->route('scholar.create')
                ->with('payment_failed', 'Your payment is already being processed. Please wait a moment and refresh.');
        }

        try {
            $baseUrl   = rtrim(config('services.cashfree.base_url'), '/');
            $sessionId = $this->resolveSession($payment, $app, $user, $phone, $baseUrl);

            if (!$sessionId) {
                return redirect()
                    ->route('scholar.create')
                    ->with('payment_failed', 'Could not start payment. Please try again.');
            }

            // Refresh in case the row's payment_id changed inside resolveSession()
            $payment->refresh();

            if ($payment->status === 'success') {
                return redirect()
                    ->route('scholar.create')
                    ->with('payment_success', true);
            }

            return view('scholar.payment', [
                'paymentSessionId' => $sessionId,
                'orderId'          => $payment->payment_id,
                'mode'             => $this->cashfreeMode(),
            ]);
        } finally {
            $lock->release();
        }
    }

    public function initiatePayment(Request $request)
    {
        try {
            $user = Auth::guard('user')->user();
            $app = Application::where('user_id', $user->id)
                ->where('status', 'draft')
                ->latest('id')
                ->first();
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
            Log::info($payment->payment_id);
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

    /** Called by payment gateway after success (return_url). Best-effort UX path. */
    public function paymentSuccess(Request $request, string $payment_id)
    {
        $payment = Payment::where('payment_id', $payment_id)->first();

        if ($payment->status === 'success') {
            return redirect()->route('scholar.create')->with('payment_success', true);
        }

        $response = $this->cashfreeHttp()
            ->get(rtrim(config('services.cashfree.base_url'), '/') . "/orders/{$payment_id}/payments");
        $data = $response->json();

        if (!empty($data[0]) && ($data[0]['payment_status'] ?? null) === 'SUCCESS') {
            $this->markPaymentSuccess($payment, $data[0]['cf_payment_id'] ?? null);
            return redirect()->route('scholar.create')->with('payment_success', true);
        }

        Log::warning('Payment not successful on verify', [
            'payment_id' => $payment_id,
            'response'   => $data,
        ]);

        return redirect()->route('scholar.create')->with('payment_failed', true);
    }

    /**
     * Server-to-server webhook from Cashfree. This is the reliable path —
     * unlike return_url, it fires even if the user closes the tab or the
     * redirect never completes, so it should be the source of truth.
     *
     * Register this URL in your Cashfree dashboard webhook settings and
     * add CASHFREE_WEBHOOK_SECRET to your .env / config/services.php.
     */
    public function webhook(Request $request)
    {
        $signature = $request->header('x-webhook-signature');
        $timestamp = $request->header('x-webhook-timestamp');
        $rawBody   = $request->getContent();

        $expected = base64_encode(hash_hmac(
            'sha256',
            $timestamp . $rawBody,
            config('services.cashfree.webhook_secret'),
            true
        ));

        if (!$signature || !hash_equals($expected, $signature)) {
            Log::warning('Cashfree webhook signature mismatch', ['payment_id' => $request->input('data.order.order_id')]);
            return response()->json(['status' => 'invalid signature'], 400);
        }

        $payload   = $request->json()->all();
        $eventType = $payload['type'] ?? null;
        $orderId   = $payload['data']['order']['order_id'] ?? null;
        $cfPayment = $payload['data']['payment']['cf_payment_id'] ?? null;
        $status    = $payload['data']['payment']['payment_status'] ?? null;

        if (!$orderId) {
            return response()->json(['status' => 'ignored, no order_id'], 200);
        }

        $payment = Payment::where('payment_id', $orderId)->first();

        if (!$payment) {
            Log::warning('Cashfree webhook for unknown payment_id', ['payment_id' => $orderId]);
            return response()->json(['status' => 'unknown payment_id'], 200);
        }

        if ($eventType === 'PAYMENT_SUCCESS_WEBHOOK' && $status === 'SUCCESS') {
            $this->markPaymentSuccess($payment, $cfPayment);
        }

        // Always 200 so Cashfree doesn't keep retrying a webhook we've already handled.
        return response()->json(['status' => 'ok'], 200);
    }

    /* ───────────────────────── helpers ───────────────────────── */

    /**
     * Resolve a usable payment_session_id for this Payment row.
     * Self-heals if the previous Cashfree order is stale, paid, or expired,
     * instead of blindly retrying a create against a burned order_id.
     */
    private function resolveSession(Payment $payment, ?Application $app, $user, string $phone, string $baseUrl): ?string
    {
        $existing = $this->cashfreeHttp()->get($baseUrl . "/orders/{$payment->payment_id}");

        if ($existing->successful()) {
            $data   = $existing->json();
            $status = $data['order_status'] ?? null;

            if ($status === 'ACTIVE' && !empty($data['payment_session_id'])) {
                return $data['payment_session_id'];
            }

            if ($status === 'PAID') {
                // Order succeeded at Cashfree but our webhook/return_url never
                // landed. Reconcile now instead of erroring out.
                $paymentsResp = $this->cashfreeHttp()->get($baseUrl . "/orders/{$payment->payment_id}/payments");
                $paymentsData = $paymentsResp->json();
                $cfPaymentId  = $paymentsData[0]['cf_payment_id'] ?? null;

                $this->markPaymentSuccess($payment, $cfPaymentId);
                return null; // caller checks $payment->status === 'success' after refresh
            }

            // EXPIRED / TERMINATED / anything else: this order_id can never
            // be reused. Mint a fresh one rather than retrying the same ID.
            return $this->createOrder($payment, $user, $phone, $baseUrl, true);
        }

        // 404 — no order exists yet under this ID, safe to create for the first time.
        return $this->createOrder($payment, $user, $phone, $baseUrl, false);
    }

    private function createOrder(Payment $payment, $user, string $phone, string $baseUrl, bool $mintNewId): ?string
    {
        $orderId = $mintNewId
            ? $payment->payment_id . '_R' . now()->timestamp
            : $payment->payment_id;

        $cf = $this->cashfreeHttp()->post($baseUrl . '/orders', [
            'order_id'       => $orderId,
            'order_amount'   => (float) $payment->amount,
            'order_currency' => 'INR',
            'customer_details' => [
                'customer_id'    => (string) $user->id,
                'customer_name'  => $user->name ?? 'Applicant',
                'customer_email' => $user->email ?? 'noemail@example.com',
                'customer_phone' => $phone,
            ],
            'order_meta' => [
                'return_url' => route('payment.success', ['payment_id' => $orderId]),
            ],
        ]);

        $data = $cf->json();

        // Defensive: even after the checks above, a race (two tabs) could still
        // hit order_already_exists. Don't just fail — re-check the order once.
        if (!$cf->successful() && ($data['code'] ?? null) === 'order_already_exists') {
            Log::warning('Cashfree order_already_exists on create, re-checking order state', [
                'order_id' => $orderId,
            ]);

            $recheck = $this->cashfreeHttp()->get($baseUrl . "/orders/{$orderId}");
            if ($recheck->successful()) {
                $recheckData = $recheck->json();
                if (($recheckData['order_status'] ?? null) === 'ACTIVE' && !empty($recheckData['payment_session_id'])) {
                    if ($mintNewId) {
                        $payment->update(['payment_id' => $orderId]);
                    }
                    return $recheckData['payment_session_id'];
                }
            }

            Log::error('Cashfree order create failed after retry', [
                'payment_id' => $orderId,
                'status'     => $cf->status(),
                'response'   => $data,
            ]);
            return null;
        }

        if (!$cf->successful() || empty($data['payment_session_id'])) {
            Log::error('Cashfree order create failed', [
                'payment_id' => $orderId,
                'status'     => $cf->status(),
                'response'   => $data,
            ]);
            return null;
        }

        if ($mintNewId) {
            // Keep the Payment row's identifier in sync with the order_id
            // that's actually live at Cashfree, so future lookups match.
            $payment->update(['payment_id' => $orderId]);
        }

        return $data['payment_session_id'];
    }

    /**
     * Single source of truth for marking a payment + application as paid.
     * Used by paymentSuccess(), the webhook, and self-heal in resolveSession()
     * so all three paths stay identical and idempotent.
     */
    private function markPaymentSuccess(Payment $payment, ?string $cfPaymentId): void
    {
        if ($payment->status === 'success') {
            return; // already handled — keep this idempotent for webhook retries
        }

        $payment->update([
            'status'         => 'success',
            'transaction_id' => $cfPaymentId,
        ]);

        $app = Application::find($payment->application_id);
        if (!$app) {
            return;
        }

        $completed = $app->completed_steps ?? [];
        if (!in_array('payment', $completed)) {
            $completed[] = 'payment';
        }
        $app->completed_steps = $completed;
        $app->current_step    = 'preview';
        $app->payment_status  = 'paid';
        $app->status          = 'submitted';
        $app->submitted_at    = now();
        $app->save();

        try {
            Mail::to($app->email)
                // ->cc('admissions@rgu.ac.in')
                ->send(new ApplicationSubmitted($app));
        } catch (Throwable $e) {
            Log::error('Application submission email failed', [
                'application_id' => $app->id,
                'email'          => $app->email,
                'error'          => $e->getMessage(),
            ]);
        }
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

    private function cashfreeMode(): string
    {
        $base = (string) config('services.cashfree.base_url');
        return str_contains($base, 'sandbox') ? 'sandbox' : 'production';
    }

    private function normalizePhone(?string $phone): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        }
        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        return preg_match('/^[6-9]\d{9}$/', $digits) ? $digits : null;
    }
}
