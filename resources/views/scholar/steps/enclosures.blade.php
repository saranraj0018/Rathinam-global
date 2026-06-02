{{-- STEP 7 — List of Enclosures
     Backend field names: enclosures[<key>] (checklist), noc_document (file, PT),
       service_certificate (file, PT), equivalence_cert (file), enclosures_confirm --}}
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

    {{-- Checklist --}}
    <ul class="encl-list">
        @foreach ($data['enclosures'] as $item)
            <li class="encl-list__item @if($item['pt_only']) js-pt-only @endif" @if($item['pt_only']) data-pt-only hidden @endif>
                <label class="inline-check inline-check--block">
                    <input type="checkbox" name="enclosures[{{ $item['key'] }}]" value="1">
                    <span>{{ $item['label'] }}</span>
                    @if ($item['pt_only'])<span class="badge-pt">Part-Time</span>@endif
                </label>
            </li>
        @endforeach
    </ul>

    {{-- Part-Time required uploads --}}
    <div class="js-pt-only" data-pt-only hidden>
        <div class="callout callout--info mt-2">
            <strong>Part-Time applicants</strong>
            The following are mandatory for Part-Time Ph.D. A blank NOC format is shown below for your employer.
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <x-upload name="noc_document" label="No Objection Certificate (NOC)" required data-conditional />
            <x-upload name="service_certificate" label="Service Certificate" required data-conditional />
        </div>

        {{-- NOC reference format (printable) --}}
        <details class="noc-format">
            <summary>View NOC format</summary>
            <div class="noc-format__body">
                <p class="noc-format__title">NO OBJECTION CERTIFICATE (NOC)</p>
                <p>This is to certify that Mr./ Ms. <span class="noc-blank"></span> is employed as
                    <span class="noc-blank"></span> in the Department / Division / Section / Unit of
                    <span class="noc-blank"></span> at this College / School / Polytechnic / Institute / Industry / Company.
                    This organization has no objection in permitting him/her to pursue the Ph.D. Programme under the
                    Part-Time category at Rathinam Global University.</p>
                <div class="noc-format__sign">
                    <span>Date: ____________<br>Place: ____________</span>
                    <span>Signature of the Employer with Office Seal</span>
                </div>
            </div>
        </details>
    </div>

    {{-- Foreign degree --}}
    <x-upload name="equivalence_cert" label="Equivalence Certificate — foreign degree (optional)"
              hint="If applicable. To be produced at the time of admission · PDF/JPG/PNG · max 2 MB" />

    {{-- Final confirmation --}}
    <label class="confirm-box">
        <input type="checkbox" name="enclosures_confirm" value="1" required>
        <span>I confirm that my application is complete and all applicable self-attested documents have been enclosed.</span>
    </label>
    <p class="f-error" data-error-for="enclosures_confirm"></p>
</section>
