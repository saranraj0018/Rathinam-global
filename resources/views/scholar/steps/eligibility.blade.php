<section class="wizard-step" data-step="3" data-step-id="eligibility" hidden>
    <div class="step-head">
        <span class="step-kicker">Eligibility</span>
        <h2 class="step-title">Eligibility for Ph.D. Admission</h2>
        <p class="step-desc">Have you qualified in any National / State level examination?</p>
    </div>

    <div class="f-group">
        <span class="f-label">Qualified in a National / State Level Examination? <span class="f-req">*</span></span>
        <div class="choice-grid choice-grid--2" data-validate-radio="eligibility_qualified" data-reveal-group>
            <label class="choice-card choice-card--sm">
                <input type="radio" name="eligibility_qualified" value="Yes" required data-reveal-target="#eligibility-yes">
                <span class="choice-card__box"><span class="choice-card__title">Yes</span></span>
            </label>
            <label class="choice-card choice-card--sm">
                <input type="radio" name="eligibility_qualified" value="No" required data-reveal-target="#eligibility-no">
                <span class="choice-card__box"><span class="choice-card__title">No</span></span>
            </label>
        </div>
        <p class="f-error" data-error-for="eligibility_qualified"></p>
    </div>

    {{-- If Yes --}}
    <div id="eligibility-yes" hidden>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-select-field name="eligibility_exam" label="Which examination?" required :options="$data['eligibility_exams']"
                            hint="SLET / SET / UGC-NET / CSIR-NET / GATE / etc." />
            <x-upload name="eligibility_cert" label="Self-attested copy of qualifying score" required data-conditional />
        </div>
    </div>

    {{-- If No --}}
    <div id="eligibility-no" hidden>
        <div class="callout callout--warn">
            <strong>RGU Common Eligibility Test (CET)</strong>
            Since you have not qualified in a National / State level examination, you must clear the
            <strong>RGU Common Eligibility Test (CET)</strong> to be considered for admission.
        </div>
    </div>
</section>
