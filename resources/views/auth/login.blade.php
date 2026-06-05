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
            <form id="loginForm" method="POST" action="{{ route('auth.login.store') }}" novalidate>
                @csrf
                <x-text-field name="email" label="Email" type="email" required placeholder="you@example.com"
                    autocomplete="email" />
                <div class="f-group">
                    <label for="password" class="f-label">Password <span class="f-req">*</span></label>
                    <div class="pw">
                        <div x-data="{ showConfirm: false }">
                            <label class="block text-[14px] font-medium mb-2">Confirm Password</label>

                            <div class="relative">
                                <input :type="showConfirm ? 'text' : 'password'" name="password" class="f-input pr-10">

                                <button type="button" @click="showConfirm = !showConfirm"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">
                                    <i x-show="!showConfirm" class="fa fa-eye-slash"></i>
                                    <i x-show="showConfirm" class="fa fa-eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <p class="f-error" data-error-for="password"></p>
                </div>

                <button type="submit" class="btn btn-primary">Sign In</button>
            </form>
            <p class="auth__alt">Don’t have an account? <a href="{{ route('auth.register') }}">Sign Up</a></p>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('admin/js/auth.js') }}"></script>
@endpush
