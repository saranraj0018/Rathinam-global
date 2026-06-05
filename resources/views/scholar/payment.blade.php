<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Redirecting to payment…</title>

    {{-- Cashfree JS SDK v3 --}}
    <script src="https://sdk.cashfree.com/js/v3/cashfree.js"></script>

    <style>
        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            margin: 0;
            background: #f5f6f8;
            color: #1f2937;
        }
        .pay-card {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 8px 30px rgba(0, 0, 0, .08);
            padding: 36px 40px;
            text-align: center;
            max-width: 420px;
            width: 100%;
        }
        .pay-card h1 { font-size: 1.25rem; margin: 0 0 8px; }
        .pay-card p  { color: #6b7280; margin: 0 0 20px; font-size: .95rem; }
        .spinner {
            width: 38px; height: 38px;
            border: 4px solid #e5e7eb;
            border-top-color: #2563eb;
            border-radius: 50%;
            margin: 0 auto 20px;
            animation: spin 0.8s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .pay-btn {
            display: inline-block;
            background: #2563eb;
            color: #fff;
            border: 0;
            border-radius: 8px;
            padding: 12px 22px;
            font-size: 1rem;
            cursor: pointer;
        }
        .pay-btn:disabled { opacity: .6; cursor: not-allowed; }
        .pay-err { color: #b91c1c; font-size: .9rem; margin-top: 14px; }
    </style>
</head>
<body>
    <div class="pay-card">
        <div class="spinner" id="spinner"></div>
        <h1>Opening secure payment…</h1>
        <p>Please don't close or refresh this page.</p>
        <button class="pay-btn" id="payBtn" style="display:none">Proceed to Payment</button>
        <div class="pay-err" id="payErr"></div>
    </div>

    <script>
        (function () {
            var sessionId = @json($paymentSessionId);
            var mode      = @json($mode); // 'sandbox' or 'production'

            var spinner = document.getElementById("spinner");
            var btn     = document.getElementById("payBtn");
            var errEl   = document.getElementById("payErr");

            function showError(msg) {
                spinner.style.display = "none";
                errEl.textContent = msg || "Could not start payment. Please try again.";
                btn.style.display = "inline-block";
                btn.disabled = false;
            }

            function startCheckout() {
                try {
                    var cashfree = Cashfree({ mode: mode });
                    cashfree.checkout({
                        paymentSessionId: sessionId,
                        redirectTarget: "_self", // redirect this tab to return_url after payment
                    });
                } catch (e) {
                    showError(e && e.message);
                }
            }

            btn.addEventListener("click", function () {
                btn.disabled = true;
                startCheckout();
            });

            if (!sessionId) {
                showError("Payment session is missing. Please go back and try again.");
                return;
            }

            // Auto-launch on load.
            startCheckout();
        })();
    </script>
</body>
</html>