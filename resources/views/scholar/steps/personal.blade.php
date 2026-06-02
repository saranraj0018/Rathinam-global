{{-- STEP 2 — Personal details
     Backend field names: full_name, dob, age, gender, single_girl_child,
       mother_tongue, languages[i][name], languages[i][skills][],
       nationality, religion, community, community_cert (file),
       differently_abled, disability_cert (file),
       father_name, mother_name, mobile, email,
       address_current, address_permanent, address_same --}}
<section class="wizard-step" data-step="1" data-step-id="personal" hidden>
    <div class="step-head">
        <span class="step-kicker">Section 3 — 13</span>
        <h2 class="step-title">Personal Details</h2>
        <p class="step-desc">Tell us about yourself. Please enter your name exactly as in your certificates.</p>
    </div>

    <x-text-field name="full_name" label="Full Name (in Block Letters)" required uppercase
                  placeholder="FIRST · MIDDLE · FAMILY" hint="As printed on your certificates." />

    <div class="grid gap-5 sm:grid-cols-2">
        <x-text-field name="dob" label="Date of Birth" type="date" required />
        <x-text-field name="age" label="Age (auto-calculated)" placeholder="—" />
    </div>

    {{-- Gender + Single girl child --}}
    <div class="f-group">
        <span class="f-label">Gender <span class="f-req">*</span></span>
        <div class="choice-grid choice-grid--4" data-validate-radio="gender">
            @foreach ($data['genders'] as $g)
                <label class="choice-card choice-card--sm">
                    <input type="radio" name="gender" value="{{ $g }}" required>
                    <span class="choice-card__box"><span class="choice-card__title">{{ $g }}</span></span>
                </label>
            @endforeach
            <label class="choice-card choice-card--sm choice-card--check">
                <input type="checkbox" name="single_girl_child" value="1">
                <span class="choice-card__box"><span class="choice-card__title">Single Girl Child</span></span>
            </label>
        </div>
        <p class="f-error" data-error-for="gender"></p>
    </div>

    {{-- Languages known --}}
    <div class="f-group">
        <span class="f-label">Mother Tongue &amp; Other Languages Known</span>
        <p class="f-hint mb-2">Tick the skills you have for each language — R (Read), W (Write), S (Speak).</p>
        <div class="lang-table">
            <div class="lang-row lang-row--head">
                <span>Language</span>
                <span class="lang-row__skills">R / W / S</span>
            </div>
            @for ($i = 0; $i < 4; $i++)
                <div class="lang-row">
                    <input type="text" name="languages[{{ $i }}][name]" class="f-input"
                           placeholder="{{ $i === 0 ? 'Mother tongue' : 'Language ' . ($i + 1) }}"
                           @if($i === 0) id="mother_tongue" data-mother-tongue @endif>
                    <span class="lang-row__skills">
                        @foreach (['R', 'W', 'S'] as $skill)
                            <label class="skill-chip">
                                <input type="checkbox" name="languages[{{ $i }}][skills][]" value="{{ $skill }}">
                                <span>{{ $skill }}</span>
                            </label>
                        @endforeach
                    </span>
                </div>
            @endfor
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-text-field name="nationality" label="Nationality" required value="Indian" />
        <x-text-field name="religion" label="Religion" required />
    </div>

    {{-- Community + certificate --}}
    <div class="grid gap-5 sm:grid-cols-2">
        <x-select-field name="community" label="Community" required :options="$data['communities']"
                        hint="Self-attested copy required (next field)." />
        <div id="community-cert-wrap" hidden>
            <x-upload name="community_cert" label="Community Certificate" required data-conditional />
        </div>
    </div>

    {{-- Differently abled --}}
    <div class="f-group">
        <span class="f-label">Differentially Abled? <span class="f-req">*</span></span>
        <div class="choice-grid choice-grid--2" data-validate-radio="differently_abled" data-reveal-group>
            <label class="choice-card choice-card--sm">
                <input type="radio" name="differently_abled" value="Yes" required data-reveal-target="#disability-cert-wrap">
                <span class="choice-card__box"><span class="choice-card__title">Yes</span></span>
            </label>
            <label class="choice-card choice-card--sm">
                <input type="radio" name="differently_abled" value="No" required>
                <span class="choice-card__box"><span class="choice-card__title">No</span></span>
            </label>
        </div>
        <p class="f-error" data-error-for="differently_abled"></p>
        <div id="disability-cert-wrap" class="mt-3" hidden>
            <x-upload name="disability_cert" label="Disability Certificate (self-attested)" required data-conditional />
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-text-field name="father_name" label="Name of the Father / Guardian" required />
        <x-text-field name="mother_name" label="Name of the Mother" required />
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-text-field name="mobile" label="Mobile Number" type="tel" required
                      placeholder="10-digit mobile" autocomplete="tel" />
        <x-text-field name="email" label="E-mail" type="email" required
                      placeholder="you@example.com" autocomplete="email" />
    </div>

    {{-- Address --}}
    <x-textarea-field name="address_current" label="Communication Address (Current)" required rows="3" />
    <label class="inline-check">
        <input type="checkbox" id="address_same" name="address_same" value="1">
        <span>Permanent address is the same as current</span>
    </label>
    <x-textarea-field name="address_permanent" label="Permanent Address" required rows="3" />
</section>
