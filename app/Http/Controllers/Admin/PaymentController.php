<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Mail\ApplicationSubmitted;
use Illuminate\Support\Facades\Mail;
use Throwable;

class PaymentController extends Controller
{
    /**
     * Step 1 (AJAX from the Pay Now button): make sure a pending Payment row
     * exists, remember it in the session and hand back the Razorpay Payment
     * Page URL to redirect to.
     */
    public function initiatePayment(Request $request)
    {
        try {
            $user = Auth::guard('user')->user();
            $app = Application::where('user_id', $user->id)
                ->where('status', 'draft')
                ->latest('id')
                ->first();
            Log::info('[PAY] initiatePayment: start', ['user_id' => $user->id, 'application_id' => $app->id ?? null]);
            if (!$app) {
                Log::error('[PAY] initiatePayment FAILED: no draft application for user', ['user_id' => $user->id]);
                return response()->json(['success' => false, 'message' => 'No draft application found.'], 422);
            }
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
                    'amount'         => 1, // keep in sync with the amount set on the Razorpay Payment Page
                    'application_id' => $app->id,
                    'user_id'        => $user->id,
                    'status'         => 'pending',
                    'type'           => 'application',
                ]);
            }

            session(['pending_form_payment_id' => $payment->payment_id]);

            // Preferred: a per-application Razorpay Payment Link whose callback_url
            // brings the browser back to us (so the payment can be verified and the
            // "Payment confirmed" popup shown). Falls back to the static page.
            $pageUrl = $this->createRazorpayPaymentLink($payment, $app, $user)
                ?? (string) config('services.razorpay.payment_page_url');
            Log::info('[PAY] initiatePayment: redirecting to Razorpay', [
                'payment_id' => $payment->payment_id,
                'url'        => $pageUrl,
            ]);

            return response()->json([
                'success'    => true,
                'payment_id' => $payment->payment_id,
                'redirect'   => $pageUrl,
            ]);
        } catch (\Throwable $e) {
            Log::error('initiatePayment failed', ['msg' => $e->getMessage(), 'line' => $e->getLine()]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),   // remove in production
            ], 500);
        }
    }

    /**
     * Step 2: the browser comes back from the Razorpay Payment Page.
     * Set this URL as the page's redirect URL in the Razorpay dashboard.
     *
     * Nothing is trusted from the query string except razorpay_payment_id,
     * and that is verified server-to-server against the Razorpay API
     * (captured + amount + not already used) before anything is marked paid.
     */
    public function razorpayReturn(Request $request)
    {
        $user      = Auth::guard('user')->user();
        $paymentId = session('pending_form_payment_id');
        Log::info('[PAY] razorpayReturn: start', [
            'user_id'    => $user->id ?? null,
            'session_id' => $paymentId,
            'query'      => $request->only(['razorpay_payment_id', 'razorpay_payment_page_id', 'razorpay_payment_link_id', 'razorpay_payment_link_status']),
        ]);

        // Payment Link callbacks carry our reference ("APPL_xxx-<ts>"); prefer it.
        $ref = (string) $request->input('razorpay_payment_link_reference_id', '');
        if ($ref !== '') {
            $paymentId = explode('-', $ref)[0];
        }

        $payment = $paymentId
            ? Payment::where('payment_id', $paymentId)->where('user_id', $user->id)->first()
            : null;

        if (!$payment) {
            Log::warning('[PAY] razorpayReturn: no pending payment found');
            return redirect()->route('scholar.create');
        }

        if ($payment->status === 'success') {
            session()->forget('pending_form_payment_id');
            return $this->thankYouRedirect($payment);
        }

        $rzpPaymentId = (string) ($request->input('razorpay_payment_id') ?? '');
        if ($rzpPaymentId === '') {
            // Razorpay did not send an id: we cannot verify. The webhook will
            // complete the payment if it really succeeded.
            Log::warning('[PAY] razorpayReturn: no razorpay_payment_id in redirect; waiting for webhook');
            return redirect()->route('scholar.create')
                ->with('payment_failed', 'We could not confirm your payment yet. If money was deducted, it will be updated shortly.');
        }

        // Payment Link callback: verify Razorpay's HMAC signature first.
        if ($ref !== '' && !$this->validPaymentLinkSignature($request)) {
            Log::error('[PAY] razorpayReturn: payment link signature invalid');
            return redirect()->route('scholar.create')
                ->with('payment_failed', 'Payment could not be verified. Please contact support.');
        }

        if ($this->verifyRazorpayPayment($payment, $rzpPaymentId)) {
            $this->markPaymentSuccess($payment, $rzpPaymentId);
            session()->forget('pending_form_payment_id');
            return $this->thankYouRedirect($payment);
        }

        return redirect()->route('scholar.create')
            ->with('payment_failed', 'Payment could not be verified. Please try again or contact support.');
    }

    /** Payment done (the application is already submitted): show the Thank You page. */
    private function thankYouRedirect(Payment $payment)
    {
        $payment->refresh();
        $ref = optional(Application::find($payment->application_id))->application_no;

        return redirect()->route('scholar.thankyou')->with('ref', $ref);
    }

    /**
     * Server-to-server webhook from Razorpay (the reliable path).
     * Dashboard: Settings -> Webhooks -> URL https://<domain>/payment/razorpay-webhook,
     * events: payment.captured (and payment_page.paid), secret = RAZORPAY_WEBHOOK_SECRET.
     *
     * Payment Pages do not carry our payment_id, so the payer is matched by the
     * e-mail / mobile they typed on the page plus the amount.
     */
    public function webhook(Request $request)
    {
        $raw       = $request->getContent();
        $signature = (string) $request->header('X-Razorpay-Signature');
        $expected  = hash_hmac('sha256', $raw, (string) config('services.razorpay.webhook_secret'));

        if ($signature === '' || !hash_equals($expected, $signature)) {
            Log::warning('[PAY] Razorpay webhook signature mismatch');
            return response()->json(['ok' => false], 400);
        }

        $event  = $request->input('event');
        $entity = $request->input('payload.payment.entity', []);
        Log::info('[PAY] Razorpay webhook', ['event' => $event, 'payment' => $entity['id'] ?? null, 'status' => $entity['status'] ?? null]);

        if (!in_array($event, ['payment.captured', 'payment_page.paid', 'payment_link.paid', 'order.paid'], true)
            || ($entity['status'] ?? null) !== 'captured') {
            return response()->json(['ok' => true]); // not interesting; ack so it is not retried
        }

        $linkRef = (string) $request->input('payload.payment_link.entity.reference_id', '');
        $payment = $linkRef !== ''
            ? Payment::where('payment_id', explode('-', $linkRef)[0])->where('status', 'pending')->first()
            : $this->findPendingPaymentForRazorpay($entity);
        if (!$payment) {
            Log::warning('[PAY] Razorpay webhook: no matching pending payment', [
                'email'   => $entity['email'] ?? null,
                'contact' => $entity['contact'] ?? null,
                'amount'  => $entity['amount'] ?? null,
            ]);
            return response()->json(['ok' => true]);
        }

        if ($this->razorpayIdAlreadyUsed($entity['id'] ?? '', $payment->id)) {
            return response()->json(['ok' => true]);
        }

        $this->markPaymentSuccess($payment, $entity['id'] ?? null);
        return response()->json(['ok' => true]);
    }

    /** Create a Razorpay Payment Link for this payment; null if keys missing or the call fails. */
    private function createRazorpayPaymentLink(Payment $payment, Application $app, $user): ?string
    {
        $keyId  = (string) config('services.razorpay.key_id');
        $secret = (string) config('services.razorpay.key_secret');
        if ($keyId === '' || $secret === '') {
            Log::warning('[PAY] RAZORPAY_KEY_ID/SECRET not set; using static payment page (no redirect back / no verification)');
            return null;
        }

        $customer = array_filter([
            'name'    => $app->full_name ?? ($user->name ?? null),
            'email'   => $app->email ?? ($user->email ?? null),
            'contact' => $this->normalizePhone($app->mobile ?? ($user->phone ?? null)),
        ]);

        $resp = Http::withBasicAuth($keyId, $secret)->acceptJson()->post('https://api.razorpay.com/v1/payment_links', [
            'amount'          => (int) round(((float) $payment->amount) * 100),
            'currency'        => 'INR',
            'accept_partial'  => false,
            'reference_id'    => $payment->payment_id . '-' . time(),
            'description'     => 'Ph.D Application Fee',
            'customer'        => $customer,
            'notify'          => ['sms' => false, 'email' => false],
            'reminder_enable' => false,
            'callback_url'    => route('payment.razorpay.return'),
            'callback_method' => 'get',
        ]);
        $data = $resp->json();
        Log::info('[PAY] createRazorpayPaymentLink', ['http' => $resp->status(), 'body' => $data ?? $resp->body()]);

        return ($resp->successful() && !empty($data['short_url'])) ? $data['short_url'] : null;
    }

    /** Razorpay signs: payment_link_id|reference_id|status|payment_id with the key secret. */
    private function validPaymentLinkSignature(Request $request): bool
    {
        $payload = implode('|', [
            (string) $request->input('razorpay_payment_link_id'),
            (string) $request->input('razorpay_payment_link_reference_id'),
            (string) $request->input('razorpay_payment_link_status'),
            (string) $request->input('razorpay_payment_id'),
        ]);
        $expected = hash_hmac('sha256', $payload, (string) config('services.razorpay.key_secret'));

        return hash_equals($expected, (string) $request->input('razorpay_signature'))
            && $request->input('razorpay_payment_link_status') === 'paid';
    }

    /** GET /v1/payments/{id} and confirm it really paid this application's fee. */
    private function verifyRazorpayPayment(Payment $payment, string $rzpPaymentId): bool
    {
        if ($this->razorpayIdAlreadyUsed($rzpPaymentId, $payment->id)) {
            Log::warning('[PAY] razorpay payment id already used by another payment', ['rzp' => $rzpPaymentId]);
            return false;
        }

        $resp = Http::withBasicAuth(
            (string) config('services.razorpay.key_id'),
            (string) config('services.razorpay.key_secret')
        )->acceptJson()->get('https://api.razorpay.com/v1/payments/' . urlencode($rzpPaymentId));

        $data = $resp->json();
        Log::info('[PAY] verifyRazorpayPayment', [
            'http' => $resp->status(),
            'body' => $data ?? $resp->body(),
        ]);

        if (!$resp->successful() || !is_array($data)) {
            return false;
        }

        $expectedPaise = (int) round(((float) $payment->amount) * 100);

        $ok = ($data['status'] ?? null) === 'captured'
            && ($data['currency'] ?? 'INR') === 'INR'
            && (int) ($data['amount'] ?? 0) >= $expectedPaise;

        if (!$ok) {
            Log::error('[PAY] verifyRazorpayPayment FAILED checks', [
                'status'        => $data['status'] ?? null,
                'amount'        => $data['amount'] ?? null,
                'expected_paise' => $expectedPaise,
            ]);
        }
        return $ok;
    }

    private function razorpayIdAlreadyUsed(string $rzpPaymentId, int $exceptPaymentRowId): bool
    {
        if ($rzpPaymentId === '') {
            return false;
        }
        return Payment::where('transaction_id', $rzpPaymentId)
            ->where('id', '!=', $exceptPaymentRowId)
            ->exists();
    }

    private function findPendingPaymentForRazorpay(array $entity): ?Payment
    {
        $email   = strtolower(trim((string) ($entity['email'] ?? '')));
        $contact = substr(preg_replace('/\D+/', '', (string) ($entity['contact'] ?? '')), -10);
        $paise   = (int) ($entity['amount'] ?? 0);

        $query = Payment::where('status', 'pending')
            ->whereHas('application', function ($q) use ($email, $contact) {
                $q->where(function ($w) use ($email, $contact) {
                    if ($email !== '') {
                        $w->orWhereRaw('LOWER(email) = ?', [$email]);
                    }
                    if (strlen($contact) === 10) {
                        $w->orWhere('mobile', 'like', '%' . $contact);
                    }
                });
            })
            ->latest('id');

        foreach ($query->get() as $p) {
            if ($paise >= (int) round(((float) $p->amount) * 100)) {
                return $p;
            }
        }
        return null;
    }

    /**
     * Single source of truth for marking a payment + application as paid.
     * Used by the return handler and the webhook so both paths stay identical
     * and idempotent.
     */
    private function markPaymentSuccess(Payment $payment, ?string $gatewayPaymentId): void
    {
        if ($payment->status === 'success') {
            return; // already handled — keep this idempotent for webhook retries
        }

        $payment->update([
            'status'         => 'success',
            'transaction_id' => $gatewayPaymentId,
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

    /** 10-digit Indian mobile (6-9 start) or null. */
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
