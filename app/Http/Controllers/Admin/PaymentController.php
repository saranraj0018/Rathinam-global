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
    /** Bump when this file changes, so you can confirm the live server has the new version. */
    private const CODE_VERSION = 'pay-2026-11-13-v4';

    /** Non-secret snapshot of what the server is actually using (shown in logs and /payment/debug). */
    private function debugSnapshot(): array
    {
        $keyId = (string) config('services.razorpay.key_id');
        return [
            'code_version'      => self::CODE_VERSION,
            'checkout_mode'     => config('services.razorpay.checkout_mode', '(missing - old config/services.php)'),
            'payment_page_url'  => config('services.razorpay.payment_page_url'),
            'application_fee'   => config('services.razorpay.application_fee', '(missing - old config/services.php)'),
            'key_mode'          => str_starts_with($keyId, 'rzp_live_') ? 'live' : (str_starts_with($keyId, 'rzp_test_') ? 'test' : 'not set'),
            'secret_set'        => config('services.razorpay.key_secret') ? 'yes' : 'no',
            'webhook_secret_set'=> config('services.razorpay.webhook_secret') ? 'yes' : 'no',
            'config_cached'     => app()->configurationIsCached() ? 'YES - clear it after changing .env/config' : 'no',
            'controller_file'   => __FILE__,
            'base_path'         => base_path(),
            'app_url'           => config('app.url'),
            'return_url'        => route('payment.razorpay.return'),
        ];
    }

    /** Payment log channel: storage/logs/payment-YYYY-MM-DD.log */
    private function log()
    {
        return Log::channel('payment');
    }

    /** GET /payment/debug (logged-in users): shows the snapshot above as JSON. */
    public function debug()
    {
        return response()->json($this->debugSnapshot(), 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    public function initiatePayment(Request $request)
    {
        $this->log()->info('[PAY] initiatePayment: config', $this->debugSnapshot());
        try {
            $user = Auth::guard('user')->user();
            $app = Application::where('user_id', $user->id)
                ->where('status', 'draft')
                ->latest('id')
                ->first();
            $this->log()->info('[PAY] initiatePayment: start', ['user_id' => $user->id, 'application_id' => $app->id ?? null]);
            if (!$app) {
                $this->log()->error('[PAY] initiatePayment FAILED: no draft application for user', ['user_id' => $user->id]);
                return response()->json(['success' => false, 'message' => 'No draft application found.'], 422);
            }
            $app->current_step   = 'payment';
            $app->payment_status = 'payment_pending';
            $app->save();

            $payment = Payment::where('application_id', $app->id)
                ->where('status', 'pending')
                ->latest()
                ->first();

            $fee = (float) config('services.razorpay.application_fee');

            if ($payment && (float) $payment->amount !== $fee) {
                // Pending row created before the fee changed: bring it up to date.
                $payment->amount = $fee;
                $payment->save();
            }

            if (!$payment) {
                $payment = Payment::create([
                    'payment_id'     => 'APPL_' . strtoupper(uniqid()),
                    'amount'         => $fee, // RAZORPAY_APPLICATION_FEE in .env
                    'application_id' => $app->id,
                    'user_id'        => $user->id,
                    'status'         => 'pending',
                    'type'           => 'application',
                ]);
            }

            session(['pending_form_payment_id' => $payment->payment_id]);
            $this->log()->info('[PAY] initiatePayment: payment row', [
                'payment'     => $payment->toArray(),
                'application' => $app->only(['id', 'application_no', 'status', 'payment_status', 'current_step']),
                'ip'          => $request->ip(),
                'user_agent'  => $request->userAgent(),
            ]);

            // Standard Checkout: create (or reuse) a Razorpay Order, store its id on the
            // payment row, and let the browser open Razorpay Checkout on our own page.
            if (config('services.razorpay.checkout_mode') === 'checkout') {
                $orderId = $this->ensureRazorpayOrder($payment);
                if (!$orderId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Could not start payment with Razorpay. Please try again in a moment.',
                    ], 502);
                }

                return response()->json([
                    'success'     => true,
                    'mode'        => 'checkout',
                    'payment_id'  => $payment->payment_id,
                    'key'         => (string) config('services.razorpay.key_id'),
                    'order_id'    => $orderId,
                    'amount'      => (int) round(((float) $payment->amount) * 100),
                    'currency'    => 'INR',
                    'name'        => 'Rathinam Group of Institutions',
                    'description' => 'Ph.D Application Fee',
                    'prefill'     => array_filter([
                        'name'    => $app->full_name ?? ($user->name ?? null),
                        'email'   => $app->email ?? ($user->email ?? null),
                        'contact' => $this->normalizePhone($app->mobile ?? ($user->phone ?? null)),
                    ]),
                    'verify_url'  => route('payment.verify'),
                    'status_url'  => route('payment.status'),
                ]);
            }

            // Preferred: a per-application Razorpay Payment Link whose callback_url
            // brings the browser back to us (so the payment can be verified and the
            // "Payment confirmed" popup shown). Falls back to the static page.
            $pageUrl = config('services.razorpay.checkout_mode') === 'link'
                ? $this->createRazorpayPaymentLink($payment, $app, $user)
                : null;
            $pageUrl = $pageUrl ?? (string) config('services.razorpay.payment_page_url');
            $this->log()->info('[PAY] initiatePayment: redirecting to Razorpay', [
                'payment_id' => $payment->payment_id,
                'url'        => $pageUrl,
            ]);

            return response()->json([
                'success'    => true,
                'mode'       => config('services.razorpay.checkout_mode') === 'link' ? 'link' : 'page',
                'payment_id' => $payment->payment_id,
                'redirect'   => $pageUrl,
                'status_url' => route('payment.status'),
                'debug'      => [
                    'code_version'  => self::CODE_VERSION,
                    'checkout_mode' => config('services.razorpay.checkout_mode'),
                    'amount'        => $payment->amount,
                ],
            ]);
        } catch (\Throwable $e) {
            $this->log()->error('initiatePayment failed', ['msg' => $e->getMessage(), 'line' => $e->getLine()]);
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
        $this->log()->info('[PAY] razorpayReturn: start', [
            'user_id'    => $user->id ?? null,
            'session_id' => $paymentId,
            'method'     => $request->method(),
            'full_url'   => $request->fullUrl(),
            'all_input'  => $request->all(),
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
            $this->log()->warning('[PAY] razorpayReturn: no pending payment found');
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
            $this->log()->warning('[PAY] razorpayReturn: no razorpay_payment_id in redirect; waiting for webhook');
            return redirect()->route('scholar.create')
                ->with('payment_failed', 'We could not confirm your payment yet. If money was deducted, it will be updated shortly.');
        }

        // Payment Link callback: verify Razorpay's HMAC signature first.
        if ($ref !== '' && !$this->validPaymentLinkSignature($request)) {
            $this->log()->error('[PAY] razorpayReturn: payment link signature invalid');
            return redirect()->route('scholar.create')
                ->with('payment_failed', 'Payment could not be verified. Please contact support.');
        }

        if ($this->verifyRazorpayPayment($payment, $rzpPaymentId)) {
            if (!$payment->order_id && $request->filled('razorpay_order_id')) {
                $payment->update(['order_id' => (string) $request->input('razorpay_order_id')]);
            }
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
        $this->log()->info('[PAY] webhook: received', [
            'event'     => $request->input('event'),
            'ip'        => $request->ip(),
            'signature' => $signature !== '' ? 'present' : 'missing',
            'payload'   => json_decode($raw, true) ?? $raw,
        ]);

        if ($signature === '' || !hash_equals($expected, $signature)) {
            $this->log()->warning('[PAY] Razorpay webhook signature mismatch');
            return response()->json(['ok' => false], 400);
        }

        $event  = $request->input('event');
        $entity = $request->input('payload.payment.entity', []);
        $this->log()->info('[PAY] Razorpay webhook', ['event' => $event, 'payment' => $entity['id'] ?? null, 'status' => $entity['status'] ?? null]);

        if (!in_array($event, ['payment.captured', 'payment_page.paid', 'payment_link.paid', 'order.paid'], true)
            || ($entity['status'] ?? null) !== 'captured') {
            return response()->json(['ok' => true]); // not interesting; ack so it is not retried
        }

        $linkRef = (string) $request->input('payload.payment_link.entity.reference_id', '');
        $orderId = (string) ($entity['order_id'] ?? '');
        $payment = $orderId !== ''
            ? Payment::where('order_id', $orderId)->where('status', 'pending')->first()
            : null;
        if (!$payment) {
            $payment = $linkRef !== ''
                ? Payment::where('payment_id', explode('-', $linkRef)[0])->where('status', 'pending')->first()
                : $this->findPendingPaymentForRazorpay($entity);
        }
        if (!$payment) {
            $this->log()->warning('[PAY] Razorpay webhook: no matching pending payment', [
                'email'   => $entity['email'] ?? null,
                'contact' => $entity['contact'] ?? null,
                'amount'  => $entity['amount'] ?? null,
            ]);
            return response()->json(['ok' => true]);
        }

        if ($this->razorpayIdAlreadyUsed($entity['id'] ?? '', $payment->id)) {
            return response()->json(['ok' => true]);
        }

        if (!$payment->order_id && !empty($entity['order_id'])) {
            $payment->update(['order_id' => $entity['order_id']]); // Razorpay's order (Payment Page flow)
        }

        $this->markPaymentSuccess($payment, $entity['id'] ?? null);
        return response()->json(['ok' => true]);
    }

    /**
     * Create a Razorpay Order for this payment (or reuse the stored one if it is still
     * unpaid and for the same amount) and save order_id on the payments row.
     */
    private function ensureRazorpayOrder(Payment $payment): ?string
    {
        $keyId  = (string) config('services.razorpay.key_id');
        $secret = (string) config('services.razorpay.key_secret');
        if ($keyId === '' || $secret === '') {
            $this->log()->error('[PAY] ensureRazorpayOrder: RAZORPAY_KEY_ID/SECRET not set');
            return null;
        }
        $paise = (int) round(((float) $payment->amount) * 100);

        if ($payment->order_id) {
            $existing = Http::withBasicAuth($keyId, $secret)->acceptJson()
                ->get('https://api.razorpay.com/v1/orders/' . urlencode($payment->order_id));
            $o = $existing->json();
            $this->log()->info('[PAY] ensureRazorpayOrder: existing order', ['http' => $existing->status(), 'body' => $o]);
            if ($existing->successful() && ($o['status'] ?? '') !== 'paid' && (int) ($o['amount'] ?? 0) === $paise) {
                return $payment->order_id;
            }
        }

        $orderRequest = [
            'amount'   => $paise,
            'currency' => 'INR',
            'receipt'  => $payment->payment_id,
            'notes'    => [
                'payment_id'     => $payment->payment_id,
                'application_id' => (string) $payment->application_id,
            ],
        ];
        $this->log()->info('[PAY] ensureRazorpayOrder: request', $orderRequest);
        $resp = Http::withBasicAuth($keyId, $secret)->acceptJson()
            ->post('https://api.razorpay.com/v1/orders', $orderRequest);
        $data = $resp->json();
        $this->log()->info('[PAY] ensureRazorpayOrder: response', ['http' => $resp->status(), 'body' => $data ?? $resp->body()]);

        if (!$resp->successful() || empty($data['id'])) {
            return null;
        }
        $payment->update(['order_id' => $data['id']]);
        return $data['id'];
    }

    /**
     * POST /payment/verify - called by Razorpay Checkout's success handler with
     * razorpay_order_id, razorpay_payment_id and razorpay_signature.
     */
    public function verifyCheckout(Request $request)
    {
        $user      = Auth::guard('user')->user();
        $orderId   = (string) $request->input('razorpay_order_id');
        $rzpPayId  = (string) $request->input('razorpay_payment_id');
        $signature = (string) $request->input('razorpay_signature');
        $this->log()->info('[PAY] verifyCheckout: start', [
            'user_id'             => $user->id ?? null,
            'order_id'            => $orderId,
            'razorpay_payment_id' => $rzpPayId,
            'signature'           => $signature !== '' ? 'present' : 'missing',
        ]);

        $payment = $orderId !== ''
            ? Payment::where('order_id', $orderId)->where('user_id', $user->id)->first()
            : null;
        if (!$payment) {
            $this->log()->warning('[PAY] verifyCheckout: no payment row for order', ['order_id' => $orderId]);
            return response()->json(['success' => false, 'message' => 'Payment record not found.'], 404);
        }

        if ($payment->status !== 'success') {
            $expected = hash_hmac('sha256', $orderId . '|' . $rzpPayId, (string) config('services.razorpay.key_secret'));
            if ($rzpPayId === '' || !hash_equals($expected, $signature)) {
                $this->log()->error('[PAY] verifyCheckout: signature invalid', ['order_id' => $orderId]);
                return response()->json(['success' => false, 'message' => 'Payment could not be verified. Please contact support.'], 422);
            }
            if (!$this->verifyRazorpayPayment($payment, $rzpPayId)) {
                return response()->json(['success' => false, 'message' => 'Payment not confirmed yet. If money was deducted it will update shortly.'], 422);
            }
            $this->markPaymentSuccess($payment, $rzpPayId);
        }

        session()->forget('pending_form_payment_id');
        $this->log()->info('[PAY] verifyCheckout: success', ['payment_id' => $payment->payment_id, 'order_id' => $orderId]);
        return response()->json(['success' => true, 'status' => 'success', 'redirect' => $this->thankYouUrl($payment)]);
    }

    /**
     * GET /payment/status - looks up the user's latest payment; if it has an order_id,
     * asks Razorpay whether that order has a captured payment and updates the row.
     * Called when the checkout popup is closed, so a paid-but-not-returned payment is caught.
     */
    public function status(Request $request)
    {
        $user    = Auth::guard('user')->user();
        $payment = Payment::where('user_id', $user->id)->latest('id')->first();
        if (!$payment) {
            return response()->json(['status' => 'none']);
        }

        if ($payment->status !== 'success' && $payment->order_id) {
            $resp = Http::withBasicAuth(
                (string) config('services.razorpay.key_id'),
                (string) config('services.razorpay.key_secret')
            )->acceptJson()->get('https://api.razorpay.com/v1/orders/' . urlencode($payment->order_id) . '/payments');
            $items = $resp->json('items') ?? [];
            $this->log()->info('[PAY] status: order payments', [
                'order_id' => $payment->order_id,
                'http'     => $resp->status(),
                'payments' => array_map(fn ($p) => [
                    'id'     => $p['id'] ?? null,
                    'status' => $p['status'] ?? null,
                    'amount' => $p['amount'] ?? null,
                    'method' => $p['method'] ?? null,
                ], $items),
            ]);
            foreach ($items as $p) {
                if (($p['status'] ?? '') === 'captured' && $this->verifyRazorpayPayment($payment, (string) $p['id'])) {
                    $this->markPaymentSuccess($payment, (string) $p['id']);
                    $payment->refresh();
                    break;
                }
            }
        }

        if ($payment->status !== 'success' && !$payment->order_id) {
            $found = $this->findCapturedPaymentForRow($payment);
            if ($found && $this->verifyRazorpayPayment($payment, $found)) {
                $this->markPaymentSuccess($payment, $found);
                $payment->refresh();
            }
        }

        return response()->json([
            'status'   => $payment->status,
            'order_id' => $payment->order_id,
            'redirect' => $payment->status === 'success' ? $this->thankYouUrl($payment) : null,
        ]);
    }

    /**
     * Payment Page flow: Razorpay creates its own order, so we have no order_id.
     * Look at recent captured payments on the account and pick the one paid by this
     * applicant (same email or mobile as the application, amount >= fee, not used yet).
     */
    private function findCapturedPaymentForRow(Payment $payment): ?string
    {
        $app = Application::find($payment->application_id);
        if (!$app) {
            return null;
        }
        $email   = strtolower(trim((string) $app->email));
        $mobile  = substr(preg_replace('/\D+/', '', (string) $app->mobile), -10);
        $minPaise = (int) round(((float) $payment->amount) * 100);
        $from    = optional($payment->created_at)->subMinutes(5)->timestamp ?? now()->subDay()->timestamp;

        for ($skip = 0; $skip < 500; $skip += 100) {
            $resp = Http::withBasicAuth(
                (string) config('services.razorpay.key_id'),
                (string) config('services.razorpay.key_secret')
            )->acceptJson()->get('https://api.razorpay.com/v1/payments', [
                'from' => $from, 'to' => now()->timestamp, 'count' => 100, 'skip' => $skip,
            ]);
            $items = $resp->json('items') ?? [];
            $this->log()->info('[PAY] findCapturedPaymentForRow', [
                'payment_id' => $payment->payment_id, 'http' => $resp->status(), 'skip' => $skip, 'count' => count($items),
            ]);
            if (!$resp->successful()) {
                return null;
            }

            foreach ($items as $p) {
                $pEmail  = strtolower(trim((string) ($p['email'] ?? '')));
                $pMobile = substr(preg_replace('/\D+/', '', (string) ($p['contact'] ?? '')), -10);
                $matches = ($email !== '' && $pEmail === $email)
                    || (strlen($mobile) === 10 && $pMobile === $mobile);
                if (($p['status'] ?? '') === 'captured' && $matches
                    && (int) ($p['amount'] ?? 0) >= $minPaise
                    && !$this->razorpayIdAlreadyUsed((string) $p['id'], $payment->id)) {
                    $this->log()->info('[PAY] findCapturedPaymentForRow: matched', [
                        'payment_id' => $payment->payment_id, 'razorpay_payment_id' => $p['id'],
                        'order_id' => $p['order_id'] ?? null, 'amount' => $p['amount'] ?? null,
                    ]);
                    // Remember Razorpay's order id for this row as well.
                    if (!empty($p['order_id']) && !$payment->order_id) {
                        $payment->update(['order_id' => $p['order_id']]);
                    }
                    return (string) $p['id'];
                }
            }
            if (count($items) < 100) {
                break;
            }
        }
        return null;
    }

    /** Flash the application ref for the Thank You page and return its URL. */
    private function thankYouUrl(Payment $payment): string
    {
        $ref = optional(Application::find($payment->application_id))->application_no;
        session()->flash('ref', $ref);
        return route('scholar.thankyou');
    }

    /** Create a Razorpay Payment Link for this payment; null if keys missing or the call fails. */
    private function createRazorpayPaymentLink(Payment $payment, Application $app, $user): ?string
    {
        $keyId  = (string) config('services.razorpay.key_id');
        $secret = (string) config('services.razorpay.key_secret');
        if ($keyId === '' || $secret === '') {
            $this->log()->warning('[PAY] RAZORPAY_KEY_ID/SECRET not set; using static payment page (no redirect back / no verification)');
            return null;
        }

        $customer = array_filter([
            'name'    => $app->full_name ?? ($user->name ?? null),
            'email'   => $app->email ?? ($user->email ?? null),
            'contact' => $this->normalizePhone($app->mobile ?? ($user->phone ?? null)),
        ]);

        $linkRequest = [
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
        ];
        $this->log()->info('[PAY] createRazorpayPaymentLink: request', $linkRequest);
        $resp = Http::withBasicAuth($keyId, $secret)->acceptJson()->post('https://api.razorpay.com/v1/payment_links', $linkRequest);
        $data = $resp->json();
        $this->log()->info('[PAY] createRazorpayPaymentLink', ['http' => $resp->status(), 'body' => $data ?? $resp->body()]);

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
            $this->log()->warning('[PAY] razorpay payment id already used by another payment', ['rzp' => $rzpPaymentId]);
            return false;
        }

        $resp = Http::withBasicAuth(
            (string) config('services.razorpay.key_id'),
            (string) config('services.razorpay.key_secret')
        )->acceptJson()->get('https://api.razorpay.com/v1/payments/' . urlencode($rzpPaymentId));

        $data = $resp->json();
        $this->log()->info('[PAY] verifyRazorpayPayment', [
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
            $this->log()->error('[PAY] verifyRazorpayPayment FAILED checks', [
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

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($payment, $gatewayPaymentId, &$app) {
                $payment->update([
                    'status'         => 'success',
                    'transaction_id' => $gatewayPaymentId,
                ]);

                $app = Application::find($payment->application_id);
                if (!$app) {
                    return;
                }

                $completed = $app->completed_steps ?? [];
                if (!is_array($completed)) {
                    $completed = (array) json_decode((string) $completed, true);
                }
                if (!in_array('payment', $completed)) {
                    $completed[] = 'payment';
                }
                $app->completed_steps = $completed;
                $app->current_step    = 'preview';
                $app->payment_status  = 'paid';
                $app->status          = 'submitted';
                $app->submitted_at    = now();
                $app->save();
            });
        } catch (Throwable $e) {
            $this->log()->error('[PAY] markPaymentSuccess FAILED (nothing saved)', [
                'payment_id'          => $payment->payment_id,
                'razorpay_payment_id' => $gatewayPaymentId,
                'error'               => $e->getMessage(),
                'file'                => $e->getFile() . ':' . $e->getLine(),
            ]);
            throw $e;
        }
        $this->log()->info('[PAY] markPaymentSuccess: saved', [
            'payment_id' => $payment->payment_id, 'razorpay_payment_id' => $gatewayPaymentId,
            'application_id' => $payment->application_id,
        ]);

        if (empty($app)) {
            return;
        }

        try {
            Mail::to($app->email)
                // ->cc('admissions@rgu.ac.in')
                ->send(new ApplicationSubmitted($app));
        } catch (Throwable $e) {
            $this->log()->error('Application submission email failed', [
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


