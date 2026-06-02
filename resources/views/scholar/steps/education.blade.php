{{-- STEP 3 — Educational Qualification
     Backend field names: education[<level>][subjects|institution|passing|marks],
       education_documents (file — single merged PDF/image of all mark sheets) --}}
@php
    // Bachelor's is the universal minimum (FT / PT / Integrated / Start-up all hold a UG).
    // Master's is left optional so Integrated applicants are not wrongly blocked.
    $requiredLevels = ['bachelor'];
@endphp
<section class="wizard-step" data-step="2" data-step-id="education" hidden>
    <div class="step-head">
        <span class="step-kicker">Educational Qualification</span>
        <h2 class="step-title">Educational Qualification</h2>
        <p class="step-desc">Patterns accepted: 12+3+2 · 12+4+2 · 10+3+3+2 · 12+4+1 · 12+4. Fill the rows that apply to you.</p>
    </div>

    <div class="edu-table">
        <div class="edu-table__head">
            <span>Certification / Degree</span>
            <span>Subjects / Specialization</span>
            <span>School / College / University</span>
            <span>Month &amp; Year</span>
            <span>Marks / Class / Grade</span>
        </div>

        @foreach ($data['education_levels'] as $key => $label)
            @php $req = in_array($key, $requiredLevels); @endphp
            <div class="edu-row">
                <div class="edu-row__label">
                    {{ $label }} @if ($req)<span class="f-req">*</span>@endif
                </div>
                <div class="edu-cell" data-cell="Subjects / Specialization">
                    <input type="text" name="education[{{ $key }}][subjects]" class="f-input f-input--sm" @if($req) data-required="true" @endif>
                </div>
                <div class="edu-cell" data-cell="Institution / University">
                    <input type="text" name="education[{{ $key }}][institution]" class="f-input f-input--sm" @if($req) data-required="true" @endif>
                </div>
                <div class="edu-cell" data-cell="Month &amp; Year of Passing">
                    <input type="month" name="education[{{ $key }}][passing]" class="f-input f-input--sm" @if($req) data-required="true" @endif>
                </div>
                <div class="edu-cell" data-cell="Marks / Class / Rank / Grade">
                    <input type="text" name="education[{{ $key }}][marks]" class="f-input f-input--sm" @if($req) data-required="true" @endif>
                </div>
            </div>
        @endforeach
    </div>

    <div class="callout callout--info mt-6">
        <strong>Upload your mark sheets &amp; certificates</strong>
        Merge <em>all</em> mark sheets / certificates (SSLC, HSC, UG, PG, etc.) into a
        <strong>single PDF</strong> (or one image) and upload it here — no need to attach them separately.
    </div>

    <x-upload name="education_documents" label="Consolidated mark sheets &amp; certificates (single file)" required
              accept=".pdf,.jpg,.jpeg,.png"
              hint="One merged PDF preferred · PDF/JPG/PNG · max 2 MB" />
</section>
