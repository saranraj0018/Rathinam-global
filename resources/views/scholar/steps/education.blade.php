@php
    // Always required (every field + mark sheet)
    $requiredLevels = ['sslc', 'bachelor', 'master', 'mphil'];
    // HSC / Diploma: at least ONE of the two must be completed
    $eitherLevels   = ['hsc', 'diploma'];
@endphp

<section class="wizard-step" data-step="2" data-step-id="education" hidden>
    <div class="step-head">
        <span class="step-kicker">Educational Qualification</span>
        <h2 class="step-title">Educational Qualification</h2>
        <p class="step-desc">Patterns accepted: 12+3+2 · 12+4+2 · 10+3+3+2 · 12+4+1 · 12+4. Fill the rows that apply to
            you and attach each self-attested mark sheet.</p>
    </div>

    <div class="callout callout--info">
        <strong>Upload mark sheets per qualification</strong>
        The mark-sheet upload for a row unlocks only after you fill that row. SSLC, Bachelor's, Master's and M.Phil. are required; for HSC / Diploma, pick one from the dropdown and fill it.
    </div>

    <div class="edu-table edu-table--6">
        <div class="edu-table__head">
            <span>Certification / Degree</span>
            <span>Subjects / Specialization</span>
            <span>School / College / University</span>
            <span>Month &amp; Year</span>
            <span>Marks / Class / Grade</span>
            <span>Mark sheet</span>
        </div>

        @foreach ($data['education_levels'] as $key => $label)
            @php
                $req      = in_array($key, $requiredLevels);
                $either   = in_array($key, $eitherLevels);
                $reqAttr  = $req ? 'required data-required=true' : '';
                // HSC row is shown first; Diploma row is hidden until chosen in the dropdown
                $hideRow  = $key === 'diploma';
                $docType  = 'marksheet_' . $key;
                $hasSaved = !empty($draft['files'][$docType]['url']);
                $savedUrl = $draft['files'][$docType]['url']  ?? '#';
                $savedNm  = $draft['files'][$docType]['name'] ?? '';
            @endphp
            <div class="edu-row" data-edu-row data-level="{{ $key }}"
                 @if($req) data-edu-required="1" @endif
                 @if($either) data-edu-either="1" @endif
                 @if($either && $hideRow) hidden style="display:none" @endif>
                <div class="edu-row__label">
                    @if($either)
                        <select class="f-input f-input--sm" data-edu-switch aria-label="HSC or Diploma">
                            <option value="hsc"     @selected($key === 'hsc')>HSC</option>
                            <option value="diploma" @selected($key === 'diploma')>Diploma</option>
                        </select>
                        <span class="req" style="color:#dc2626">*</span>
                    @else
                        {{ $label }}@if($req) <span class="req" style="color:#dc2626">*</span>@endif
                    @endif
                </div>

                <div class="edu-cell" data-cell="Subjects / Specialization">
                    <input type="text" name="education[{{ $key }}][subjects]"
                        class="f-input f-input--sm edu-field" {!! $reqAttr !!}>
                </div>
                <div class="edu-cell" data-cell="Institution / University">
                    <input type="text" name="education[{{ $key }}][institution]"
                        class="f-input f-input--sm edu-field" {!! $reqAttr !!}>
                </div>
                <div class="edu-cell" data-cell="Month &amp; Year of Passing">
                    <input type="month" name="education[{{ $key }}][passing]"
                        class="f-input f-input--sm edu-field" {!! $reqAttr !!}>
                </div>
                <div class="edu-cell" data-cell="Marks / Class / Rank / Grade">
                    <input type="text" name="education[{{ $key }}][marks]"
                        class="f-input f-input--sm edu-field" {!! $reqAttr !!}>
                </div>

                <div class="edu-cell edu-cell--file" data-cell="Mark sheet">
                    {{--
                        KEY: data-has-saved set SERVER-SIDE so JS never has a
                        timing window where it sees "no saved file".
                    --}}
                    <div class="edu-up" data-edu-upload
                         @if($hasSaved) data-has-saved="1" @endif>

                        <label class="edu-up__label">
                            <input type="file"
                                name="education[{{ $key }}][marksheet]"
                                accept=".pdf,.docx,.jpg,.jpeg,.png"
                                class="sr-only edu-up__input">
                            <span class="edu-up__btn">⬆ Upload</span>
                        </label>

                        <span class="edu-up__file" hidden>
                            <span class="edu-up__name"></span>
                            <button type="button" class="edu-up__remove"
                                aria-label="Remove file">&times;</button>
                        </span>

                        <div id="saved-marksheet_{{ $key }}"
                             @if(!$hasSaved) hidden @endif
                             class="saved-preview edu-up__saved">
                            <a href="{{ $savedUrl }}"
                               target="_blank"
                               data-saved-url="marksheet_{{ $key }}"
                               data-saved-name="marksheet_{{ $key }}"
                               style="color:var(--primary)">{{ $savedNm }}</a>
                            <button type="button"
                                class="edu-up__saved-remove"
                                data-remove-saved="marksheet_{{ $key }}"
                                aria-label="Remove saved file">&times;</button>
                        </div>

                        <input type="hidden"
                            name="education[{{ $key }}][marksheet_removed]"
                            value="0"
                            data-removed-flag="marksheet_{{ $key }}">
                    </div>
                    <p class="f-error"
                       data-error-for="education[{{ $key }}][marksheet]"></p>
                    <p class="f-error" data-error-for="education[{{ $key }}][row]"></p>
                </div>
            </div>
        @endforeach
    </div>
    <p class="f-hint mt-3">Each file: PDF, DOCX, JPG, JPEG or PNG · max 2 MB.</p>
</section>
