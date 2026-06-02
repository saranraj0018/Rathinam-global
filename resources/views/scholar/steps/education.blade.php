@php
    // Bachelor's is the universal minimum (FT / PT / Integrated / Start-up all hold a UG).
    $requiredLevels = ['bachelor'];
@endphp
<section class="wizard-step" data-step="2" data-step-id="education" hidden>
    <div class="step-head">
        <span class="step-kicker">Educational Qualification</span>
        <h2 class="step-title">Educational Qualification</h2>
        <p class="step-desc">Patterns accepted: 12+3+2 · 12+4+2 · 10+3+3+2 · 12+4+1 · 12+4. Fill the rows that apply to you and attach each self-attested mark sheet.</p>
    </div>

    <div class="callout callout--info">
        <strong>Upload mark sheets per qualification</strong>
        The mark-sheet upload for a row unlocks only after you fill that row completely (subjects, institution, month/year and marks).
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
            @php $req = in_array($key, $requiredLevels); @endphp
            <div class="edu-row" data-edu-row>
                <div class="edu-row__label">
                    {{ $label }} @if ($req)<span class="f-req">*</span>@endif
                </div>
                <div class="edu-cell" data-cell="Subjects / Specialization">
                    <input type="text" name="education[{{ $key }}][subjects]" class="f-input f-input--sm edu-field" @if($req) data-required="true" @endif>
                </div>
                <div class="edu-cell" data-cell="Institution / University">
                    <input type="text" name="education[{{ $key }}][institution]" class="f-input f-input--sm edu-field" @if($req) data-required="true" @endif>
                </div>
                <div class="edu-cell" data-cell="Month &amp; Year of Passing">
                    <input type="month" name="education[{{ $key }}][passing]" class="f-input f-input--sm edu-field" @if($req) data-required="true" @endif>
                </div>
                <div class="edu-cell" data-cell="Marks / Class / Rank / Grade">
                    <input type="text" name="education[{{ $key }}][marks]" class="f-input f-input--sm edu-field" @if($req) data-required="true" @endif>
                </div>
                <div class="edu-cell edu-cell--file" data-cell="Mark sheet">
                    <div class="edu-up" data-edu-upload>
                        <label class="edu-up__label">
                            <input type="file" name="education[{{ $key }}][marksheet]" accept=".pdf,.jpg,.jpeg,.png"
                                   class="sr-only edu-up__input" @if($req) data-required="true" @endif disabled>
                            <span class="edu-up__btn">⬆ Upload</span>
                        </label>
                        <span class="edu-up__file" hidden>
                            <span class="edu-up__name"></span>
                            <button type="button" class="edu-up__remove" aria-label="Remove file">&times;</button>
                        </span>
                    </div>
                    <p class="f-error" data-error-for="education[{{ $key }}][marksheet]"></p>
                </div>
            </div>
        @endforeach
    </div>
    <p class="f-hint mt-3">Each file: PDF, JPG, JPEG or PNG · max 2 MB.</p>
</section>
