<section class="wizard-step" data-step="8" data-step-id="preview" hidden>
    <div class="step-head">
        <span class="step-kicker">Almost done</span>
        <h2 class="step-title">Preview your Application</h2>
        <p class="step-desc">Review every detail below. Use <em>Back</em> to fix anything, then pay the application fee to submit. You can also save a PDF copy.</p>
    </div>

    <div class="preview-toolbar">
        <button type="button" class="btn btn-ghost" data-print>
            ⬇ Save / Print as PDF
        </button>
        <span class="preview-toolbar__note">Tip: choose "Save as PDF" in the print dialog.</span>
    </div>

    {{-- Document-style preview (also the print target) --}}
    <div id="preview-doc" class="preview-doc" aria-live="polite">
        {{-- Print-only letterhead --}}
        <div class="preview-doc__letterhead">
            <img src="{{ asset('images/logo.png') }}" alt="RGU" class="preview-doc__logo">
            <div>
                <p class="preview-doc__uni">Rathinam Global University</p>
                <p class="preview-doc__form">Application for Admission to Ph.D. · Doctoral Programmes 2026–27</p>
            </div>
        </div>
        {{-- JS injects the rendered sections here --}}
        <div data-preview-body></div>
    </div>

    {{-- Payment call-to-action — appears only on the preview step --}}
    <div class="pay-cta">
        <div class="pay-cta__info">
            <span class="pay-cta__label">Application Fee</span>
            <span class="pay-cta__amount">₹500</span>
        </div>
        <button type="button" class="btn btn-primary btn-pay" data-pay-now>
            Pay Now &amp; Submit →
        </button>
    </div>
    <p class="f-error" data-error-for="payment"></p>
</section>
