{{-- STEP 8 — Declaration & signature
     Backend field names: declaration_agree, declaration_date,
       declaration_station, declaration_signature (typed name) --}}
<section class="wizard-step" data-step="7" data-step-id="declaration" hidden>
    <div class="step-head">
        <span class="step-kicker">Declaration</span>
        <h2 class="step-title">Declaration</h2>
        <p class="step-desc">Please read, then sign by typing your full name.</p>
    </div>

    <label class="confirm-box">
        <input type="checkbox" name="declaration_agree" value="1" required>
        <span>I certify that the above statements are true to the best of my knowledge.</span>
    </label>
    <p class="f-error" data-error-for="declaration_agree"></p>

    <div class="grid gap-5 sm:grid-cols-2 mt-5">
        <x-text-field name="declaration_date" label="Date" type="date" required :value="now()->toDateString()" />
        <x-text-field name="declaration_station" label="Station / Place" required placeholder="e.g. Coimbatore" />
    </div>

    {{-- Signature --}}
    <div class="f-group">
        <label for="declaration_signature" class="f-label">Signature of the Applicant <span class="f-req">*</span></label>
        <input type="text" id="declaration_signature" name="declaration_signature" required
               class="f-input" placeholder="Type your full name to sign" data-signature-input
               autocomplete="off">
        <div class="signature-pad">
            <span class="signature-pad__hint">Your signature preview</span>
            <span class="signature-pad__ink" data-signature-preview></span>
        </div>
        <p class="f-hint">Typing your name acts as your electronic signature for this application.</p>
        <p class="f-error" data-error-for="declaration_signature"></p>
    </div>
</section>
