<section class="wizard-step" data-step="0" data-step-id="programme" hidden>
    <div class="step-head">
        <span class="step-kicker">Section 1 — 2</span>
        <h2 class="step-title">Programme &amp; School</h2>
        <p class="step-desc">Choose where you seek admission. Discipline and specialization update based on the School you pick (refer Annexure-1).</p>
    </div>

    <div class="grid gap-6 md:grid-cols-[1fr_220px]">
        <div class="space-y-5">
            {{-- 1a. School --}}
            <div class="f-group">
                <label for="school" class="f-label">Name of the School <span class="f-req">*</span></label>
                <select id="school" name="school" required aria-required="true" class="f-input f-select" data-cascade="school">
                    <option value="" disabled selected>— Select a School —</option>
                    @foreach ($data['schools'] as $i => $school)
                        <option value="{{ $school['name'] }}">{{ $i + 1 }}. {{ $school['name'] }}</option>
                    @endforeach
                </select>
                <p class="f-error" data-error-for="school"></p>
            </div>

            {{-- 1b. Discipline --}}
            <div class="f-group">
                <label for="discipline" class="f-label">Name of the Discipline <span class="f-req">*</span></label>
                <select id="discipline" name="discipline" required aria-required="true" class="f-input f-select" data-cascade="discipline" disabled>
                    <option value="" disabled selected>— Select a School first —</option>
                </select>
                <p class="f-error" data-error-for="discipline"></p>
            </div>

            {{-- 1c. Specialization --}}
            <div class="f-group">
                <label for="specialization" class="f-label">Specialization sought <span class="f-req">*</span></label>
                <select id="specialization" name="specialization" required aria-required="true" class="f-input f-select" data-cascade="specialization" disabled>
                    <option value="" disabled selected>— Select a Discipline first —</option>
                </select>
                <p class="f-hint" data-spec-hint hidden>No preset specialization for this discipline — please specify yours below.</p>
                <p class="f-error" data-error-for="specialization"></p>
            </div>

            {{-- 1c (other) — revealed when "Other (please specify)" chosen or discipline has no list --}}
            <div class="f-group" data-spec-other-wrap hidden>
                <label for="specialization_other" class="f-label">Specify your specialization <span class="f-req">*</span></label>
                <input type="text" id="specialization_other" name="specialization_other" class="f-input"
                       placeholder="Type your specialization / research area" />
                <p class="f-error" data-error-for="specialization_other"></p>
            </div>
        </div>

        {{-- Passport photo --}}
        <div class="photo-col">
            <x-upload name="photo" label="Passport Size Photograph" :image="true" required
                      hint="Recent passport photo · JPG/PNG · max 2 MB" />
        </div>
    </div>

    {{-- 2. Programme Mode --}}
    <div class="f-group mt-2">
        <span class="f-label">Programme Mode <span class="f-req">*</span></span>
        <div class="choice-grid choice-grid--4" data-validate-radio="programme_mode">
            @foreach ($data['programme_modes'] as $value => $label)
                <label class="choice-card">
                    <input type="radio" name="programme_mode" value="{{ $value }}" required>
                    <span class="choice-card__box">
                        <span class="choice-card__title">{{ $label }}</span>
                    </span>
                </label>
            @endforeach
        </div>
        <p class="f-hint">Note: NOC &amp; Service Certificate are required only for <strong>Part-Time</strong> applicants — the Enclosures step adapts automatically.</p>
        <p class="f-error" data-error-for="programme_mode"></p>
    </div>
</section>
