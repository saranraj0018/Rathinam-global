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
            <form id="scholar-form" action="{{ route('scholar.submit') }}" method="POST"
                  enctype="multipart/form-data" novalidate>
                @csrf

                @include('scholar.steps.programme')
                @include('scholar.steps.personal')
                @include('scholar.steps.education')
                @include('scholar.steps.eligibility')
                @include('scholar.steps.experience')
                @include('scholar.steps.research')
                @include('scholar.steps.enclosures')
                @include('scholar.steps.declaration')
                @include('scholar.steps.preview')

                {{-- ── Wizard navigation ───────────────────────────── --}}
                <div class="wizard-nav">
                    <button type="button" class="btn btn-ghost" data-prev hidden>
                        ← Back
                    </button>
                    <span class="wizard-nav__spacer"></span>
                    <button type="button" class="btn btn-primary" data-next>
                        Save &amp; Continue →
                    </button>
                    <button type="submit" class="btn btn-submit" data-submit hidden>
                        Submit Application
                    </button>
                </div>
            </form>
        </div>

        <p class="wizard-foot-note">
            Fields marked <span class="f-req">*</span> are required. Your progress moves forward only
            when the current section is valid. Self-attested document copies must be enclosed where indicated
            (max&nbsp;2&nbsp;MB each).
        </p>
    </div>
@endsection
