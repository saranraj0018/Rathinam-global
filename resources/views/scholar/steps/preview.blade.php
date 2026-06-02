{{-- STEP 9 — Preview & submit. #preview-doc is rendered by scholar.js from the
     live form values when the applicant reaches this step. --}}
<section class="wizard-step" data-step="8" data-step-id="preview" hidden>
    <div class="step-head">
        <span class="step-kicker">Almost done</span>
        <h2 class="step-title">Preview your Application</h2>
        <p class="step-desc">Review every detail below. Use <em>Back</em> to fix anything, then submit. You can also save a PDF copy.</p>
    </div>

    <div class="preview-toolbar">
        <button type="button" class="btn btn-ghost" data-print>
            ⬇ Save / Print as PDF
        </button>
        <span class="preview-toolbar__note">Tip: choose “Save as PDF” in the print dialog.</span>
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
</section>
