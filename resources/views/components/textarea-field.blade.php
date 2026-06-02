@props([
    'name',
    'label',
    'required' => false,
    'placeholder' => '',
    'value' => '',
    'rows' => 3,
    'hint' => null,
])

<div class="f-group" {{ $attributes }}>
    <label for="{{ $name }}" class="f-label">
        {{ $label }}
        @if ($required) <span class="f-req">*</span> @endif
    </label>

    <textarea
        id="{{ $name }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        placeholder="{{ $placeholder }}"
        @if ($required) required aria-required="true" @endif
        class="f-input f-textarea">{{ old($name, $value) }}</textarea>

    @if ($hint)
        <p class="f-hint">{{ $hint }}</p>
    @endif
    <p class="f-error" data-error-for="{{ $name }}"></p>
</div>
