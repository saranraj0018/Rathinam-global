{{-- resources/views/components/field.blade.php --}}
@props(['label', 'value'])
<div class="flex flex-col gap-1">
    <span class="text-[11px] uppercase tracking-[.06em] text-muted font-semibold">{{ $label }}</span>
    <span class="text-sm font-medium text-text">{{ filled($value) ? $value : '—' }}</span>
</div>
