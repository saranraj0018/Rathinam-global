<section class="wizard-step" data-step="6" data-step-id="enclosures" hidden>
    <div class="step-head">
        <span class="step-kicker">Enclosures</span>
        <h2 class="step-title">List of Enclosures</h2>
        <p class="step-desc">Tick the documents you are enclosing. Items shown adapt to your programme mode.</p>
    </div>

    <div class="callout callout--warn">
        <strong>Important</strong>
        Ensure the application is complete in all respects with all required documents. Incomplete applications,
        or those without the necessary supporting documents, will be rejected and no interim correspondence will be entertained.
    </div>

    <p class="f-hint" style="margin-bottom:10px">
        These tick automatically from the documents you uploaded in the earlier steps. Any item marked
        <strong>Upload pending</strong> must be uploaded in its step before you continue. Tick the foreign-degree item manually if it applies to you.
    </p>
    <ul class="encl-list">
        @foreach ($data['enclosures'] as $item)
            @php $manual = empty($item['source']); @endphp
            <li class="encl-list__item @if($item['pt_only']) js-pt-only @endif"
                @if($item['pt_only']) data-pt-only hidden @endif
                @if(! $manual) data-encl-source="{{ $item['source'] }}" @endif>
                <label class="inline-check inline-check--block">
                    <input type="checkbox" name="enclosures[{{ $item['key'] }}]" value="1"
                           @if(! $manual) data-encl-auto disabled @endif>
                    <span>{{ $item['label'] }}</span>
                    @if ($item['pt_only'])<span class="badge-pt">Part-Time</span>@endif
                </label>
                @unless ($manual)
                    <span class="encl-status" data-encl-status></span>
                @endunless
            </li>
        @endforeach
    </ul>

    <div class="js-pt-only" data-pt-only hidden>
        <div class="callout callout--info mt-2">
            <strong>Part-Time applicants</strong>
            NOC and Service Certificate are mandatory. Download the NOC format, get it signed &amp; sealed by your employer, then upload it.
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <x-upload name="noc_document" label="No Objection Certificate (NOC)" required data-conditional />
                <a href="{{ asset('downloads/noc-format.pdf') }}" download class="noc-download">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                        <polyline points="7 10 12 15 17 10" />
                        <line x1="12" y1="15" x2="12" y2="3" />
                    </svg>
                    Download NOC format (PDF)
                </a>
            </div>
            <x-upload name="service_certificate" label="Service Certificate" required data-conditional />
        </div>
    </div>

    <x-upload name="equivalence_cert" label="Equivalence Certificate — foreign degree (optional)"
              hint="If applicable. To be produced at the time of admission · PDF/JPG/PNG · max 2 MB" />

    <label class="confirm-box">
        <input type="checkbox" name="enclosures_confirm" value="1" required>
        <span>I confirm that my application is complete and all applicable self-attested documents have been enclosed.</span>
    </label>
    <p class="f-error" data-error-for="enclosures_confirm"></p>
</section>
