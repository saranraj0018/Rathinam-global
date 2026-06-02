{{-- STEP 5 — Particulars of academic, research & industry services (optional)
     Backend field names: service[i][designation|institution|from|to|total],
       total_service_years, total_service_months
       (Service Certificate & NOC are collected in the Enclosures step.) --}}
<section class="wizard-step" data-step="4" data-step-id="experience" hidden>
    <div class="step-head">
        <span class="step-kicker">Work &amp; Research Service</span>
        <h2 class="step-title">Academic, Research &amp; Industry Service</h2>
        <p class="step-desc">List your appointments starting from the first. Leave blank if you are a fresher — this section is optional.</p>
    </div>

    <div class="rep-table" data-repeat="service">
        <div class="rep-head rep-head--5">
            <span>Designation</span>
            <span>Institution / Establishment</span>
            <span>From</span>
            <span>To</span>
            <span>Total</span>
            <span></span>
        </div>

        <div data-repeat-body>
            {{-- one starter row --}}
            <div class="rep-row rep-row--5" data-repeat-row>
                <input type="text" name="service[0][designation]" class="f-input f-input--sm" data-cell="Designation">
                <input type="text" name="service[0][institution]" class="f-input f-input--sm" data-cell="Institution">
                <input type="month" name="service[0][from]" class="f-input f-input--sm" data-cell="From">
                <input type="month" name="service[0][to]" class="f-input f-input--sm" data-cell="To">
                <input type="text" name="service[0][total]" class="f-input f-input--sm" data-cell="Total" placeholder="e.g. 2y 3m">
                <button type="button" class="rep-remove" data-repeat-remove aria-label="Remove row">&times;</button>
            </div>
        </div>

        <template data-repeat-template>
            <div class="rep-row rep-row--5" data-repeat-row>
                <input type="text" name="service[__INDEX__][designation]" class="f-input f-input--sm" data-cell="Designation">
                <input type="text" name="service[__INDEX__][institution]" class="f-input f-input--sm" data-cell="Institution">
                <input type="month" name="service[__INDEX__][from]" class="f-input f-input--sm" data-cell="From">
                <input type="month" name="service[__INDEX__][to]" class="f-input f-input--sm" data-cell="To">
                <input type="text" name="service[__INDEX__][total]" class="f-input f-input--sm" data-cell="Total" placeholder="e.g. 2y 3m">
                <button type="button" class="rep-remove" data-repeat-remove aria-label="Remove row">&times;</button>
            </div>
        </template>

        <button type="button" class="btn btn-add" data-repeat-add>+ Add another appointment</button>
    </div>

    <div class="total-service">
        <span class="f-label mb-0">Total Service</span>
        <div class="total-service__inputs">
            <label>
                <input type="number" min="0" name="total_service_years" class="f-input f-input--sm" placeholder="0">
                <span>years</span>
            </label>
            <label>
                <input type="number" min="0" max="11" name="total_service_months" class="f-input f-input--sm" placeholder="0">
                <span>months</span>
            </label>
        </div>
    </div>

    <p class="f-hint mt-4">
        Part-Time applicants: your <strong>Service Certificate</strong> and <strong>NOC</strong> are
        uploaded in the <em>Enclosures</em> step.
    </p>
</section>
