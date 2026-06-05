{{-- resources/views/components/timeline-item.blade.php --}}
@props(['title', 'badge' => null, 'sub' => null, 'date' => null, 'last' => false])
<div class="flex gap-3.5 pb-5 relative {{ !$last ? "before:content-[''] before:absolute before:left-[5px] before:top-4 before:-bottom-1 before:w-px before:bg-line" : '' }}">
    <div class="w-[11px] h-[11px] rounded-full bg-gradient-to-br from-accent to-accent2 mt-1.5 shrink-0 ring-4 ring-accent/10"></div>
    <div class="flex-1 bg-surface2 ring-1 ring-inset ring-line rounded-xl px-4 py-3.5">
        <div class="flex justify-between items-center gap-2.5">
            <strong class="text-sm text-text">{{ $title }}</strong>
            @if($badge)<span class="text-xs text-accent font-bold whitespace-nowrap">{{ $badge }}</span>@endif
        </div>
        @if($sub)<p class="mt-1 mb-1.5 text-[13px] text-muted">{{ $sub }}</p>@endif
        @if($date)<span class="text-xs text-muted font-mono">{{ $date }}</span>@endif
    </div>
</div>
