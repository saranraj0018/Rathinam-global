<nav id="navbar" aria-label="Main navigation">
    <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
        <a href="https://rathinam.global/" class="flex items-center flex-shrink-0">
            <img src="{{ asset('images/logo.png') }}" alt="RGU — Rathinam Global University"
                class="h-10 w-auto object-contain drop-shadow-lg transition-transform duration-300 hover:scale-105" />
        </a>

        <div class="hidden lg:flex items-center gap-1">
            <a href="https://rathinam.global/" class="nav-link">Home<span class="nav-link-underline"></span></a>
            <a href="{{ route('scholar.create') }}" class="nav-link">Apply<span class="nav-link-underline"></span></a>
            <a href="https://rathinam.global/#about" class="nav-link">About<span class="nav-link-underline"></span></a>
            <a href="https://rathinam.global/#contact" class="nav-link">Contact<span
                    class="nav-link-underline"></span></a>
        </div>

        @if (!empty(session()->get('user')))
            <div class="relative">
                <img id="profileToggle" src="https://i.pravatar.cc/40"
                    class="w-10 h-10 rounded-full cursor-pointer ring-2 ring-line hover:ring-gold transition">
                <div id="profileDropdown"
                    class="hidden absolute right-0 mt-2 w-64 bg-surface rounded-xl shadow-[0_20px_50px_-20px_rgba(0,0,0,.8)] border border-line2 overflow-hidden z-50">
                    {{-- <div class="px-4 py-3 text-sm text-fmuted border-b border-line">
                                    saranmarkiv18@gmail.com
                                </div> --}}
                    {{-- <a href="#" class="flex items-center gap-3 px-4 py-2 text-ftext hover:bg-surface2">
                                    <i class="fas fa-user-cog text-gold"></i> Settings
                                </a>
                                <a href="#" class="flex items-center gap-3 px-4 py-2 text-ftext hover:bg-surface2">
                                    <i class="fas fa-lock text-gold"></i> Change Password
                                </a> --}}
                    <form method="POST" action="{{ route('auth.logout') }}">
                        @csrf
                        <button type="submit"
                            class="w-full text-left px-4 py-2.5 text-[#e87878] hover:bg-surface2 flex items-center gap-2.5 transition-colors">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        @else
            {{-- Logged out: Log In / Sign Up --}}
            <div class="hidden lg:flex items-center gap-3">
                <a href="{{ route('auth.login') }}" class="nav-btn nav-btn--ghost">Log In</a>
                <a href="{{ route('auth.register') }}" class="nav-btn nav-btn--primary">Sign Up</a>
            </div>
        @endif
        <button id="mobile-menu-toggle"
            class="lg:hidden p-2 rounded-lg border transition-all duration-200 hover:bg-white/10"
            style="border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.8);"
            aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="mobile-menu">
            <svg id="icon-menu" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="4" y1="6" x2="20" y2="6" />
                <line x1="4" y1="12" x2="20" y2="12" />
                <line x1="4" y1="18" x2="20" y2="18" />
            </svg>
            <svg id="icon-close" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                style="display:none;">
                <line x1="18" y1="6" x2="6" y2="18" />
                <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>
    </div>

    <div id="mobile-menu" class="lg:hidden px-6 py-4 space-y-1 border-t"
        style="background: rgba(8,8,16,0.96); backdrop-filter: blur(24px); border-color: rgba(255,255,255,0.08);">
        <a href="https://rathinam.global/" class="block px-4 py-3 text-sm rounded-xl hover:bg-white/[0.06]"
            style="color: rgba(255,255,255,0.7);">Home</a>
        <a href="{{ route('scholar.create') }}" class="block px-4 py-3 text-sm rounded-xl hover:bg-white/[0.06]"
            style="color: rgba(255,255,255,0.7);">Apply</a>
        <a href="https://rathinam.global/#about" class="block px-4 py-3 text-sm rounded-xl hover:bg-white/[0.06]"
            style="color: rgba(255,255,255,0.7);">About</a>
        <a href="https://rathinam.global/#contact" class="block px-4 py-3 text-sm rounded-xl hover:bg-white/[0.06]"
            style="color: rgba(255,255,255,0.7);">Contact</a>
        <div class="flex gap-3 pt-3">
            <a href="{{ route('auth.login') }}" class="nav-btn nav-btn--ghost flex-1">Log In</a>
            <a href="{{ route('auth.register') }}" class="nav-btn nav-btn--primary flex-1">Sign Up</a>
        </div>
    </div>
</nav>
<script>
    $(document).ready(function() {
        $("#profileDropdown").hide();
        $("#profileToggle").on("click", function(e) {
            e.stopPropagation();
            $("#profileDropdown").toggle();
        });
        $(document).on("click", function(e) {
            if (!$(e.target).closest("#profileDropdown, #profileToggle").length) {
                $("#profileDropdown").hide();
            }
        });
    });
</script>
