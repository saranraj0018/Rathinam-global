{{-- resources/views/admin/applications/index.blade.php --}}
@extends('admin.app')
@section('title', 'Applications')

@section('content')
    @php
        $statuses = [
            'all' => 'All',
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'under_review' => 'Under Review',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
        // status -> badge utility classes (Tailwind can't build classes from runtime strings)
$badge = [
    'draft' => 'bg-fmuted/10 text-[#a3a9bb]',
    'submitted' => 'bg-[#6b9bf2]/10 text-[#6b9bf2]',
    'under_review' => 'bg-[#e0a94a]/10 text-[#e0a94a]',
    'approved' => 'bg-[#46c08a]/10 text-[#46c08a]',
    'rejected' => 'bg-[#e87878]/10 text-[#e87878]',
];

$sortLink = function ($column, $label) {
    $dir = request('sort') === $column && request('dir') === 'asc' ? 'desc' : 'asc';
    $arrow = request('sort') === $column ? (request('dir') === 'asc' ? ' ↑' : ' ↓') : '';
    $url = route('applications', array_merge(request()->except('page'), ['sort' => $column, 'dir' => $dir]));
    return '<a href="' .
        $url .
        '" class="text-inherit no-underline hover:text-ftext">' .
        $label .
        $arrow .
        '</a>';
        };
    @endphp

    <div
        class="min-h-screen bg-ink text-ftext font-sans text-sm
            [background-image:radial-gradient(1100px_circle_at_88%_-8%,rgba(201,163,90,.10),transparent_42%),radial-gradient(900px_circle_at_-5%_4%,rgba(107,155,242,.07),transparent_40%)]">

        {{-- Topbar --}}
        <header
            class="sticky top-0 z-20 flex items-center justify-between gap-6 px-8 py-[18px]
                   border-b border-line bg-surface/60 backdrop-blur-xl">
            <div class="flex items-center gap-3.5">
                <div
                    class="w-11 h-11 rounded-[13px] grid place-items-center font-display font-semibold text-[23px] text-[#1a1408]
                        bg-gradient-to-br from-gold to-gold2 shadow-[0_8px_24px_-8px_rgba(201,163,90,.6),0_0_0_1px_rgba(230,201,139,.2)_inset]">
                    A</div>
                <div>
                    <div class="text-[10.5px] font-bold tracking-[.16em] uppercase text-gold2/85">Doctoral Programmes ·
                        2026–27</div>
                    <h1 class="font-display text-[19px] font-semibold leading-tight m-0">Admissions Console</h1>
                </div>
            </div>

            <form method="GET" action="{{ route('applications') }}"
                class="flex items-center gap-2.5 w-[420px] px-4 py-2.5 rounded-xl bg-surface border border-line text-fmuted
                     transition focus-within:border-gold focus-within:ring-[3px] focus-within:ring-gold/10">
                @if (request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"
                    class="shrink-0">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
                <input name="q" value="{{ request('q') }}" placeholder="Search by name, application no, or email…"
                    class="bg-transparent border-0 outline-none text-ftext w-full placeholder:text-[#5f6477]">
            </form>
        </header>

        {{-- Page head --}}
        <div class="px-8 pt-[30px] pb-2">
            <h2 class="font-display text-[32px] font-semibold tracking-tight m-0">Application Management</h2>
            <p class="mt-1.5 text-fmuted text-[14.5px]">Review, filter, and process Ph.D. applications across all schools.
            </p>
        </div>

        {{-- Status pills --}}
        <div class="flex gap-2 flex-wrap px-8 pt-5 pb-1">
            @foreach ($statuses as $key => $label)
                @php $isActive = request('status', 'all') === $key; @endphp
                <a href="{{ route('applications', array_merge(request()->except('page'), ['status' => $key])) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[10px] text-[13px] font-semibold no-underline transition
                      {{ $isActive
                          ? 'bg-gradient-to-br from-gold to-gold2 text-[#1a1408] shadow-[0_8px_22px_-10px_rgba(201,163,90,.7)]'
                          : 'bg-surface border border-line text-fmuted hover:border-line2 hover:text-ftext' }}">
                    {{ $label }}
                    <span
                        class="text-[11px] font-extrabold px-[7px] py-px rounded-md
                             {{ $isActive ? 'bg-[#1a1408]/20 text-[#3a2e10]' : 'bg-white/[.06] text-fmuted' }}">
                        {{ $counts[$key] ?? 0 }}
                    </span>
                </a>
            @endforeach
            {{-- Trigger Button --}}
            <a href="javascript:void(0)"
                onclick="event.stopPropagation(); document.getElementById('downloadModal').classList.remove('hidden')"
                title="Download applications"
                class="inline-flex items-center gap-1.5 px-3.5 py-[7px] rounded-[9px] text-[13px] font-semibold no-underline transition bg-gradient-to-br from-gold to-gold2 text-[#1a1408] hover:scale-105">
                ⬇ Download Applications
            </a>

        </div>
        {{-- Table --}}
        <main class="px-8 pt-3.5 pb-[60px] overflow-x-auto">
            <div
                class="bg-surface border border-line rounded-[18px] overflow-hidden
                    shadow-[0_1px_0_rgba(255,255,255,.03)_inset,0_20px_50px_-24px_rgba(0,0,0,.8)]">
                <table class="w-full border-separate border-spacing-0 min-w-[980px]">
                    <thead>
                        <tr
                            class="[&>th]:text-left [&>th]:text-[11px] [&>th]:uppercase [&>th]:tracking-[.08em]
                               [&>th]:text-[#5f6477] [&>th]:font-bold [&>th]:px-[18px] [&>th]:py-[15px]
                               [&>th]:border-b [&>th]:border-line [&>th]:bg-ink2 [&>th]:whitespace-nowrap">
                            <th>{!! $sortLink('application_no', 'Application No') !!}</th>
                            <th>{!! $sortLink('full_name', 'Applicant') !!}</th>
                            <th>Contact</th>
                            <th>Specialization</th>
                            <th>{!! $sortLink('programme_mode', 'Mode') !!}</th>
                            <th>Docs</th>
                            <th>{!! $sortLink('status', 'Status') !!}</th>
                            <th class="text-right">Documents</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $a)
                            @php
                                $url = route('applications_show', $a);
                                if ($a->programme_mode == 'FT' || $a->programme_mode == 'full_time') {
                                    $mode = 'Full Time';
                                } elseif ($a->programme_mode == 'PT' || $a->programme_mode == 'part_time') {
                                    $mode = 'Part Time';
                                } elseif ($a->programme_mode == 'FT-Startup') {
                                    $mode = 'Startup Based Ph.D';
                                } elseif ($a->programme_mode == 'Integrated') {
                                    $mode = 'Integrated PG + Ph.D';
                                } else {
                                    $mode = '';
                                }
                            @endphp
                            <tr onclick="window.location='{{ $url }}'"
                                class="group cursor-pointer transition-colors hover:bg-surface2
                                   [&>td]:px-[18px] [&>td]:py-[15px] [&>td]:border-b [&>td]:border-line [&>td]:align-middle
                                   last:[&>td]:border-b-0">
                                <td class="font-mono text-[13px] font-medium text-gold2">{{ $a->application_no }}</td>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="w-[38px] h-[38px] rounded-[11px] shrink-0 grid place-items-center font-bold text-[12.5px] text-gold2
                                                bg-gradient-to-br from-surface2 to-line2 border border-line2">
                                            {{ \Illuminate\Support\Str::of($a->full_name)->explode(' ')->take(2)->map(fn($n) => $n[0] ?? '')->implode('') }}
                                        </div>
                                        <div>
                                            <strong class="block font-semibold text-ftext">{{ $a->full_name }}</strong>
                                            <span class="text-xs text-fmuted">{{ $a->gender }} · {{ $a->age }}
                                                yrs</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-xs text-fmuted leading-[1.7]">{{ $a->email }}<br>{{ $a->mobile }}
                                </td>
                                <td>{{ $a->specialization ?? '—' }}</td>
                                <td>
                                    <span
                                        class="text-[11.5px] font-medium px-2.5 py-1 rounded-lg bg-surface2 border border-line2 text-fmuted">
                                        {{ $mode }}
                                    </span>
                                </td>
                                <td class="text-[13px] font-medium text-fmuted">📎 {{ $a->documents_count }}</td>
                                <td>
                                    <span
                                        class="inline-flex items-center gap-1.5 px-[11px] py-[5px] rounded-lg text-[11.5px] font-bold whitespace-nowrap
                                             before:content-[''] before:w-1.5 before:h-1.5 before:rounded-full before:bg-current
                                             {{ $badge[$a->status] ?? $badge['draft'] }}">
                                        {{ $statuses[$a->status] ?? $a->status }}
                                    </span>
                                </td>
                                {{-- replace the final <td class="text-right"> ... </td> in each row --}}
                                <td class="text-right whitespace-nowrap">
                                    <div class="inline-flex items-center gap-2">
                                        <a href="{{ $url }}" onclick="event.stopPropagation()"
                                            class="inline-flex items-center gap-1.5 px-3.5 py-[7px] rounded-[9px] text-[13px] font-semibold no-underline transition
                  bg-surface2 border border-line2 text-ftext group-hover:border-gold group-hover:text-gold2">
                                            View →
                                        </a>
                                        @if ($a->documents_count > 0)
                                            <a href="{{ route('download_pdf', $a) }}" onclick="event.stopPropagation()"
                                                title="Download all documents as PDF"
                                                class="inline-flex items-center gap-1.5 px-3.5 py-[7px] rounded-[9px] text-[13px] font-semibold no-underline transition
                      bg-gradient-to-br from-gold to-gold2 text-[#1a1408] hover:scale-105">
                                                ⬇ PDF
                                            </a>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-14 text-fmuted">No applications match your
                                    filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="pt-[22px] px-2 [&_a]:text-fmuted [&_span]:text-fmuted [&_a:hover]:text-gold2">
                {{ $applications->links() }}
            </div>
        </main>
    </div>
    {{-- Download Modal --}}
    <div id="downloadModal" class="hidden fixed inset-0 z-[999]">
        <div onclick="document.getElementById('downloadModal').classList.add('hidden')"
            class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>
        <div class="absolute inset-0 flex items-center justify-center p-4">
            <div
                class="relative max-w-[80px] rounded-2xl bg-surface border border-line text-ftext font-sans
              shadow-[0_1px_0_rgba(255,255,255,.03)_inset,0_30px_80px_-20px_rgba(0,0,0,.8)] overflow-hidden">
                <div
                    class="flex items-start justify-between px-6 py-4 border-b border-line bg-gradient-to-br from-gold/10 to-transparent">
                    <div>
                        <h3 class="font-display text-[17px] font-semibold m-0 leading-tight">Download Applications</h3>
                        <p class="text-[12.5px] text-fmuted mt-1">Filter by type and date range</p>
                    </div>
                    <button type="button" onclick="document.getElementById('downloadModal').classList.add('hidden')"
                        class="grid place-items-center w-8 h-8 rounded-lg text-fmuted hover:text-ftext hover:bg-white/[.06] transition shrink-0">
                        ✕
                    </button>
                </div>
                <form action="{{ route('application.download') }}" method="GET">
                    <div class="px-6 py-5 space-y-5">
                        <div>
                            <label
                                class="block text-[11px] uppercase tracking-[.08em] font-bold text-[#5f6477] mb-2">Application
                                Type</label>
                            <select name="type" required
                                style="background-color:#15171f; color:#fff;
      background-image:url('data:image/svg+xml;utf8,<svg xmlns=%22http://www.w3.org/2000/svg%22 fill=%22none%22 stroke=%22%235f6477%22 stroke-width=%222%22 viewBox=%220 0 24 24%22><path d=%22m6 9 6 6 6-6%22/></svg>');
      background-repeat:no-repeat; background-position:right 12px center; background-size:16px;"
                                class="w-full pl-3 pr-10 py-2.5 rounded-lg border border-line text-[13px]
      focus:outline-none focus:border-gold focus:ring-[3px] focus:ring-gold/10 transition appearance-none cursor-pointer">
                                <option value="submitted">Submitted</option>
                                <option value="draft">Draft</option>
                                <option value="qualified">Qualified</option>
                            </select>
                        </div>
                        {{-- Date range --}}
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label
                                    class="block text-[11px] uppercase tracking-[.08em] font-bold text-[#5f6477] mb-1.5">From
                                    Date</label>
                                <input type="date" name="from_date"
                                    style="background-color:#15171f; color:#fff; color-scheme:dark;"
                                    class="w-full px-3 py-2 rounded-lg border border-line text-[13px]
              focus:outline-none focus:border-gold focus:ring-[3px] focus:ring-gold/10 transition">
                            </div>
                            <div>
                                <label
                                    class="block text-[11px] uppercase tracking-[.08em] font-bold text-[#5f6477] mb-1.5">To
                                    Date</label>
                                <input type="date" name="to_date"
                                    style="background-color:#15171f; color:#fff; color-scheme:dark;"
                                    class="w-full px-3 py-2 rounded-lg border border-line text-[13px]
              focus:outline-none focus:border-gold focus:ring-[3px] focus:ring-gold/10 transition">
                            </div>
                        </div>

                        <p class="text-[11.5px] text-fmuted/70 leading-relaxed">Leave the dates empty to download all
                            records of the selected type.</p>
                    </div>

                    {{-- Footer --}}
                    <div class="flex items-center justify-end gap-2.5 px-6 py-4 border-t border-line bg-ink2">
                        <button type="button" onclick="document.getElementById('downloadModal').classList.add('hidden')"
                            class="px-4 py-2 rounded-[9px] text-[13px] font-semibold text-fmuted bg-surface2 border border-line2 hover:text-ftext hover:border-gold transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[9px] text-[13px] font-semibold
                          bg-gradient-to-br from-gold to-gold2 text-[#1a1408] hover:scale-105 transition">
                            ⬇ Download Excel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') document.getElementById('downloadModal').classList.add('hidden');
        });
    </script>
@endsection
