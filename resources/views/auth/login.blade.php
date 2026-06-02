@extends('layouts.scholar')

@section('title', 'Sign In — Rathinam Global University')

@section('content')
    <div class="auth">
        <div class="auth__card">
            <div class="auth__head">
                <img class="auth__logo" src="{{ asset('images/logo.png') }}" alt="RGU">
                <h1 class="auth__title">Welcome back</h1>
                <p class="auth__sub">Sign in to continue to your RGU account</p>
            </div>

            <form id="login-form" method="POST" novalidate>
                @csrf

                <x-text-field name="email" label="Email" type="email" required
                              placeholder="you@example.com" autocomplete="email" />

                <div class="f-group">
                    <label for="password" class="f-label">Password <span class="f-req">*</span></label>
                    <div class="pw">
                        <input type="password" id="password" name="password" class="f-input"
                               autocomplete="current-password" required>
                        <button type="button" class="pw__toggle" data-pw-toggle>Show</button>
                    </div>
                    <p class="f-error" data-error-for="password"></p>
                </div>

                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>

            <p class="auth__alt">Don’t have an account? <a href="{{ route('auth.register') }}">Create one</a></p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/auth.js') }}"></script>
@endpush
