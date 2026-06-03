{{-- resources/views/admin/applications/index.blade.php --}}
@extends('admin.app')
@section('title', 'Applications')

@push('styles')
{{-- Tailwind config: light "RGU" admissions palette + display/body fonts --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    bg:       '#f4f2f8',   // page wash (cool lilac-grey)
                    surface:  '#ffffff',   // cards
                    surface2: '#f7f6fb',   // subtle fills
                    line:     '#ece9f2',   // hairline borders
                    text:     '#181423',   // near-black ink
                    muted:    '#736e82',   // secondary text
                    accent:   '#7c3aed',   // violet (brand)
                    accent2:  '#9d5cf5',   // lighter violet
                    'brand-ink': '#1c1530',// deep aubergine (hero band)
                    'st-green':  '#16a34a',
                    'st-blue':   '#2563eb',
                    'st-amber':  '#d97706',
                    'st-red':    '#dc2626',
                },
                fontFamily: {
                    sans:    ['"Plus Jakarta Sans"', 'system-ui', 'sans-serif'],
                    display: ['Fraunces', 'Georgia', 'serif'],
                    mono:    ['ui-monospace', 'SFMono-Regular', 'monospace'],
                },
                boxShadow: {
                    card: '0 1px 2px rgba(28,21,48,.04), 0 8px 24px -12px rgba(28,21,48,.12)',
                    pop:  '0 12px 40px -12px rgba(124,58,237,.35)',
                },
            },
        },
    };
</script>
@endpush

@section('content')
<div class="min-h-screen bg-bg text-text font-sans text-sm">

    {{-- ───────────── Hero band (deep aubergine, like the screenshot) ───────────── --}}
    <div class="relative overflow-hidden bg-brand-ink text-white
                [background-image:radial-gradient(900px_circle_at_12%_-40%,rgba(157,92,245,.45),transparent_55%),radial-gradient(700px_circle_at_92%_-20%,rgba(34,197,94,.18),transparent_50%)]">
        <header class="relative flex items-center justify-between gap-6 px-8 py-5">
            <div class="flex items-center gap-3.5">
                <div class="w-[44px] h-[44px] rounded-[13px] bg-gradient-to-br from-accent2 to-st-green
                            grid place-items-center font-display font-semibold text-[22px] text-white shadow-pop">A</div>
                <div>
                    <p class="text-[11px] font-semibold tracking-[.18em] text-accent2/90 uppercase">Doctoral Programmes · 2026–27</p>
                    <h1 class="font-display text-xl font-semibold leading-tight m-0">Admissions Console</h1>
                </div>
            </div>

            <form method="GET" action="{{ route('applications') }}"
                  class="flex items-center gap-2.5 bg-white/[.08] border border-white/15 rounded-full px-4 py-2.5 w-[420px]
                         backdrop-blur-md focus-within:border-accent2 focus-within:bg-white/[.12] transition-colors">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" class="text-white/60 shrink-0">
                    <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                </svg>
                <input name="q" value="{{ request('q') }}"
                       placeholder="Search by name, application no, or email…"
                       class="bg-transparent border-none outline-none text-white w-full placeholder:text-white/50">
            </form>
        </header>

        <div class="relative px-8 pb-12 pt-4">
            <h2 class="font-display text-4xl sm:text-[42px] font-semibold tracking-tight m-0">Application Management</h2>
            <p class="mt-2 text-white/55 text-[15px]">Review, filter, and process Ph.D. applications across all schools.</p>
        </div>
    </div>

    @php
        $statuses = [
            'all' => 'All', 'draft' => 'Draft', 'submitted' => 'Submitted',
            'under_review' => 'Under Review', 'approved' => 'Approved', 'rejected' => 'Rejected',
        ];
        $badgeClasses = [
            'draft'        => 'bg-muted/10 text-muted ring-1 ring-inset ring-muted/20',
            'submitted'    => 'bg-st-blue/10 text-st-blue ring-1 ring-inset ring-st-blue/20',
            'under_review' => 'bg-st-amber/10 text-st-amber ring-1 ring-inset ring-st-amber/20',
            'approved'     => 'bg-st-green/10 text-st-green ring-1 ring-inset ring-st-green/20',
            'rejected'     => 'bg-st-red/10 text-st-red ring-1 ring-inset ring-st-red/20',
        ];
    @endphp

    {{-- ───────────── Content card lifts over the hero ───────────── --}}
    <div class="px-4 sm:px-8 -mt-6 pb-16 relative">
        <div class="bg-surface rounded-[20px] border border-line shadow-card overflow-hidden">

            {{-- Status pills --}}
            <div class="flex gap-2 flex-wrap px-6 sm:px-7 pt-6 pb-5 border-b border-line">
                @foreach($statuses as $key => $label)
                    @php $isActive = request('status', 'all') === $key; @endphp
                    <a href="{{ route('applications', array_merge(request()->except('page'), ['status' => $key])) }}"
                       class="flex items-center gap-2 px-3.5 py-2 rounded-full text-[13px] font-semibold no-underline transition-all duration-150
                              {{ $isActive
                                  ? 'bg-gradient-to-r from-accent to-accent2 text-white shadow-pop'
                                  : 'bg-surface2 text-muted ring-1 ring-inset ring-line hover:ring-accent/40 hover:text-text' }}">
                        {{ $label }}
                        <span class="px-[7px] py-px rounded-full text-[11px] font-bold tabular-nums
                                     {{ $isActive ? 'bg-white/25 text-white' : 'bg-white text-muted ring-1 ring-inset ring-line' }}">
                            {{ $counts[$key] ?? 0 }}
                        </span>
                    </a>
                @endforeach
            </div>

            {{-- Table --}}
            <main class="px-1.5 sm:px-3 pt-1 pb-2 overflow-x-auto">
                @php
                    $sortLink = function($column, $label) {
                        $dir   = (request('sort') === $column && request('dir') === 'asc') ? 'desc' : 'asc';
                        $active = request('sort') === $column;
                        $arrow = $active ? (request('dir') === 'asc' ? ' ↑' : ' ↓') : '';
                        $url   = route('applications', array_merge(request()->except('page'), ['sort' => $column, 'dir' => $dir]));
                        $cls   = $active ? 'text-accent' : 'text-muted hover:text-text';
                        return '<a href="'.$url.'" class="'.$cls.' no-underline transition-colors">'.$label.$arrow.'</a>';
                    };
                @endphp

                <table class="w-full border-separate border-spacing-0 min-w-[980px]">
                    <thead>
                        <tr class="[&>th]:text-left [&>th]:text-[11px] [&>th]:uppercase [&>th]:tracking-[.08em]
                                   [&>th]:text-muted [&>th]:font-bold [&>th]:px-4 [&>th]:py-3.5
                                   [&>th]:whitespace-nowrap">
                            <th>{!! $sortLink('application_no', 'Application No') !!}</th>
                            <th>{!! $sortLink('full_name', 'Applicant') !!}</th>
                            <th>Contact</th>
                            <th>Specialization</th>
                            <th>{!! $sortLink('programme_mode', 'Mode') !!}</th>
                            <th>Docs</th>
                            <th>{!! $sortLink('status', 'Status') !!}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $a)
                            @php $url = route('applications_show', $a); @endphp
                            <tr onclick="window.location='{{ $url }}'"
                                class="group cursor-pointer transition-colors hover:bg-surface2
                                       [&>td]:px-4 [&>td]:py-3.5 [&>td]:border-t [&>td]:border-line [&>td]:align-middle">
                                <td class="font-mono text-[13px] font-medium text-accent">{{ $a->application_no }}</td>
                                <td>
                                    <div class="flex items-center gap-[11px]">
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-accent to-accent2
                                                    grid place-items-center font-bold text-[12px] text-white shrink-0">
                                            {{ \Illuminate\Support\Str::of($a->full_name)->explode(' ')->take(2)->map(fn($n) => $n[0] ?? '')->implode('') }}
                                        </div>
                                        <div>
                                            <strong class="block font-semibold text-text">{{ $a->full_name }}</strong>
                                            <span class="text-xs text-muted">{{ $a->gender }} · {{ $a->age }} yrs</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-xs text-muted block">{{ $a->email }}</span>
                                    <span class="text-xs text-muted">{{ $a->mobile }}</span>
                                </td>
                                <td class="text-[13px]">{{ $a->specialization ?? '—' }}</td>
                                <td>
                                    <span class="text-xs px-2.5 py-1 rounded-full bg-surface2 ring-1 ring-inset ring-line text-muted font-medium">
                                        {{ $a->programme_mode === 'full_time' ? 'Full Time' : 'Part Time' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="inline-flex items-center gap-[5px] text-[13px] text-muted font-medium">📎 {{ $a->documents_count }}</span>
                                </td>
                                <td>
                                    <span class="inline-flex items-center gap-[5px] px-2.5 py-[5px] rounded-full text-xs font-semibold whitespace-nowrap
                                                 {{ $badgeClasses[$a->status] ?? $badgeClasses['draft'] }}">
                                        {{ $statuses[$a->status] ?? $a->status }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="{{ $url }}" onclick="event.stopPropagation()"
                                       class="inline-flex items-center gap-1.5 bg-surface2 ring-1 ring-inset ring-line text-text
                                              px-3.5 py-[7px] rounded-full text-[13px] font-semibold no-underline transition-all duration-150
                                              group-hover:ring-accent/40 hover:!bg-gradient-to-r hover:!from-accent hover:!to-accent2 hover:!text-white hover:!ring-transparent">
                                        View →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-16 text-muted">No applications match your filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </main>
        </div>

        {{-- Pagination --}}
        <div class="px-2 pt-5 [&_nav]:flex [&_nav]:justify-between [&_nav>p]:text-muted [&_nav>p]:text-[13px]
                    [&_a]:text-accent [&_span]:text-muted">
            {{ $applications->links() }}
        </div>
    </div>
</div>
@endsection
