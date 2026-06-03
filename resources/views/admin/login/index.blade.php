@extends('admin.layout')
<style>
    .tab-btn {
        transition: color .3s ease;
    }

    .no-scrollbar::-webkit-scrollbar {
        display: none;
    }

    .no-scrollbar {
        scrollbar-width: none;
        -ms-overflow-style: none;
    }

    .form-panel {
        transition: opacity .3s ease, transform .3s ease;
    }

    .active-form {
        opacity: 1;
        transform: translateY(0);
        display: block;
    }

    .hidden-form {
        opacity: 0;
        transform: translateY(10px);
        display: none;
    }

    .auth-input::placeholder {
        color: rgba(100, 50, 120, 0.35);
    }

    .auth-input:focus {
        border-color: rgba(157, 44, 175, 0.45) !important;
    }
</style>
@section('content')
    <section class="min-h-screen overflow-y-auto no-scrollbar px-[25px] flex items-center justify-center relative"
        style="background: #f8f4ff;">
        <div class="absolute inset-0 pointer-events-none overflow-hidden z-0">
            <div
                style="position:absolute;width:420px;height:420px;top:-120px;left:-100px;border-radius:50%;background:rgba(157,44,175,0.12);filter:blur(60px);">
            </div>
            <div
                style="position:absolute;width:300px;height:300px;bottom:-80px;right:-60px;border-radius:50%;background:rgba(100,20,200,0.10);filter:blur(50px);">
            </div>
            <div
                style="position:absolute;width:200px;height:200px;top:60%;left:60%;border-radius:50%;background:rgba(230,180,255,0.18);filter:blur(40px);">
            </div>
            <div
                style="position:absolute;width:180px;height:180px;border-radius:50%;border:1.5px solid rgba(157,44,175,0.12);top:30px;left:30px;">
            </div>
            <div
                style="position:absolute;width:260px;height:260px;border-radius:50%;border:1px solid rgba(157,44,175,0.08);top:-40px;left:-30px;">
            </div>
            <div
                style="position:absolute;width:140px;height:140px;border-radius:50%;border:1.5px solid rgba(157,44,175,0.10);bottom:40px;right:40px;">
            </div>
            <div
                style="position:absolute;width:220px;height:220px;border-radius:50%;border:1px solid rgba(157,44,175,0.07);bottom:-30px;right:-20px;">
            </div>
            <div
                style="position:absolute;inset:0;
                background-image:radial-gradient(circle, rgba(157,44,175,0.18) 1px, transparent 1px);
                background-size:28px 28px;
                mask-image:radial-gradient(ellipse 90% 80% at 50% 50%, black 0%, transparent 100%);
                -webkit-mask-image:radial-gradient(ellipse 90% 80% at 50% 50%, black 0%, transparent 100%);
            ">
            </div>

            {{-- Diagonal accent lines --}}
            <div
                style="position:absolute;width:160px;height:2px;background:linear-gradient(90deg,transparent,rgba(157,44,175,0.2),transparent);top:80px;right:60px;transform:rotate(-30deg);">
            </div>
            <div
                style="position:absolute;width:120px;height:2px;background:linear-gradient(90deg,transparent,rgba(157,44,175,0.15),transparent);bottom:120px;left:50px;transform:rotate(20deg);">
            </div>
        </div>
        {{-- Card --}}
        <div class="w-full max-w-[450px] relative z-10">
            <div class="rounded-[22px] px-5 py-4"
                style="
                    background: rgba(255,255,255,0.78);
                    backdrop-filter: blur(20px);
                    -webkit-backdrop-filter: blur(20px);
                    border: 1px solid rgba(157,44,175,0.15);
                    box-shadow: 0 8px 40px rgba(157,44,175,0.12), 0 2px 8px rgba(0,0,0,0.06), inset 0 1px 0 rgba(255,255,255,0.9);
                    color: #1a0030;
                 ">

                <!-- Logo -->
                <div class="flex justify-center">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo" class="w-full h-[120px] object-contain">
                </div>

                <!-- Title -->
                <div class="text-center">
                    <h2 id="formTitle" class="text-[22px] font-semibold transition-all duration-300" style="color:#2d0050;">
                        Welcome Back!
                    </h2>
                    <p id="formSubtitle" class="text-[13px] mt-2" style="color:rgba(80,0,100,0.55);">
                        Sign in to your account
                    </p>
                </div>

                <!-- Tabs -->
                <div class="mt-7">
                    <div class="relative rounded-[12px] p-1 flex"
                        style="background:rgba(157,44,175,0.08);border:1px solid rgba(157,44,175,0.12);">
                        <span id="tabIndicator"
                            class="absolute top-1 left-1 h-[44px] w-[calc(50%_-_4px)] rounded-[10px] transition-all duration-300 ease-in-out"
                            style="background:#9D2CAF;box-shadow:0 2px 8px rgba(157,44,175,0.35);">
                        </span>
                        <button id="loginTab" type="button"
                            class="tab-btn relative z-10 w-1/2 h-[44px] rounded-[10px] text-[14px] font-medium"
                            style="color:white;">
                            Login
                        </button>
                        <button id="signupTab" type="button"
                            class="tab-btn relative z-10 w-1/2 h-[44px] rounded-[10px] text-[14px] font-medium"
                            style="color:rgba(80,0,100,0.5);">
                            Signup
                        </button>
                    </div>
                </div>

                <!-- Forms -->
                <div class="relative mt-6">
                    <!-- Login Form -->
                    <form id="loginForm" class="space-y-5" x-data="{ show: false, loading: false }" @submit="loading = true">
                        @csrf
                        <div>
                            <label class="block text-[14px] font-medium mb-2" style="color:#4a0070;">Email</label>
                            <input type="email" name="email" placeholder="Enter Your Email" value="{{ old('email') }}"
                                class="w-full h-[45px] rounded-[10px] px-4 text-[13px] outline-none transition auth-input"
                                style="background:rgba(255,255,255,0.8);border:1px solid rgba(157,44,175,0.18);color:#2d0050;">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[14px] font-medium mb-2" style="color:#4a0070;">Password</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" name="password"
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 mt-1
                              focus:outline-none focus:ring-2 focus:ring-[#363636] pr-10"
                                    placeholder="••••••••" />
                                <button type="button" @click="show = !show"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 text-lg">
                                    <i x-show="!show" class="fa fa-eye-slash" aria-hidden="true"></i>
                                    <i x-show="show" class="fa fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex justify-end -mt-2">
                            <a href="#" class="text-[13px]" style="color:rgba(157,44,175,0.7);">Forgot Password?</a>
                        </div>

                        <button type="submit"
                            class="w-full h-[48px] rounded-full text-white text-[14px] font-semibold transition hover:opacity-90"
                            style="background:linear-gradient(135deg,#9D2CAF,#b83ece);box-shadow:0 4px 18px rgba(157,44,175,0.35);">
                            Login
                        </button>

                        <p class="text-center text-[14px]" style="color:rgba(80,0,100,0.5);">
                            Don't have an account?
                            <button type="button" id="bottomSignupBtn" class="font-semibold" style="color:#9D2CAF;">Sign
                                Up</button>
                        </p>
                    </form>
                    @php
                        $roles = \App\Models\Role::get();
                    @endphp
                    <!-- Signup Form -->
                    <form id="signupForm" class="form-panel hidden-form space-y-5" x-data="{ show: false, loading: false }"
                        @submit="loading = true">
                        @csrf
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-gray-700">Role</label>
                            <select name="role"
                                class="w-full border border-gray-300 rounded-lg px-3 py-2 mt-1 focus:outline-none focus:ring-2 focus:ring-[#363636] choice-select">
                                <option value="">Choose Role</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" {{ old('role') == $role->id ? 'selected' : '' }}>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-red-500 text-sm mt-1 error-name"></p>
                            @error('role')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-[14px] font-medium mb-2" style="color:#4a0070;">Full Name</label>
                            <input type="text" placeholder="Enter your full name" name="name"
                                value="{{ old('name') }}"
                                class="w-full h-[45px] rounded-[10px] px-4 text-[13px] outline-none transition auth-input"
                                style="background:rgba(255,255,255,0.8);border:1px solid rgba(157,44,175,0.18);color:#2d0050;">
                            @error('name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-red-500 text-sm mt-1 error-name"></p>
                        </div>
                        <div>
                            <label class="block text-[14px] font-medium mb-2" style="color:#4a0070;">Email Address</label>
                            <input type="email" placeholder="your@email.com" name="signup_email"
                                value="{{ old('signup_email') }}"
                                class="w-full h-[45px] rounded-[10px] px-4 text-[13px] outline-none transition auth-input"
                                style="background:rgba(255,255,255,0.8);border:1px solid rgba(157,44,175,0.18);color:#2d0050;">
                            @error('signup_email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[14px] font-medium mb-2" style="color:#4a0070;">Mobile Number</label>
                            <input type="text" placeholder="Enter Your Number" name="mobile_number"
                                value="{{ old('mobile_number') }}"
                                class="w-full h-[45px] rounded-[10px] px-4 text-[13px] outline-none transition auth-input"
                                style="background:rgba(255,255,255,0.8);border:1px solid rgba(157,44,175,0.18);color:#2d0050;">
                            @error('mobile_number')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-[14px] font-medium mb-2" style="color:#4a0070;">Password</label>
                            <div class="relative">
                                <input :type="show ? 'text' : 'password'" placeholder="Create Password"
                                    name="signup_password"
                                    class="w-full h-[45px] rounded-[10px] px-4 text-[13px] outline-none transition auth-input"
                                    style="background:rgba(255,255,255,0.8);border:1px solid rgba(157,44,175,0.18);color:#2d0050;">

                                <button type="button" @click="show = !show"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500">

                                    <i x-show="!show" class="fa fa-eye-slash"></i>
                                    <i x-show="show" class="fa fa-eye"></i>
                                </button>
                            </div>
                            @error('signup_password')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit"
                            class="w-full h-[48px] rounded-full text-white text-[14px] font-semibold transition hover:opacity-90"
                            style="background:linear-gradient(135deg,#9D2CAF,#b83ece);box-shadow:0 4px 18px rgba(157,44,175,0.35);">
                            Create Account
                        </button>

                        <p class="text-center text-[14px]" style="color:rgba(80,0,100,0.5);">
                            Already have an account?
                            <button type="button" id="bottomLoginBtn" class="font-semibold"
                                style="color:#9D2CAF;">Login</button>
                        </p>
                    </form>
                </div>
            </div>
        </div>
    </section>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const loginTab = document.getElementById('loginTab');
            const signupTab = document.getElementById('signupTab');
            const tabIndicator = document.getElementById('tabIndicator');
            const loginForm = document.getElementById('loginForm');
            const signupForm = document.getElementById('signupForm');
            const formTitle = document.getElementById('formTitle');
            const formSubtitle = document.getElementById('formSubtitle');
            const bottomSignupBtn = document.getElementById('bottomSignupBtn');
            const bottomLoginBtn = document.getElementById('bottomLoginBtn');

            function showLogin() {
                tabIndicator.style.transform = 'translateX(0%)';
                loginTab.style.color = 'white';
                signupTab.style.color = 'rgba(80,0,100,0.5)';
                signupForm.classList.remove('active-form');
                signupForm.classList.add('hidden-form');
                setTimeout(() => {
                    signupForm.style.display = 'none';
                    loginForm.style.display = 'block';
                    setTimeout(() => {
                        loginForm.classList.remove('hidden-form');
                        loginForm.classList.add('active-form');
                    }, 20);
                }, 150);
                formTitle.textContent = 'Welcome Back!';
                formSubtitle.textContent = 'Sign in to your account';
            }

            function showSignup() {
                tabIndicator.style.transform = 'translateX(100%)';
                signupTab.style.color = 'white';
                loginTab.style.color = 'rgba(80,0,100,0.5)';
                loginForm.classList.remove('active-form');
                loginForm.classList.add('hidden-form');
                setTimeout(() => {
                    loginForm.style.display = 'none';
                    signupForm.style.display = 'block';
                    setTimeout(() => {
                        signupForm.classList.remove('hidden-form');
                        signupForm.classList.add('active-form');
                    }, 20);
                }, 150);
                formTitle.textContent = 'Create Your Account';
                formSubtitle.textContent = 'Register your new account';
            }

            loginTab.addEventListener('click', showLogin);
            signupTab.addEventListener('click', showSignup);
            bottomSignupBtn.addEventListener('click', showSignup);
            bottomLoginBtn.addEventListener('click', showLogin);
            const activeTab = "{{ session('active_tab', 'login') }}";
            if (activeTab === 'signup') {
                showSignup();
            } else {
                showLogin();
            }
        });

        $(document).on('submit', '#loginForm', function(e) {
            e.preventDefault();
            alert(1);
            let form = $(this);
            let btn = form.find('button[type="submit"]');
            btn.prop('disabled', true).text('Please Wait...');
            $('.error-message').remove();
            $.ajax({
                url: "{{ route('admin.authenticate') }}",
                type: "POST",
                data: form.serialize(),
                success: function(response) {
                    console.log(response);
                    if (response.status) {
                        window.location.href = response.redirect;
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Login');
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function(field, messages) {
                            let input = $('[name="' + field + '"]');
                            input.after(
                                '<p class="text-red-500 text-xs mt-1 error-message">' +
                                messages[0] +
                                '</p>'
                            );
                        });
                    } else if (xhr.responseJSON.message) {
                        $('input[name="password"]').after(
                            '<p class="text-red-500 text-xs mt-1 error-message">' +
                            xhr.responseJSON.message +
                            '</p>'
                        );
                    }
                }
            });
        });

        $(document).on('submit', '#signupForm', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find('button[type="submit"]');
            btn.prop('disabled', true).text('Please Wait...');
            $('.error-message').remove();
            $.ajax({
                url: "{{ route('admin.register.update') }}",
                type: "POST",
                data: form.serialize(),
                success: function(response) {
                    if (response.status) {
                        window.location.href = response.redirect;
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).text('Create Account');
                    if (xhr.status === 422) {
                        $.each(xhr.responseJSON.errors, function(field, messages) {
                            let input = $('[name="' + field + '"]');
                            input.after(
                                '<p class="text-red-500 text-xs mt-1 error-message">' +
                                messages[0] +
                                '</p>'
                            );
                        });
                    }
                }
            });
        });
    </script>
@endsection
