@props([
    'name',
    'label',
    'required' => false,
    'image' => false,                          // true => passport photo (image-only, shows thumbnail)
    'accept' => '.pdf,.jpg,.jpeg,.png',
    'maxKb' => 2048,                           // 2 MB
    'hint' => null,
])

@php
    $accept = $image ? 'image/png,image/jpeg,.png,.jpg,.jpeg' : $accept;
    $defaultHint = $image ? 'JPG, JPEG or PNG · max 2 MB' : 'PDF, JPG, JPEG or PNG · max 2 MB';
@endphp

<div class="f-group js-upload" data-max-kb="{{ $maxKb }}" data-image="{{ $image ? '1' : '0' }}" {{ $attributes }}>
    <label class="f-label" for="{{ $name }}">
        {{ $label }}
        @if ($required) <span class="f-req">*</span> @endif
    </label>

    {{-- Dropzone --}}
    <label class="upload-zone" data-zone>
        <input
            type="file"
            id="{{ $name }}"
            name="{{ $name }}"
            accept="{{ $accept }}"
            @if ($required) data-required="true" @endif
            class="sr-only js-upload-input" />
        <span class="upload-zone__icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
                <polyline points="17 8 12 3 7 8" />
                <line x1="12" y1="3" x2="12" y2="15" />
            </svg>
        </span>
        <span class="upload-zone__text">
            <span class="upload-zone__cta">Click to upload</span> or drag &amp; drop
        </span>
        <span class="upload-zone__hint">{{ $hint ?? $defaultHint }}</span>
    </label>

    {{-- Selected-file preview (revealed by JS) --}}
    <div class="upload-file" data-file hidden>
        <img class="upload-file__thumb" data-thumb alt="preview" hidden />
        <span class="upload-file__doc" data-doc hidden aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
            </svg>
        </span>
        <span class="upload-file__meta">
            <span class="upload-file__name" data-name></span>
            <span class="upload-file__size" data-size></span>
        </span>
        <button type="button" class="upload-file__remove" data-remove aria-label="Remove file">&times;</button>
    </div>

    <p class="f-error" data-error-for="{{ $name }}"></p>
</div>
