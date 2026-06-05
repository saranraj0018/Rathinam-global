@extends('layouts.scholar')

@section('title', 'Ph.D. Application Form — Rathinam Global University')

@php
    // Wizard step registry — order here drives the stepper AND the includes below.
    $steps = [
        ['id' => 'programme',   'label' => 'Programme',     'icon' => '🎓'],
        ['id' => 'personal',    'label' => 'Personal',      'icon' => '👤'],
        ['id' => 'education',   'label' => 'Education',      'icon' => '📚'],
        ['id' => 'eligibility', 'label' => 'Eligibility',   'icon' => '✅'],
        ['id' => 'experience',  'label' => 'Experience',    'icon' => '💼'],
        ['id' => 'research',    'label' => 'Research',       'icon' => '🔬'],
        ['id' => 'enclosures',  'label' => 'Enclosures',    'icon' => '📎'],
        ['id' => 'declaration', 'label' => 'Declaration',   'icon' => '✍️'],
        ['id' => 'preview',     'label' => 'Preview',        'icon' => '👁️'],
    ];
@endphp

@section('content')
    {{-- Annexure cascade data + enclosure rules for the front-end --}}
    <script id="annexure-data" type="application/json">@json($data['schools'], JSON_UNESCAPED_UNICODE)</script>

    {{-- ── Hero / title band ───────────────────────────────────────── --}}
    <header class="form-hero">
        <div class="form-hero__bg" aria-hidden="true"></div>
        <div class="mx-auto max-w-5xl px-5 text-center relative">
            <p class="form-hero__eyebrow">Doctoral Programmes · 2026–27</p>
            <h1 class="form-hero__title">Application for Admission to Ph.D.</h1>
            <p class="form-hero__sub">Full-Time · Full-Time (Start-up) · Part-Time · Integrated</p>
        </div>
    </header>

    <div class="mx-auto max-w-5xl px-4 sm:px-6 pb-24 -mt-10 relative">

        @if (session('status'))
            <div class="alert-success" role="status">{{ session('status') }}</div>
        @endif

        <div class="wizard-card">

            {{-- ── Progress stepper (desktop) ──────────────────────── --}}
            <ol class="stepper" aria-label="Application progress">
                @foreach ($steps as $i => $step)
                    <li class="stepper__item" data-step-tab="{{ $i }}">
                        <span class="stepper__dot">{{ $i + 1 }}</span>
                        <span class="stepper__label">{{ $step['label'] }}</span>
                    </li>
                @endforeach
            </ol>

            {{-- ── Progress bar (mobile) ───────────────────────────── --}}
            <div class="stepper-mobile">
                <div class="stepper-mobile__bar"><span data-progress-bar></span></div>
                <p class="stepper-mobile__text">
                    Step <strong data-step-current>1</strong> of {{ count($steps) }} —
                    <span data-step-name>{{ $steps[0]['label'] }}</span>
                </p>
            </div>

            {{-- ── The form ────────────────────────────────────────── --}}
            <form id="scholar-form" action="{{ route('scholar.submit') }}" method="POST" enctype="multipart/form-data" novalidate>
                @csrf
                @include('scholar.steps.programme', ['application' => $draft])
                @include('scholar.steps.personal')
                @include('scholar.steps.education')
                @include('scholar.steps.eligibility')
                @include('scholar.steps.experience')
                @include('scholar.steps.research')
                @include('scholar.steps.enclosures')
                @include('scholar.steps.declaration')
                @include('scholar.steps.preview', ['payment_status' => $draft['payment_status'] ?? 'payment_pending',])

                {{-- ── Wizard navigation ───────────────────────────── --}}
                <div class="wizard-nav">
                    <button type="button" class="btn btn-ghost" data-prev hidden>
                        ← Back
                    </button>
                    <span class="wizard-nav__spacer"></span>
                    <button type="button" class="btn btn-primary" data-next>
                        Save &amp; Continue →
                    </button>
                    @if ($status != 'submitted')
                    <button type="submit" class="btn btn-submit" data-submit hidden>
                        Submit Application
                    </button>
                    @endif
                </div>
            </form>
        </div>

        <p class="wizard-foot-note">
            Fields marked <span class="f-req">*</span> are required. Your progress moves forward only
            when the current section is valid. Self-attested document copies must be enclosed where indicated
            (max&nbsp;2&nbsp;MB each).
        </p>
    </div>

    <!-- Acknowledgement Modal -->
<!-- Acknowledgement Modal -->
<div id="ackModal"
     style="position:fixed; inset:0; z-index:1000; display:none;
            align-items:center; justify-content:center; padding:1rem;
            background:rgba(0,0,0,.55); backdrop-filter:blur(4px);">

    <!-- Overlay -->
    <div data-ack-overlay style="position:absolute; inset:0;"></div>

    <!-- Box -->
    <div style="position:relative; width:100%; max-width:480px;
                background:#fff; border-radius:16px; padding:2rem;
                box-shadow:0 20px 50px rgba(0,0,0,.3); text-align:center;">

        <div style="margin:0 auto 1.25rem; width:56px; height:56px;
                    display:flex; align-items:center; justify-content:center;
                    border-radius:9999px; background:#dcfce7;">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="none"
                 stroke="#16a34a" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
        </div>

        <h3 style="font-size:1.25rem; font-weight:600; color:#111827; margin:0;">
            Congratulations!
        </h3>

        <p style="margin-top:.75rem; font-size:.875rem; line-height:1.6; color:#4b5563;">
            You have been successfully registered for the Ph.D. programme at
            <span style="font-weight:500; color:#1f2937;">Rathinam Global Deemed to be
            University</span>. A copy of your registered application will be sent
            to your registered email address shortly.
        </p>

        <div style="margin-top:1.75rem; display:flex; gap:.75rem; justify-content:center; flex-wrap:wrap;">
            <button type="button" id="ackCancel"
                    style="border:1px solid #d1d5db; border-radius:8px;
                           padding:.625rem 1.25rem; font-size:.875rem;
                           font-weight:500; color:#374151; background:#fff; cursor:pointer;">
                Go Back
            </button>
            <button type="button" id="ackConfirm"
                    style="border:none; border-radius:8px; padding:.625rem 1.25rem;
                           font-size:.875rem; font-weight:500; color:#fff;
                           background:#4f46e5; cursor:pointer;">
                Submit Application
            </button>
        </div>
    </div>
</div>
@endsection
