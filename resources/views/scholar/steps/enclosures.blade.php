@php
    // Saved files keyed by document_type (for the previews / sync JS).
    $draftFiles = data_get($draft ?? [], 'files', []);
    $fileOf = fn($k) => $draftFiles[$k] ?? null;
    $hasFile = fn($k) => !empty($draftFiles[$k]['url'] ?? null);

    // Per-enclosure status from the controller:
    //   [ key => ['checked' => bool, 'doc_type' => string|null] ]
    $enclStatus = $enclosureStatus ?? [];
@endphp

<section class="wizard-step" data-step="6" data-step-id="enclosures" hidden>
    {{-- Saved files for the enclosure sync JS, keyed by document_type. --}}
    <script id="saved-files-data" type="application/json">@json($draftFiles ?: (object)[], JSON_UNESCAPED_UNICODE)</script>

    <div class="step-head">
        <span class="step-kicker">Enclosures</span>
        <h2 class="step-title">List of Enclosures</h2>
        <p class="step-desc">Tick the documents you are enclosing. Items shown adapt to your programme mode.</p>
    </div>

    <div class="callout callout--warn">
        <strong>Important</strong>
        Ensure the application is complete in all respects with all required documents. Incomplete applications,
        or those without the necessary supporting documents, will be rejected and no interim correspondence will be
        entertained.
    </div>

    <p class="f-hint" style="margin-bottom:10px">
        These tick automatically from the documents you uploaded in the earlier steps. Any item marked
        <strong>Upload pending</strong> must be uploaded in its step before you continue. Tick the foreign-degree item
        manually if it applies to you.
    </p>
    <ul class="encl-list">
        @foreach ($data['enclosures'] as $item)
            @php
                $manual = empty($item['source']);
                $docType = $enclStatus[$item['key']]['doc_type'] ?? null; // resolved document_type
                $hasSaved = !$manual && (bool) ($enclStatus[$item['key']]['checked'] ?? false);
                $manualChecked = $manual && (bool) ($enclStatus[$item['key']]['checked'] ?? false);
            @endphp
            <li class="encl-list__item @if ($item['pt_only']) js-pt-only @endif"
                @if ($item['pt_only']) data-pt-only hidden @endif
                @if (!$manual) data-encl-source="{{ $docType }}" @endif>
                <label class="inline-check inline-check--block">
                    <input type="checkbox" name="enclosures[{{ $item['key'] }}]" value="1"
                        @if (!$manual) data-encl-auto disabled @endif @checked($hasSaved || $manualChecked)>
                    <span>{{ $item['label'] }}</span>
                    @if ($item['pt_only'])
                        <span class="badge-pt">Part-Time</span>
                    @endif
                </label>
                @unless ($manual)
                    <span class="encl-status {{ $hasSaved ? 'is-ok' : 'is-pending' }}" data-encl-status>
                        {{ $hasSaved ? '✓ Uploaded' : 'Upload pending' }}
                    </span>
                @endunless
            </li>
        @endforeach
    </ul>

    <div class="js-pt-only" data-pt-only hidden>
        <div class="callout callout--info mt-2">
            <strong>Part-Time applicants</strong>
            NOC and Service Certificate are mandatory. Download the NOC format, get it signed &amp; sealed by your
            employer, then upload it.
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                @php $nocSaved = $hasFile('noc_document'); @endphp
                @if ($nocSaved)
                    <div class="saved-file-preview" id="saved-noc_document">
                        <div class="saved-file-box">
                            <div class="saved-file-info">
                                <span class="saved-file-name"
                                    data-saved-name="noc_document">{{ $fileOf('noc_document')['name'] ?? '' }}</span>
                                <a class="saved-file-link" data-saved-url="noc_document" target="_blank"
                                    href="{{ $fileOf('noc_document')['url'] }}">View uploaded file</a>
                            </div>
                            <span class="saved-file-badge">Already uploaded</span>
                        </div>
                        <p class="f-hint">Re-upload below to replace the existing file.</p>
                    </div>
                @endif
                <x-upload name="noc_document" label="No Objection Certificate (NOC)" data-conditional />
                <p class="f-hint">
                    NOC submission is mandatory and must be submitted on your joining date.
                </p>
                <a href="{{ asset('downloads/noc-format.pdf') }}" download class="noc-download">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    Download NOC format (PDF)
                </a>
            </div>
            <div>
                @php $svcSaved = $hasFile('service_certificate'); @endphp
                @if ($svcSaved)
                    <div class="saved-file-preview" id="saved-service_certificate">
                        <div class="saved-file-box">
                            <div class="saved-file-info">
                                <span class="saved-file-name"
                                    data-saved-name="service_certificate">{{ $fileOf('service_certificate')['name'] ?? '' }}</span>
                                <a class="saved-file-link" data-saved-url="service_certificate" target="_blank"
                                    href="{{ $fileOf('service_certificate')['url'] }}">View uploaded file</a>
                            </div>
                            <span class="saved-file-badge">Already uploaded</span>
                        </div>
                        <p class="f-hint">Re-upload below to replace the existing file.</p>
                    </div>
                @endif
                <x-upload name="service_certificate" label="Service Certificate" :required="!$svcSaved" data-conditional />
            </div>
        </div>
    </div>

    @php $equivSaved = $hasFile('equivalence_cert'); @endphp
    @if ($equivSaved)
        <div class="saved-file-preview" id="saved-equivalence_cert">
            <div class="saved-file-box">
                <div class="saved-file-info">
                    <span class="saved-file-name"
                        data-saved-name="equivalence_cert">{{ $fileOf('equivalence_cert')['name'] ?? '' }}</span>
                    <a class="saved-file-link" data-saved-url="equivalence_cert" target="_blank"
                        href="{{ $fileOf('equivalence_cert')['url'] }}">View uploaded file</a>
                </div>
                <span class="saved-file-badge">Already uploaded</span>
            </div>
            <p class="f-hint">Re-upload below to replace the existing file.</p>
        </div>
    @endif
    <x-upload name="equivalence_cert" label="Equivalence Certificate — foreign degree (optional)"
        hint="If applicable. To be produced at the time of admission · PDF/JPG/PNG · max 2 MB" />

    <label class="confirm-box">
        <input type="checkbox" name="enclosures_confirm" value="1" required @checked(!empty(data_get($draft ?? [], 'enclosures_confirm')))>
        <span>I confirm that my application is complete and all applicable self-attested documents have been
            enclosed.</span>
    </label>
    <p class="f-error" data-error-for="enclosures_confirm"></p>
</section>
