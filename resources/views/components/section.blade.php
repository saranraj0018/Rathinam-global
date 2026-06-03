{{-- resources/views/components/section.blade.php --}}
@props(['title', 'last' => false])
<section class="{{ $last ? '' : 'mb-8' }}">
    <h3 class="text-[12px] font-bold text-accent uppercase tracking-[.08em] mt-0 mb-4 pb-2.5 border-b border-line">{{ $title }}</h3>
    {{ $slot }}
</section>
