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
            <h1 class="auth__title">Reset password</h1>
            <p class="auth__sub">Choose a new password for your account</p>
        </div>

       <form method="POST" action="{{ route('password.update') }}" id="resetPassword" novalidate>
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <x-text-field name="email" label="Email" type="email" required
        value="{{ old('email', $email) }}" autocomplete="email" />

    {{-- New Password --}}
    <div class="f-group">
        <label for="password" class="f-label">New Password <span class="f-req">*</span></label>
        <div class="pw" x-data="{ show: false }">
            <div class="relative">
                <input :type="show ? 'text' : 'password'" id="password" name="password"
                    class="f-input pr-10" autocomplete="new-password">
                <button type="button" @click="show = !show"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                    <i x-show="!show" class="fa fa-eye"></i>
                    <i x-show="show" class="fa fa-eye-slash"></i>
                </button>
            </div>
        </div>
        <p class="f-error" data-error-for="password"></p>
    </div>

    {{-- Confirm Password --}}
    <div class="f-group">
        <label for="password_confirmation" class="f-label">Confirm Password <span class="f-req">*</span></label>
        <div class="pw" x-data="{ show: false }">
            <div class="relative">
                <input :type="show ? 'text' : 'password'" id="password_confirmation" name="password_confirmation"
                    class="f-input pr-10" autocomplete="new-password">
                <button type="button" @click="show = !show"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                    <i x-show="!show" class="fa fa-eye"></i>
                    <i x-show="show" class="fa fa-eye-slash"></i>
                </button>
            </div>
        </div>
        <p class="f-error" data-error-for="password_confirmation"></p>
    </div>

    <button type="submit" class="btn btn-primary">Reset Password</button>
</form>
    </div>
</div>
@endsection
@push('scripts')
    <script src="{{ asset('admin/js/resetpassword.js') }}?v={{ time() }}"></script>
@endpush
