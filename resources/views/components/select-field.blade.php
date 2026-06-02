@props([
    'name',
    'label',
    'options' => [],          // ['value' => 'Label'] or ['Value', 'Value', ...]
    'required' => false,
    'placeholder' => '— Select —',
    'value' => '',
    'hint' => null,
])

<div class="f-group" {{ $attributes }}>
    <label for="{{ $name }}" class="f-label">
        {{ $label }}
        @if ($required) <span class="f-req">*</span> @endif
    </label>

    <select
        id="{{ $name }}"
        name="{{ $name }}"
        @if ($required) required aria-required="true" @endif
        class="f-input f-select">
        <option value="" disabled @selected(old($name, $value) === '')>{{ $placeholder }}</option>
        @foreach ($options as $key => $text)
            @php $optValue = is_int($key) ? $text : $key; @endphp
            <option value="{{ $optValue }}" @selected(old($name, $value) === $optValue)>{{ $text }}</option>
        @endforeach
    </select>

    @if ($hint)
        <p class="f-hint">{{ $hint }}</p>
    @endif
    <p class="f-error" data-error-for="{{ $name }}"></p>
</div>
