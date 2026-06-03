{{-- resources/views/admin/applications/show.blade.php --}}
@extends('admin.app')
@section('title', $application->application_no)

@section('content')
@php
    $statuses = ['draft'=>'Draft','submitted'=>'Submitted','under_review'=>'Under Review','approved'=>'Approved','rejected'=>'Rejected'];
    $badge = [
        'draft'        => 'bg-fmuted/10 text-[#a3a9bb]',
        'submitted'    => 'bg-[#6b9bf2]/10 text-[#6b9bf2]',
        'under_review' => 'bg-[#e0a94a]/10 text-[#e0a94a]',
        'approved'     => 'bg-[#46c08a]/10 text-[#46c08a]',
        'rejected'     => 'bg-[#e87878]/10 text-[#e87878]',
    ];
    $projStatus = [
        'completed'    => 'bg-[#46c08a]/10 text-[#46c08a]',
        'won'          => 'bg-gold/[.16] text-gold2',
        'participated' => 'bg-[#6b9bf2]/10 text-[#6b9bf2]',
        'pending'      => 'bg-fmuted/10 text-[#a3a9bb]',
    ];
    $app = $application;
@endphp

<div x-data="{ tab: 'profile' }"
     class="min-h-screen bg-ink text-ftext font-sans text-sm
            [background-image:radial-gradient(1100px_circle_at_88%_-8%,rgba(201,163,90,.10),transparent_42%),radial-gradient(900px_circle_at_-5%_4%,rgba(107,155,242,.07),transparent_40%)]">

    {{-- Header --}}
    <header class="sticky top-0 z-20 flex items-center gap-3.5 px-8 py-[18px]
                   border-b border-line bg-surface/60 backdrop-blur-xl">
        <a href="{{ route('applications') }}"
           class="grid place-items-center w-10 h-10 rounded-[11px] bg-surface2 border border-line2 text-fmuted no-underline
                  transition hover:text-gold2 hover:border-gold shrink-0">←</a>
        <div class="flex-1 min-w-0">
            <span class="font-mono text-[12.5px] text-gold2">{{ $app->application_no }}</span>
            <h2 class="font-display text-[26px] font-semibold tracking-tight mt-0.5 mb-0">{{ $app->full_name }}</h2>
        </div>
        <span class="inline-flex items-center gap-1.5 px-[13px] py-1.5 rounded-[9px] text-xs font-bold whitespace-nowrap
                     before:content-[''] before:w-[7px] before:h-[7px] before:rounded-full before:bg-current
                     {{ $badge[$app->status] ?? $badge['draft'] }}">
            {{ $statuses[$app->status] ?? $app->status }}
        </span>
    </header>

    {{-- Tabs --}}
    <nav class="flex gap-1 px-6 border-b border-line bg-ink2 overflow-x-auto">
        @php
            $tabs = ['profile'=>'Profile','education'=>'Education','experience'=>'Experience','extras'=>'Projects & Skills','documents'=>'Documents'];
        @endphp
        @foreach($tabs as $key => $label)
            <button @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}'
                        ? 'text-gold2 after:opacity-100'
                        : 'text-fmuted hover:text-ftext after:opacity-0'"
                    class="relative bg-transparent border-0 px-4 py-[15px] text-[13px] font-semibold cursor-pointer
                           inline-flex items-center gap-[7px] whitespace-nowrap transition-colors
                           after:content-[''] after:absolute after:left-3 after:right-3 after:-bottom-px after:h-[2.5px] after:rounded-full
                           after:bg-gradient-to-r after:from-gold after:to-gold2 after:transition-opacity">
                {{ $label }}
                @if($key === 'documents')
                    <span class="bg-surface2 border border-line2 px-[7px] py-px rounded-md text-[11px] font-bold">{{ $app->documents->count() }}</span>
                @endif
            </button>
        @endforeach
    </nav>

    <div class="px-8 pt-7 pb-[60px] max-w-[920px]">

        {{-- PROFILE --}}
        <div x-show="tab === 'profile'" x-transition.opacity.duration.200ms>
            @php
                $personal = [
                    'Full Name'        => $app->full_name,
                    'Date of Birth'    => optional($app->dob)->format('d M Y').' ('.$app->age.' yrs)',
                    'Gender'           => $app->gender,
                    'Nationality'      => $app->nationality,
                    'Religion'         => $app->religion,
                    'Community'        => $app->community ?? '—',
                    "Father's Name"    => $app->father_name,
                    "Mother's Name"    => $app->mother_name,
                    'Single Girl Child'=> $app->single_girl_child ? 'Yes' : 'No',
                    'Differently Abled'=> $app->differently_abled ? 'Yes' : 'No',
                ];
                $programme = [
                    'Specialization' => $app->specialization ?? $app->specialization_other ?? '—',
                    'Mode'           => $app->programme_mode === 'full_time' ? 'Full Time' : 'Part Time',
                    'School ID'      => $app->school_id ?? '—',
                    'Discipline ID'  => $app->discipline_id ?? '—',
                    'Eligibility'    => $app->eligibility_qualified ? 'Qualified ('.($app->eligibility_exam ?? '—').')' : 'Not Qualified',
                    'Submitted At'   => optional($app->submitted_at)->format('d M Y, h:i A') ?? 'Not submitted',
                ];
            @endphp

            <section class="mb-[30px]">
                <h3 class="text-xs font-bold text-gold2 uppercase tracking-[.08em] m-0 mb-4 pb-2.5 border-b border-line">Personal Details</h3>
                <div class="grid grid-cols-2 gap-x-[26px] gap-y-4">
                    @foreach($personal as $k => $v)
                        <div class="flex flex-col gap-[5px]">
                            <span class="text-[11px] uppercase tracking-[.06em] text-fmuted font-semibold">{{ $k }}</span>
                            <span class="text-sm font-medium text-ftext">{{ filled($v) ? $v : '—' }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="mb-[30px]">
                <h3 class="text-xs font-bold text-gold2 uppercase tracking-[.08em] m-0 mb-4 pb-2.5 border-b border-line">Programme</h3>
                <div class="grid grid-cols-2 gap-x-[26px] gap-y-4">
                    @foreach($programme as $k => $v)
                        <div class="flex flex-col gap-[5px]">
                            <span class="text-[11px] uppercase tracking-[.06em] text-fmuted font-semibold">{{ $k }}</span>
                            <span class="text-sm font-medium text-ftext">{{ filled($v) ? $v : '—' }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            <section class="mb-[30px]">
                <h3 class="text-xs font-bold text-gold2 uppercase tracking-[.08em] m-0 mb-4 pb-2.5 border-b border-line">Contact</h3>
                <div class="grid grid-cols-2 gap-x-[26px] gap-y-4">
                    <div class="flex flex-col gap-[5px]"><span class="text-[11px] uppercase tracking-[.06em] text-fmuted font-semibold">Mobile</span><span class="text-sm font-medium">{{ $app->mobile }}</span></div>
                    <div class="flex flex-col gap-[5px]"><span class="text-[11px] uppercase tracking-[.06em] text-fmuted font-semibold">Email</span><span class="text-sm font-medium">{{ $app->email }}</span></div>
                    <div class="flex flex-col gap-[5px] col-span-2"><span class="text-[11px] uppercase tracking-[.06em] text-fmuted font-semibold">Current Address</span><span class="text-sm font-medium">{{ $app->address_current }}</span></div>
                    <div class="flex flex-col gap-[5px] col-span-2"><span class="text-[11px] uppercase tracking-[.06em] text-fmuted font-semibold">Permanent Address</span><span class="text-sm font-medium">{{ $app->address_same ? 'Same as current' : $app->address_permanent }}</span></div>
                </div>
            </section>

            <section class="mb-[30px]">
                <h3 class="text-xs font-bold text-gold2 uppercase tracking-[.08em] m-0 mb-4 pb-2.5 border-b border-line">Languages</h3>
                @if($app->languages->isEmpty())
                    <p class="text-fmuted text-[13px] italic py-1">No records available.</p>
                @else
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="[&>th]:text-left [&>th]:text-[11px] [&>th]:uppercase [&>th]:tracking-[.06em] [&>th]:text-fmuted [&>th]:font-bold [&>th]:px-3 [&>th]:py-2.5 [&>th]:border-b [&>th]:border-line">
                                <th>Language</th><th class="!text-center">Read</th><th class="!text-center">Write</th><th class="!text-center">Speak</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($app->languages as $l)
                                <tr class="[&>td]:px-3 [&>td]:py-[11px] [&>td]:border-b [&>td]:border-line [&>td]:text-[13px]">
                                    <td class="font-medium">{{ $l->language }}</td>
                                    <td class="text-center {{ $l->can_read ? 'text-[#46c08a]' : 'text-[#5f6477]' }}">{{ $l->can_read ? '✓' : '—' }}</td>
                                    <td class="text-center {{ $l->can_write ? 'text-[#46c08a]' : 'text-[#5f6477]' }}">{{ $l->can_write ? '✓' : '—' }}</td>
                                    <td class="text-center {{ $l->can_speak ? 'text-[#46c08a]' : 'text-[#5f6477]' }}">{{ $l->can_speak ? '✓' : '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </section>
        </div>

        {{-- EDUCATION --}}
        <div x-show="tab === 'education'" x-transition.opacity.duration.200ms style="display:none">
            <section class="mb-[30px]">
                <h3 class="text-xs font-bold text-gold2 uppercase tracking-[.08em] m-0 mb-4 pb-2.5 border-b border-line">Educational Qualifications</h3>
                @if($app->educations->isEmpty())
                    <p class="text-fmuted text-[13px] italic py-1">No records available.</p>
                @else
                    <div class="pl-1.5">
                        @foreach($app->educations as $e)
                            <div class="flex gap-3.5 pb-5 relative {{ !$loop->last ? "before:content-[''] before:absolute before:left-[5px] before:top-4 before:-bottom-1 before:w-px before:bg-line2" : '' }}">
                                <div class="w-[11px] h-[11px] rounded-full mt-1.5 shrink-0 bg-gradient-to-br from-gold to-gold2 ring-4 ring-gold/10"></div>
                                <div class="flex-1 bg-surface2 border border-line2 rounded-xl px-4 py-[13px]">
                                    <div class="flex justify-between items-center gap-2.5">
                                        <strong class="text-sm">{{ $e->education_level }}</strong>
                                        <span class="text-xs text-gold2 font-bold whitespace-nowrap">{{ $e->marks }}</span>
                                    </div>
                                    <p class="mt-1.5 mb-1.5 text-[13px] text-fmuted">{{ $e->institution }} · {{ $e->subjects }}</p>
                                    <span class="text-xs text-fmuted font-mono">{{ optional($e->passing_date)->format('d M Y') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        {{-- EXPERIENCE --}}
        <div x-show="tab === 'experience'" x-transition.opacity.duration.200ms style="display:none">
            <section class="mb-[30px]">
                <h3 class="text-xs font-bold text-gold2 uppercase tracking-[.08em] m-0 mb-4 pb-2.5 border-b border-line">Service Summary</h3>
                <div class="grid grid-cols-2 gap-x-[26px] gap-y-4">
                    <div class="flex flex-col gap-[5px]"><span class="text-[11px] uppercase tracking-[.06em] text-fmuted font-semibold">Total Service</span><span class="text-sm font-medium">{{ $app->total_service_years }}y {{ $app->total_service_months }}m</span></div>
                </div>
            </section>
            <section class="mb-[30px]">
                <h3 class="text-xs font-bold text-gold2 uppercase tracking-[.08em] m-0 mb-4 pb-2.5 border-b border-line">Work Experience</h3>
                @if($app->services->isEmpty())
                    <p class="text-fmuted text-[13px] italic py-1">No records available.</p>
                @else
                    <div class="pl-1.5">
                        @foreach($app->services as $s)
                            <div class="flex gap-3.5 pb-5 relative {{ !$loop->last ? "before:content-[''] before:absolute before:left-[5px] before:top-4 before:-bottom-1 before:w-px before:bg-line2" : '' }}">
                                <div class="w-[11px] h-[11px] rounded-full mt-1.5 shrink-0 bg-gradient-to-br from-gold to-gold2 ring-4 ring-gold/10"></div>
                                <div class="flex-1 bg-surface2 border border-line2 rounded-xl px-4 py-[13px]">
                                    <div class="flex justify-between items-center gap-2.5">
                                        <strong class="text-sm">{{ $s->designation }}</strong>
                                        <span class="text-xs text-gold2 font-bold whitespace-nowrap">{{ $s->total_duration }}</span>
                                    </div>
                                    <p class="mt-1.5 mb-1.5 text-[13px] text-fmuted">{{ $s->institution }}</p>
                                    <span class="text-xs text-fmuted font-mono">{{ optional($s->from_date)->format('d M Y') }} — {{ optional($s->to_date)->format('d M Y') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        {{-- EXTRAS --}}
        <div x-show="tab === 'extras'" x-transition.opacity.duration.200ms style="display:none">
            <section class="mb-[30px]">
                <h3 class="text-xs font-bold text-gold2 uppercase tracking-[.08em] m-0 mb-4 pb-2.5 border-b border-line">Projects</h3>
                @forelse($app->projects as $p)
                    <div class="flex justify-between items-center gap-3 bg-surface2 border border-line2 rounded-xl px-4 py-3 mb-2 text-sm">
                        <span>{{ $p->title }}</span>
                        <span class="text-[11px] font-bold px-2.5 py-[3px] rounded-md whitespace-nowrap {{ $projStatus[strtolower($p->status)] ?? $projStatus['pending'] }}">{{ $p->status }}</span>
                    </div>
                @empty
                    <p class="text-fmuted text-[13px] italic py-1">No records available.</p>
                @endforelse
            </section>
            <section class="mb-[30px]">
                <h3 class="text-xs font-bold text-gold2 uppercase tracking-[.08em] m-0 mb-4 pb-2.5 border-b border-line">Courses</h3>
                @forelse($app->courses as $c)
                    <div class="flex justify-between items-center gap-3 bg-surface2 border border-line2 rounded-xl px-4 py-3 mb-2 text-sm">
                        <span>{{ $c->course_name }}</span>
                        <span class="text-[11px] font-bold px-2.5 py-[3px] rounded-md whitespace-nowrap {{ $c->completed ? 'bg-[#46c08a]/10 text-[#46c08a]' : 'bg-fmuted/10 text-[#a3a9bb]' }}">{{ $c->completed ? 'Completed' : 'Pending' }}</span>
                    </div>
                @empty
                    <p class="text-fmuted text-[13px] italic py-1">No records available.</p>
                @endforelse
            </section>
            <section class="mb-[30px]">
                <h3 class="text-xs font-bold text-gold2 uppercase tracking-[.08em] m-0 mb-4 pb-2.5 border-b border-line">Career Aspirations</h3>
                @if($app->aspirations->isEmpty())
                    <p class="text-fmuted text-[13px] italic py-1">No records available.</p>
                @else
                    <div class="flex flex-wrap gap-2">
                        @foreach($app->aspirations as $a)
                            <span class="bg-surface2 border border-line2 px-3.5 py-[7px] rounded-[10px] text-[13px] font-medium text-gold2">{{ $a->aspiration }}</span>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        {{-- DOCUMENTS --}}
        <div x-show="tab === 'documents'" x-transition.opacity.duration.200ms style="display:none">
            <section class="mb-[30px]">
                <h3 class="text-xs font-bold text-gold2 uppercase tracking-[.08em] m-0 mb-4 pb-2.5 border-b border-line">Uploaded Documents</h3>
                @if($app->documents->isEmpty())
                    <p class="text-fmuted text-[13px] italic py-1">No documents uploaded.</p>
                @else
                    @foreach($app->documents as $d)
                        <div class="flex items-center gap-[13px] bg-surface2 border border-line2 rounded-xl px-4 py-[13px] mb-2.5 transition-colors hover:border-gold">
                            <div class="w-[42px] h-[42px] rounded-xl bg-surface border border-line2 grid place-items-center text-gold2 text-lg shrink-0">📄</div>
                            <div class="flex-1 min-w-0">
                                <strong class="text-sm block truncate">{{ $d->document_type }}</strong>
                                <span class="text-xs text-fmuted">{{ $d->file_name }} · {{ $d->file_size_human }}</span>
                            </div>
                            <a title="Download" href="{{ route('download', [$app, $d]) }}"
                               class="w-[38px] h-[38px] rounded-[10px] bg-gradient-to-br from-gold to-gold2 text-[#1a1408] grid place-items-center no-underline transition-transform hover:scale-105">⬇</a>
                        </div>
                    @endforeach
                    <a href="{{ route('download_all', $app) }}"
                       class="flex items-center justify-center gap-[9px] w-full bg-surface2 border border-dashed border-line2 text-fmuted
                              p-3.5 rounded-xl text-[13px] font-semibold no-underline transition-colors hover:border-gold hover:text-gold2 mt-1.5">⬇ Download All as ZIP</a>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
