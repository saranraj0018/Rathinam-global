@extends('layouts.scholar')
<style>
    [x-cloak] {
        display: none !important;
    }
</style>
@section('title', 'Sign In — Rathinam Global University')
@section('content')
   <div class="auth">
    <div class="auth__card">
        <div class="auth__head">
            <img class="auth__logo" src="{{ asset('images/logo.png') }}" alt="RGU">
            <h1 class="auth__title">Forgot password?</h1>
            <p class="auth__sub">Enter your email and we'll send you a reset link</p>
        </div>
        @if (session('status'))
            <p class="auth__status">{{ session('status') }}</p>
        @endif
        <form method="POST" action="{{ route('password.email') }}" novalidate id="forgotPassForm">
            @csrf
            <x-text-field name="email" label="Email" type="email" required
                placeholder="you@example.com" autocomplete="email" />
            <button type="submit" class="btn btn-primary">Send Reset Link</button>
        </form>
        <p class="auth__alt"><a href="{{ route('auth.login') }}">Back to Sign In</a></p>
    </div>
</div>
@endsection
@push('scripts')
    <script src="{{ asset('admin/js/resetpassword.js') }}?v={{ time() }}"></script>
@endpush
