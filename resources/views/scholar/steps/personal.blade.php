<section class="wizard-step" data-step="1" data-step-id="personal" hidden>
    <div class="step-head">
        <span class="step-kicker">Section 3 — 13</span>
        <h2 class="step-title">Personal Details</h2>
        <p class="step-desc">Tell us about yourself. Please enter your name exactly as in your certificates.</p>
    </div>

    {{-- <x-text-field name="full_name" label="Full Name (in Block Letters)" required uppercase
                  placeholder="FIRST · MIDDLE · FAMILY" hint="As printed on your certificates." /> --}}
    <x-text-field name="full_name" label="Full Name (in Block Letters)" required uppercase
        placeholder="FIRST · MIDDLE · FAMILY" hint="As printed on your certificates."
        value="{{ strtoupper($user->name ?? '') }}" />

    <div class="grid gap-5 sm:grid-cols-2">
        <x-text-field name="dob" label="Date of Birth" type="date" required />
        <x-text-field name="age" label="Age (auto-calculated)" placeholder="—" />
    </div>

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

    <div class="f-group">
        <span class="f-label">Mother Tongue &amp; Other Languages Known <span class="f-req">*</span></span>
        <p class="f-hint mb-2">Type a language first, then tick the skills — R (Read), W (Write), S (Speak), U
            (Understand).</p>
        <div class="lang-table" data-repeat="languages">
            <div class="lang-row lang-row--head">
                <span>Language</span>
                <span class="lang-row__skills">R / W / S / U</span>
            </div>

            <div data-repeat-body>
                <div class="lang-row" data-repeat-row>
                    <input type="text" name="languages[0][name]" id="mother_tongue" class="f-input lang-name"
                        data-lang-name required placeholder="Mother tongue (required)">
                    <span class="lang-row__skills">
                        @foreach ($data['language_skills'] as $code => $skill)
                            <label class="skill-chip" title="{{ $skill }}">
                                <input type="checkbox" name="languages[0][skills][]" value="{{ $code }}"
                                    disabled>
                                <span>{{ $code }}</span>
                            </label>
                        @endforeach
                    </span>
                </div>
            </div>

            <template data-repeat-template>
                <div class="lang-row" data-repeat-row>
                    <input type="text" name="languages[__INDEX__][name]" class="f-input lang-name" data-lang-name
                        placeholder="Other language">
                    <span class="lang-row__skills">
                        @foreach ($data['language_skills'] as $code => $skill)
                            <label class="skill-chip" title="{{ $skill }}">
                                <input type="checkbox" name="languages[__INDEX__][skills][]" value="{{ $code }}"
                                    disabled>
                                <span>{{ $code }}</span>
                            </label>
                        @endforeach
                        <button type="button" class="rep-remove rep-remove--lang" data-repeat-remove
                            aria-label="Remove language">&times;</button>
                    </span>
                </div>
            </template>

            <button type="button" class="btn btn-add" data-repeat-add>+ Add another language</button>
        </div>
        <p class="f-error" data-error-for="languages[0][name]"></p>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-text-field name="nationality" label="Nationality" required value="Indian" />
        <x-text-field name="religion" label="Religion" required />
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-select-field name="community" label="Community" required :options="$data['communities']"
            hint="Self-attested copy required (next field)." />
       <div id="community-cert-wrap" hidden>
    <div class="f-group">
        <label class="f-label">Community Certificate <span class="f-req">*</span></label>
        @php
                $hasSavedPhoto = !empty($draft['files']['community_certificate']['url'] ?? null);
        @endphp

        {{-- Saved file preview (shown by JS when draft has this file) --}}
        <div class="saved-file-preview" id="saved-community_certificate" hidden>
            <div class="saved-file-box">
                <svg class="saved-file-icon" ...></svg>
                <div class="saved-file-info">
                    <span class="saved-file-name" data-saved-name="community_certificate"></span>
                    <a class="saved-file-link" data-saved-url="community_certificate"
                       target="_blank" href="#">View uploaded file</a>
                </div>
                <span class="saved-file-badge">Already uploaded</span>
            </div>
            <p class="f-hint">Re-upload below to replace the existing file.</p>
        </div>

        <x-upload name="community_certificate" label=""  :image="true" :required="!$hasSavedPhoto"/>
    </div>
</div>
    </div>

    <div class="f-group">
        <span class="f-label">Differentially Abled? <span class="f-req">*</span></span>
        <div class="choice-grid choice-grid--2" data-validate-radio="differently_abled" data-reveal-group>
            <label class="choice-card choice-card--sm">
                <input type="radio" name="differently_abled" value="Yes" required
                    data-reveal-target="#disability-cert-wrap">
                <span class="choice-card__box"><span class="choice-card__title">Yes</span></span>
            </label>
            <label class="choice-card choice-card--sm">
                <input type="radio" name="differently_abled" value="No" required>
                <span class="choice-card__box"><span class="choice-card__title">No</span></span>
            </label>
        </div>
        <p class="f-error" data-error-for="differently_abled"></p>
        <div id="disability-cert-wrap" class="mt-3" hidden>
    <div class="f-group">
        <label class="f-label">Disability Certificate <span class="f-req">*</span></label>

        <div class="saved-file-preview" id="saved-disability_certificate" hidden>
            <div class="saved-file-box">
                <div class="saved-file-info">
                    <span class="saved-file-name" data-saved-name="disability_certificate"></span>
                    <a class="saved-file-link" data-saved-url="disability_certificate"
                       target="_blank" href="#">View uploaded file</a>
                </div>
                <span class="saved-file-badge">Already uploaded</span>
            </div>
            <p class="f-hint">Re-upload below to replace the existing file.</p>
        </div>

        <x-upload name="disability_certificate" label="" />
    </div>
</div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-text-field name="father_name" label="Name of the Father / Guardian" required />
        <x-text-field name="mother_name" label="Name of the Mother" required />
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <x-text-field name="mobile" label="Mobile Number" type="tel" required placeholder="10-digit mobile"
            autocomplete="tel" value="{{ $user->mobile ?? ($user->phone ?? '') }}" />
        <x-text-field name="email" label="E-mail" type="email" required placeholder="you@example.com"
            autocomplete="email" value="{{ $user->email ?? '' }}" />
    </div>

    <x-textarea-field name="address_current" label="Communication Address (Current)" required rows="3" />
    <label class="inline-check">
        <input type="checkbox" id="address_same" name="address_same" value="1">
        <span>Permanent address is the same as current</span>
    </label>
    <x-textarea-field name="address_permanent" label="Permanent Address" required rows="3" />
</section>
