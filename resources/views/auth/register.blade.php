@extends('layouts.scholar')

@section('title', 'Create Account — Rathinam Global University')

@section('content')
    <div class="auth">
        <div class="auth__card">
            <div class="auth__head">
                <img class="auth__logo" src="{{ asset('images/logo.png') }}" alt="RGU">
                <h1 class="auth__title">Create your account</h1>
                <p class="auth__sub">Register to apply to Rathinam Global University</p>
            </div>

            <form id="signupForm" method="POST" action="{{ route('auth.register.store') }}" novalidate>
                @csrf
                <x-text-field name="name" label="Full Name" required
                              placeholder="Your full name" autocomplete="name" />

                <x-text-field name="phone_number" label="Phone Number" type="tel" required
                              placeholder="10-digit mobile" autocomplete="tel" />

                <x-text-field name="email" label="Email" type="email" required
                              placeholder="you@example.com" autocomplete="email" />

                <div class="f-group">
                    <label for="password" class="f-label">Password <span class="f-req">*</span></label>
                    <div class="pw">
                        <input type="password" id="password" name="password" class="f-input"
                               autocomplete="new-password" required>
                        <button type="button" class="pw__toggle" data-pw-toggle>Show</button>
                    </div>
                    <p class="f-hint">At least 8 characters, including letters and numbers.</p>
                    <p class="f-error" data-error-for="password"></p>
                </div>

                <div class="f-group">
                    <label for="password_confirmation" class="f-label">Confirm Password <span class="f-req">*</span></label>
                    <div class="pw">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="f-input"
                               autocomplete="new-password" required>
                        <button type="button" class="pw__toggle" data-pw-toggle>Show</button>
                    </div>
                    <p class="f-error" data-error-for="password_confirmation"></p>
                </div>

                <button type="submit" class="btn btn-primary">Create Account</button>
            </form>

            <p class="auth__alt">Already have an account? <a href="{{ route('auth.login') }}">Sign in</a></p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/auth.js') }}"></script>
@endpush
