@extends('layouts.scholar')
<style>[x-cloak]{ display:none !important; }</style>
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
   <div id="declarationModal"
     class="fixed inset-0 z-[9999] hidden items-center justify-center bg-black/55 p-4">

    <div class="flex max-h-[85vh] w-full max-w-2xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl">

        <!-- Head -->
        <div class="border-b border-gray-200 px-6 py-4">
            <h2 class="text-lg font-semibold text-gray-900">
                Doctoral Programmes 2026–27 — Declaration Form
            </h2>
        </div>

        <!-- Body (scrollable) -->
        <div class="flex-1 overflow-y-auto px-6 py-5 text-[15px] leading-relaxed text-gray-700">
            <h3 class="mb-1 mt-0 text-[15px] font-semibold text-gray-900">Declaration</h3>
            <p class="mb-4">
                I hereby declare that all the information furnished by me in this application
                and the documents enclosed are true and correct to the best of my knowledge
                and belief. I understand that if any information provided is found to be false
                or incorrect, my application/admission is liable to be cancelled.
            </p>
            <p class="mb-4">
                I also confirm that I have enclosed all the required documents mentioned above
                and agree to abide by the rules and regulations of the institution.
            </p>

            <h3 class="mb-1 mt-4 text-[15px] font-semibold text-gray-900">Application Fee</h3>
            <p class="mb-0">
                I have paid the applicable application fee of ₹2,000/- (Rupees Two Thousand Only)
                and enclosed the payment proof.
            </p>
        </div>

        <!-- Foot -->
        <div class="border-t border-gray-200 px-6 py-4">
            <label class="mb-4 flex cursor-pointer items-start gap-2">
                <input type="checkbox" id="declAgree"
                       class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-sm text-gray-700">
                    I have read and agree to the above declaration.
                </span>
            </label>

            <button type="button" id="declConfirmBtn" disabled
                    class="w-full cursor-not-allowed rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white opacity-50 transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Confirm &amp; Continue
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        const DECLARATION_CONFIRM_URL = "{{ route('auth.declaration.confirm') }}";
        const CSRF_TOKEN = "{{ csrf_token() }}";
    </script>
    <script src="{{ asset('admin/js/auth.js') }}?v=1.0.1"></script>
@endpush
