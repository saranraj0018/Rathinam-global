@extends('layouts.scholar')

@section('title', 'Application Submitted — Rathinam Global University')

@section('content')
    <div class="thankyou">
        <div class="thankyou__card">
            <svg class="tick" viewBox="0 0 52 52" role="img" aria-label="Success">
                <circle class="tick__bg" cx="26" cy="26" r="25" />
                <circle class="tick__circle" cx="26" cy="26" r="25" />
                <path class="tick__check" d="M16 27 l7 7 l13 -14" />
            </svg>

            <h1 class="thankyou__title">Thank You!</h1>

            @if (session('ref'))
                <span class="thankyou__ref">Application Ref: {{ session('ref') }}</span>
            @endif

            <p class="thankyou__msg">
                Your Ph.D. application has been submitted successfully. A confirmation will be sent to your
                registered e-mail. Our admissions team will review your application and get in touch with you.
            </p>

            <div class="thankyou__actions">
                <a href="https://rathinam.global/" class="btn btn-primary">Back to Home</a>
                <a href="{{ route('scholar.create') }}" class="btn btn-ghost">Submit another application</a>
            </div>
        </div>
    </div>
@endsection
