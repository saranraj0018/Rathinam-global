{{-- resources/views/admin/applications/show.blade.php --}}
@extends('admin.app')
@section('title', $application->application_no)

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    bg:       '#f4f2f8',
                    surface:  '#ffffff',
                    surface2: '#f7f6fb',
                    line:     '#ece9f2',
                    text:     '#181423',
                    muted:    '#736e82',
                    accent:   '#7c3aed',
                    accent2:  '#9d5cf5',
                    'brand-ink': '#1c1530',
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
                keyframes: {
                    fade: { from: { opacity: '0', transform: 'translateY(6px)' }, to: { opacity: '1', transform: 'none' } },
                },
                animation: { fade: 'fade .22s ease-out' },
            },
        },
    };
</script>
@endpush

@section('content')
@php
    $statuses = ['draft'=>'Draft','submitted'=>'Submitted','under_review'=>'Under Review','approved'=>'Approved','rejected'=>'Rejected'];
    $badgeClasses = [
        'draft'        => 'bg-white/15 text-white ring-1 ring-inset ring-white/25',
        'submitted'    => 'bg-st-blue/20 text-white ring-1 ring-inset ring-st-blue/40',
        'under_review' => 'bg-st-amber/20 text-white ring-1 ring-inset ring-st-amber/40',
        'approved'     => 'bg-st-green/25 text-white ring-1 ring-inset ring-st-green/50',
        'rejected'     => 'bg-st-red/25 text-white ring-1 ring-inset ring-st-red/50',
    ];
    $projStatus = [
        'completed'    => 'bg-st-green/10 text-st-green ring-1 ring-inset ring-st-green/20',
        'won'          => 'bg-accent/10 text-accent ring-1 ring-inset ring-accent/20',
        'participated' => 'bg-st-blue/10 text-st-blue ring-1 ring-inset ring-st-blue/20',
        'pending'      => 'bg-muted/10 text-muted ring-1 ring-inset ring-muted/20',
    ];
    $app = $application;
@endphp

<div class="font-sans bg-bg text-text min-h-screen text-sm">

    {{-- ───────────── Hero header (aubergine band) ───────────── --}}
    <div class="relative overflow-hidden bg-brand-ink text-white
                [background-image:radial-gradient(800px_circle_at_15%_-50%,rgba(157,92,245,.45),transparent_55%),radial-gradient(600px_circle_at_95%_-20%,rgba(34,197,94,.16),transparent_50%)]">
        <header class="relative flex items-center gap-3.5 px-8 pt-6 pb-12">
            <a href="{{ route('applications') }}"
               class="grid place-items-center w-10 h-10 rounded-full bg-white/[.08] ring-1 ring-inset ring-white/15 text-white/80 no-underline
                      transition-colors hover:bg-white/[.16] hover:text-white">←</a>
            <div class="flex-1">
                <span class="font-mono text-xs text-accent2">{{ $app->application_no }}</span>
                <h2 class="font-display text-3xl font-semibold mt-0.5 mb-0 tracking-tight">{{ $app->full_name }}</h2>
            </div>
            <span class="inline-flex items-center gap-[5px] px-3.5 py-1.5 rounded-full text-xs font-semibold backdrop-blur-sm
                         {{ $badgeClasses[$app->status] ?? $badgeClasses['draft'] }}">
                {{ $statuses[$app->status] ?? $app->status }}
            </span>
        </header>
    </div>

    {{-- ───────────── Content card lifts over hero ───────────── --}}
    <div class="px-4 sm:px-8 -mt-6 pb-16 relative">
        <div class="bg-surface rounded-[20px] border border-line shadow-card overflow-hidden">

            {{-- Tabs --}}
            <nav class="flex gap-1 px-4 sm:px-6 border-b border-line overflow-x-auto">
                @php
                    $tabs = [
                        'profile'    => 'Profile',
                        'education'  => 'Education',
                        'experience' => 'Experience',
                        'extras'     => 'Projects & Skills',
                        'documents'  => 'Documents',
                    ];
                @endphp
                @foreach($tabs as $key => $label)
                    <button data-tab="{{ $key }}"
                            class="dtab relative bg-transparent border-none text-muted px-4 py-4
                                   text-[13px] font-semibold cursor-pointer flex items-center gap-[7px] whitespace-nowrap transition-colors
                                   hover:text-text
                                   data-[active=true]:text-accent
                                   after:content-[''] after:absolute after:left-3 after:right-3 after:-bottom-px after:h-[2.5px] after:rounded-full
                                   after:bg-gradient-to-r after:from-accent after:to-accent2 after:opacity-0
                                   data-[active=true]:after:opacity-100"
                            @if($key === 'profile') data-active="true" @endif>
                        {{ $label }}
                        @if($key === 'documents')
                            <span class="bg-surface2 ring-1 ring-inset ring-line px-[7px] py-px rounded-full text-[11px] font-bold">{{ $app->documents->count() }}</span>
                        @endif
                    </button>
                @endforeach
            </nav>

            <div class="px-6 sm:px-8 pt-7 pb-10 max-w-[920px]">

                {{-- PROFILE --}}
                <div class="pane animate-fade" data-pane="profile">
                    <x-section title="Personal Details">
                        @php
                            $personal = [
                                'Full Name'        => $app->full_name,
                                'Date of Birth'    => optional($app->dob)->format('d M Y') . ' (' . $app->age . ' yrs)',
                                'Gender'           => $app->gender,
                                'Nationality'      => $app->nationality,
                                'Religion'         => $app->religion,
                                'Community'        => $app->community ?? '—',
                                "Father's Name"    => $app->father_name,
                                "Mother's Name"    => $app->mother_name,
                                'Single Girl Child'=> $app->single_girl_child ? 'Yes' : 'No',
                                'Differently Abled'=> $app->differently_abled ? 'Yes' : 'No',
                            ];
                        @endphp
                        <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                            @foreach($personal as $label => $value)
                                <x-field :label="$label" :value="$value" />
                            @endforeach
                        </div>
                    </x-section>

                    <x-section title="Programme">
                        @php
                            $programme = [
                                'Specialization' => $app->specialization ?? $app->specialization_other ?? '—',
                                'Mode'           => $app->programme_mode === 'full_time' ? 'Full Time' : 'Part Time',
                                'School ID'      => $app->school_id ?? '—',
                                'Discipline ID'  => $app->discipline_id ?? '—',
                                'Eligibility'    => $app->eligibility_qualified ? 'Qualified ('.($app->eligibility_exam ?? '—').')' : 'Not Qualified',
                                'Submitted At'   => optional($app->submitted_at)->format('d M Y, h:i A') ?? 'Not submitted',
                            ];
                        @endphp
                        <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                            @foreach($programme as $label => $value)
                                <x-field :label="$label" :value="$value" />
                            @endforeach
                        </div>
                    </x-section>

                    <x-section title="Contact">
                        <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                            <x-field label="Mobile" :value="$app->mobile" />
                            <x-field label="Email" :value="$app->email" />
                            <div class="col-span-2"><x-field label="Current Address" :value="$app->address_current" /></div>
                            <div class="col-span-2"><x-field label="Permanent Address" :value="$app->address_same ? 'Same as current' : $app->address_permanent" /></div>
                        </div>
                    </x-section>

                    <x-section title="Languages" :last="true">
                        @if($app->languages->isEmpty())
                            <x-empty />
                        @else
                            <table class="w-full border-collapse">
                                <thead>
                                    <tr class="[&>th]:text-left [&>th]:text-[11px] [&>th]:uppercase [&>th]:tracking-[.06em] [&>th]:text-muted [&>th]:font-bold [&>th]:px-3 [&>th]:py-2.5 [&>th]:border-b [&>th]:border-line">
                                        <th>Language</th><th class="!text-center">Read</th><th class="!text-center">Write</th><th class="!text-center">Speak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($app->languages as $l)
                                        <tr class="[&>td]:px-3 [&>td]:py-2.5 [&>td]:border-b [&>td]:border-line [&>td]:text-[13px]">
                                            <td class="font-medium">{{ $l->language }}</td>
                                            <td class="text-center {{ $l->can_read ? 'text-st-green' : 'text-muted/50' }}">{{ $l->can_read ? '✓' : '—' }}</td>
                                            <td class="text-center {{ $l->can_write ? 'text-st-green' : 'text-muted/50' }}">{{ $l->can_write ? '✓' : '—' }}</td>
                                            <td class="text-center {{ $l->can_speak ? 'text-st-green' : 'text-muted/50' }}">{{ $l->can_speak ? '✓' : '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </x-section>
                </div>

                {{-- EDUCATION --}}
                <div class="pane hidden" data-pane="education">
                    <x-section title="Educational Qualifications" :last="true">
                        @if($app->educations->isEmpty())
                            <x-empty />
                        @else
                            <div class="pl-1">
                                @foreach($app->educations as $e)
                                    <x-timeline-item :last="$loop->last"
                                        :title="$e->education_level" :badge="$e->marks"
                                        :sub="$e->institution.' · '.$e->subjects"
                                        :date="optional($e->passing_date)->format('d M Y')" />
                                @endforeach
                            </div>
                        @endif
                    </x-section>
                </div>

                {{-- EXPERIENCE --}}
                <div class="pane hidden" data-pane="experience">
                    <x-section title="Service Summary">
                        <div class="grid grid-cols-2 gap-x-6 gap-y-4">
                            <x-field label="Total Service" :value="$app->total_service_years.'y '.$app->total_service_months.'m'" />
                        </div>
                    </x-section>
                    <x-section title="Work Experience" :last="true">
                        @if($app->services->isEmpty())
                            <x-empty />
                        @else
                            <div class="pl-1">
                                @foreach($app->services as $s)
                                    <x-timeline-item :last="$loop->last"
                                        :title="$s->designation" :badge="$s->total_duration"
                                        :sub="$s->institution"
                                        :date="optional($s->from_date)->format('d M Y').' — '.optional($s->to_date)->format('d M Y')" />
                                @endforeach
                            </div>
                        @endif
                    </x-section>
                </div>

                {{-- EXTRAS --}}
                <div class="pane hidden" data-pane="extras">
                    <x-section title="Projects">
                        @forelse($app->projects as $p)
                            <div class="flex justify-between items-center gap-3 bg-surface2 ring-1 ring-inset ring-line rounded-xl px-4 py-3 mb-2 text-sm">
                                <span class="font-medium">{{ $p->title }}</span>
                                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full whitespace-nowrap
                                             {{ $projStatus[strtolower($p->status)] ?? $projStatus['pending'] }}">{{ $p->status }}</span>
                            </div>
                        @empty
                            <x-empty />
                        @endforelse
                    </x-section>
                    <x-section title="Courses">
                        @forelse($app->courses as $c)
                            <div class="flex justify-between items-center gap-3 bg-surface2 ring-1 ring-inset ring-line rounded-xl px-4 py-3 mb-2 text-sm">
                                <span class="font-medium">{{ $c->course_name }}</span>
                                <span class="text-[11px] font-semibold px-2.5 py-1 rounded-full whitespace-nowrap
                                             {{ $c->completed ? 'bg-st-green/10 text-st-green ring-1 ring-inset ring-st-green/20' : 'bg-muted/10 text-muted ring-1 ring-inset ring-muted/20' }}">{{ $c->completed ? 'Completed' : 'Pending' }}</span>
                            </div>
                        @empty
                            <x-empty />
                        @endforelse
                    </x-section>
                    <x-section title="Career Aspirations" :last="true">
                        @if($app->aspirations->isEmpty())
                            <x-empty />
                        @else
                            <div class="flex flex-wrap gap-2">
                                @foreach($app->aspirations as $a)
                                    <span class="bg-accent/[.07] ring-1 ring-inset ring-accent/15 px-3.5 py-1.5 rounded-full text-[13px] font-medium text-accent">{{ $a->aspiration }}</span>
                                @endforeach
                            </div>
                        @endif
                    </x-section>
                </div>

                {{-- DOCUMENTS --}}
                <div class="pane hidden" data-pane="documents">
                    <x-section title="Uploaded Documents" :last="true">
                        @if($app->documents->isEmpty())
                            <x-empty msg="No documents uploaded." />
                        @else
                            <div class="grid gap-2.5 mb-4">
                                @foreach($app->documents as $d)
                                    <div class="flex items-center gap-[13px] bg-surface2 ring-1 ring-inset ring-line rounded-xl px-4 py-3 transition-colors hover:ring-accent/40">
                                        <div class="w-[42px] h-[42px] rounded-xl bg-white ring-1 ring-inset ring-line grid place-items-center text-accent text-lg shrink-0">📄</div>
                                        <div class="flex-1 min-w-0">
                                            <strong class="text-sm block truncate">{{ $d->document_type }}</strong>
                                            <span class="text-xs text-muted">{{ $d->file_name }} · {{ $d->file_size_human }}</span>
                                        </div>
                                        <a title="Download" href="{{ route('download', [$app, $d]) }}"
                                           class="w-[38px] h-[38px] rounded-xl bg-gradient-to-br from-accent to-accent2 text-white grid place-items-center no-underline transition-transform hover:scale-105">⬇</a>
                                    </div>
                                @endforeach
                            </div>
                            <a href="{{ route('download_all', $app) }}"
                               class="w-full flex items-center justify-center gap-[9px] bg-surface2 ring-1 ring-inset ring-dashed ring-line text-muted
                                      p-3.5 rounded-xl text-[13px] font-semibold no-underline transition-colors hover:ring-accent/50 hover:text-accent">⬇ Download All as ZIP</a>
                        @endif
                    </x-section>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.dtab').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.dtab').forEach(b => b.removeAttribute('data-active'));
            document.querySelectorAll('.pane').forEach(p => p.classList.add('hidden'));
            btn.setAttribute('data-active', 'true');
            const pane = document.querySelector('[data-pane="' + btn.dataset.tab + '"]');
            pane.classList.remove('hidden');
            pane.classList.remove('animate-fade');
            void pane.offsetWidth;
            pane.classList.add('animate-fade');
        });
    });
</script>
@endsection
