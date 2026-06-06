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
               <a href="{{ route('password.request') }}" class="block text-right text-sm text-blue-600 hover:underline mt-1 mb-3 auth__forgot">Forgot password?</a>
                <button type="submit" class="btn btn-primary mt-1">Sign In</button>
            </form>
            <p class="auth__alt">Don’t have an account? <a href="{{ route('auth.register') }}">Sign Up</a></p>
        </div>
    </div>
   {{-- Outer overlay --}}
<div id="declarationModal"
     class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4 backdrop-blur-md">

    {{-- Inner modal --}}
    <div class="flex w-full max-w-xl flex-col overflow-hidden rounded-xl bg-white shadow-2xl"
         style="max-height: 85vh;">

        {{-- Header --}}
        <div class="shrink-0 rounded-t-2xl border-b border-gray-200 px-5 py-3.5 flex items-center justify-between">
            <div>
                <p class="text-[10px] font-medium uppercase tracking-widest text-gray-400">Doctoral Programmes 2026–27</p>
                <h2 class="mt-0.5 text-sm font-semibold text-gray-900">Declaration Form</h2>
            </div>
            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
        </div>

        {{-- Scrollable Body --}}
        <div class="flex-1 overflow-y-auto px-5 py-4 space-y-5 text-sm text-gray-700">

            {{-- Section 1: Educational Qualification --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-50">
                        <svg class="h-3.5 w-3.5 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-1.342" />
                        </svg>
                    </div>
                    <h3 class="text-xs font-semibold text-gray-800 uppercase tracking-wide">Educational Qualification Certification / Degree</h3>
                </div>
                <div class="grid grid-cols-2 gap-1.5">
                    @foreach(['SSLC', 'HSC (+2)', 'Diploma', "Bachelor's Degree", "Master's Degree", 'M.Phil.', 'Others'] as $qual)
                        <div class="flex items-center gap-2 rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 text-xs text-gray-600">
                            <span class="h-1.5 w-1.5 rounded-full bg-gray-300 shrink-0"></span>
                            {{ $qual }}
                        </div>
                    @endforeach
                </div>
            </div>

            <hr class="border-gray-100">

            {{-- Section 2: List of Enclosures --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-green-50">
                        <svg class="h-3.5 w-3.5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m18.375 12.739-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                        </svg>
                    </div>
                    <h3 class="text-xs font-semibold text-gray-800 uppercase tracking-wide">List of Enclosures</h3>
                </div>
                <p class="text-xs text-gray-500 mb-2">Please enclose the following documents along with your application.</p>
                <div class="space-y-1.5">
                    @php
                        $enclosures = [
                            'Self-attested copy of SSLC mark sheet',
                            'Self-attested copy of HSC (+2) mark sheet',
                            'Self-attested copy of UG degree certificate / provisional certificate',
                            'Self-attested copy of PG mark sheets / consolidated mark sheet',
                            'Self-attested copy of PG degree certificate / provisional certificate',
                            'Self-attested copy of M.Phil. degree certificate / provisional certificate',
                            'Self-attested copy of community certificate (OC / EWS / BC / BCM / MBC / DNC / SC / SCA / ST)',
                            'No objection certificate (NOC) from head of institution / employer / director (self-declaration for self-employment — part-time Ph.D. only)',
                            'Service certificate from head of institution / employer (part-time Ph.D. only)',
                            'Equivalence certificate for foreign degree holders (to be produced at time of admission)',
                        ];
                    @endphp
                    @foreach($enclosures as $i => $doc)
                        <div class="flex items-start gap-2.5 rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 text-xs text-gray-600">
                            <span class="mt-0.5 shrink-0 font-medium text-gray-400">{{ $i + 1 }}.</span>
                            <span>{{ $doc }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            <hr class="border-gray-100">

            {{-- Section 3: Projects / Thesis / Events --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-amber-50">
                        <svg class="h-3.5 w-3.5 text-amber-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.01 6.01 0 0 0 1.5-.189m-1.5.189a6.01 6.01 0 0 1-1.5-.189m3.75 7.478a12.06 12.06 0 0 1-4.5 0m3.75 2.383a14.406 14.406 0 0 1-3 0M14.25 18v-.192c0-.983.658-1.823 1.508-2.316a7.5 7.5 0 1 0-7.517 0c.85.493 1.509 1.333 1.509 2.316V18" />
                        </svg>
                    </div>
                    <h3 class="text-xs font-semibold text-gray-800 uppercase tracking-wide">Projects / Thesis / Start-ups / Events</h3>
                </div>
                <p class="text-xs text-gray-500">
                    UG &amp; PG projects, M.Phil. thesis title, start-ups, ideathons or hackathons — completed,
                    participated, or won — along with any additional details should be provided in your application form.
                </p>
            </div>

            <hr class="border-gray-100">

            {{-- Section 4: Declaration --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-violet-50">
                        <svg class="h-3.5 w-3.5 text-violet-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                    </div>
                    <h3 class="text-xs font-semibold text-gray-800 uppercase tracking-wide">Declaration</h3>
                </div>
                <div class="rounded-xl bg-gray-50 px-4 py-3 text-xs leading-relaxed text-gray-600 space-y-2">
                    <p>I hereby declare that all the information furnished by me in this application and the documents enclosed are true and correct to the best of my knowledge and belief. I understand that if any information provided is found to be false or incorrect, my application/admission is liable to be cancelled.</p>
                    <p>I also confirm that I have enclosed all the required documents mentioned above and agree to abide by the rules and regulations of the institution.</p>
                </div>
            </div>

            {{-- Section 5: Application Fee --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <div class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-50">
                        <svg class="h-3.5 w-3.5 text-emerald-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75" />
                        </svg>
                    </div>
                    <h3 class="text-xs font-semibold text-gray-800 uppercase tracking-wide">Application Fee</h3>
                </div>
                <div class="rounded-xl bg-gray-50 px-4 py-3 text-xs leading-relaxed text-gray-900">
                    The applicable application fee is <strong class="font-semibold">₹2,000/-</strong>
                    (Rupees Two Thousand Only). Please ensure payment proof is enclosed with your application.
                </div>
            </div>

        </div>

        {{-- Footer --}}
        <div class="shrink-0 rounded-b-2xl border-t border-gray-200 bg-white px-5 py-3.5">
            <label class="mb-3 flex cursor-pointer items-start gap-2.5">
                <input type="checkbox" id="declAgree"
                       class="mt-0.5 h-4 w-4 shrink-0 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="text-xs leading-relaxed text-gray-700">
                    I have read the above declaration and confirm that all the information provided is true and correct.
                    <span class="text-red-500">*</span>
                </span>
            </label>

            <p id="declAgreeError" class="hidden mb-2 text-xs text-red-500 flex items-center gap-1">
                <svg class="h-3.5 w-3.5 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                You must agree to the declaration before continuing.
            </p>

            <button type="button" id="declConfirmBtn"
                    class="w-full rounded-xl btn btn-primary px-4 py-2.5 text-sm font-medium text-white transition">
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
