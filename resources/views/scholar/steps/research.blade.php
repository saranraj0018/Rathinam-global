<section class="wizard-step" data-step="5" data-step-id="research" hidden>
    <div class="step-head">
        <span class="step-kicker">Research Profile</span>
        <h2 class="step-title">Projects, Courses &amp; Aspirations</h2>
        <p class="step-desc">Share your project / start-up / hackathon record and your research direction.</p>
    </div>

    {{-- Projects --}}
    <span class="f-label">Projects (UG &amp; PG) / Thesis (M.Phil.) / Start-up / Ideathon or Hackathon</span>
    <div class="rep-table mt-2" data-repeat="projects">
        <div class="rep-head rep-head--proj">
            <span>#</span>
            <span>Title of project / Start-up / Event</span>
            <span>Outcome</span>
            <span></span>
        </div>
        <div data-repeat-body>
            <div class="rep-row rep-row--proj" data-repeat-row>
                <span class="rep-row__num" data-repeat-num>1</span>
                <input type="text" name="projects[0][title]" class="f-input f-input--sm" data-cell="Title">
                <select name="projects[0][status]" class="f-input f-input--sm f-select" data-cell="Outcome">
                    <option value="">—</option>
                    <option>Completed</option>
                    <option>Participated</option>
                    <option>Won</option>
                </select>
                <button type="button" class="rep-remove" data-repeat-remove aria-label="Remove row">&times;</button>
            </div>
        </div>
        <template data-repeat-template>
            <div class="rep-row rep-row--proj" data-repeat-row>
                <span class="rep-row__num" data-repeat-num></span>
                <input type="text" name="projects[__INDEX__][title]" class="f-input f-input--sm" data-cell="Title">
                <select name="projects[__INDEX__][status]" class="f-input f-input--sm f-select" data-cell="Outcome">
                    <option value="">—</option>
                    <option>Completed</option>
                    <option>Participated</option>
                    <option>Won</option>
                </select>
                <button type="button" class="rep-remove" data-repeat-remove aria-label="Remove row">&times;</button>
            </div>
        </template>
        <button type="button" class="btn btn-add" data-repeat-add>+ Add project / event</button>
    </div>

    {{-- Mandatory courses --}}
    <div class="f-group mt-7">
        <span class="f-label">Courses completed during UG / PG / M.Phil. studies <span class="f-req">*</span></span>
        <div class="course-list">
            @foreach ($data['mandatory_courses'] as $key => $title)
                <div class="course-row" data-validate-radio="course_{{ $key }}">
                    <span class="course-row__title">{{ $title }}</span>
                    <div class="yesno">
                        <label class="choice-card choice-card--xs">
                            <input type="radio" name="course_{{ $key }}" value="Yes" required>
                            <span class="choice-card__box"><span class="choice-card__title">Yes</span></span>
                        </label>
                        <label class="choice-card choice-card--xs">
                            <input type="radio" name="course_{{ $key }}" value="No" required>
                            <span class="choice-card__box"><span class="choice-card__title">No</span></span>
                        </label>
                    </div>
                    <p class="f-error" data-error-for="course_{{ $key }}"></p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Career aspirations --}}
    <div class="f-group">
        <span class="f-label">Career Aspirations <span class="f-req">*</span></span>
        <p class="f-hint mb-2">Select all that apply.</p>
        <div class="choice-grid choice-grid--3" data-validate-checkbox="career_aspirations">
            @foreach ($data['career_aspirations'] as $aspiration)
                <label class="choice-card choice-card--check choice-card--sm">
                    <input type="checkbox" name="career_aspirations[]" value="{{ $aspiration }}"
                        @if ($aspiration === 'Any other') data-reveal-target="#career-other-wrap" @endif>
                    <span class="choice-card__box"><span class="choice-card__title">{{ $aspiration }}</span></span>
                </label>
            @endforeach
        </div>
        <div id="career-other-wrap" class="mt-3" hidden>
            <x-text-field name="career_other" label="Please specify your aspiration" />
        </div>
        <p class="f-error" data-error-for="career_aspirations"></p>
    </div>
  @php
    // A file already exists for this field?  Adjust source to match your view.
    $summaryUploaded = !empty($draft['files']['summary_document']['url'] ?? null);
@endphp

<div class="f-group">
    <label class="f-label">One-page Ph.D. focus / theme summary <span class="f-req">*</span></label>

    <div class="saved-file-preview" id="saved-summary_document" @unless($summaryUploaded) hidden @endunless>
        <div class="saved-file-box">
            <div class="saved-file-info">
                <span class="saved-file-name" data-saved-name="summary_document">
                    {{ $draft['files']['summary_document']['name'] ?? '' }}
                </span>
                <a class="saved-file-link" data-saved-url="summary_document"
                   target="_blank" href="{{ $draft['files']['summary_document']['url'] ?? '#' }}">View uploaded file</a>
            </div>
            <span class="saved-file-badge">Already uploaded</span>
        </div>
        <p class="f-hint">Re-upload below to replace the existing file.</p>
    </div>

    {{-- required ONLY when no file has been uploaded yet --}}
    <x-upload name="summary_document" label="" :required="!$summaryUploaded"
              hint="PDF preferred · PDF/JPG/PNG · max 2 MB" />
</div>
</section>
