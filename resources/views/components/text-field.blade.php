@props([
    'name',
    'label',
    'type' => 'text',
    'required' => false,
    'placeholder' => '',
    'value' => '',
    'uppercase' => false,
    'hint' => null,
    'autocomplete' => null,
])

<div class="f-group" {{ $attributes }}>
    <label for="{{ $name }}" class="f-label">
        {{ $label }}
        @if ($required) <span class="f-req">*</span> @endif
    </label>

    <input
        type="{{ $type }}"
        id="{{ $name }}"
        name="{{ $name }}"
        value="{{ old($name, $value) }}"
        placeholder="{{ $placeholder }}"
        @if ($autocomplete) autocomplete="{{ $autocomplete }}" @endif
        @if ($required) required aria-required="true" @endif
        class="f-input @if ($uppercase) uppercase-input @endif" />

    @if ($hint)
        <p class="f-hint">{{ $hint }}</p>
    @endif
    <p class="f-error" data-error-for="{{ $name }}"></p>
</div>
