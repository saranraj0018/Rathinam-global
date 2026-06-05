@php
    $user = auth()->user();
    $displayName = $user?->name ?? 'User';
    $displayEmail = $user?->email ?? '';
    $initial = strtoupper(mb_substr($displayName, 0, 1));
@endphp
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
            <a href="https://rathinam.global/#contact" class="nav-link">Contact<span class="nav-link-underline"></span></a>
        </div>

        <div class="hidden lg:block profile" data-profile>
            <button class="profile__btn" data-profile-toggle aria-haspopup="true" aria-expanded="false">
                <span class="profile__avatar">{{ $initial }}</span>
                <span class="profile__name">{{ $displayName }}</span>
                <svg class="profile__chev" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9" /></svg>
            </button>
            <div class="profile__menu" data-profile-menu hidden>
                <div class="profile__card">
                    <span class="profile__avatar profile__avatar--lg">{{ $initial }}</span>
                    <div class="profile__meta">
                        <p class="profile__card-name">{{ $displayName }}</p>
                        <p class="profile__card-email">{{ $displayEmail }}</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button type="submit" class="profile__signout">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" /><polyline points="16 17 21 12 16 7" /><line x1="21" y1="12" x2="9" y2="12" /></svg>
                        Sign Out
                    </button>
                </form>
            </div>
        </div>

        <button id="mobile-menu-toggle" class="lg:hidden p-2 rounded-lg border transition-all duration-200 hover:bg-white/10"
            style="border-color: rgba(255,255,255,0.2); color: rgba(255,255,255,0.8);"
            aria-label="Toggle navigation menu" aria-expanded="false" aria-controls="mobile-menu">
            <svg id="icon-menu" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="4" y1="6" x2="20" y2="6" /><line x1="4" y1="12" x2="20" y2="12" /><line x1="4" y1="18" x2="20" y2="18" />
            </svg>
            <svg id="icon-close" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                <line x1="18" y1="6" x2="6" y2="18" /><line x1="6" y1="6" x2="18" y2="18" />
            </svg>
        </button>
    </div>

    <div id="mobile-menu" class="lg:hidden px-6 py-4 space-y-1 border-t"
        style="background: rgba(8,8,16,0.96); backdrop-filter: blur(24px); border-color: rgba(255,255,255,0.08);">
        <a href="https://rathinam.global/" class="block px-4 py-3 text-sm rounded-xl hover:bg-white/[0.06]" style="color: rgba(255,255,255,0.7);">Home</a>
        <a href="{{ route('scholar.create') }}" class="block px-4 py-3 text-sm rounded-xl hover:bg-white/[0.06]" style="color: rgba(255,255,255,0.7);">Apply</a>
        <a href="https://rathinam.global/#about" class="block px-4 py-3 text-sm rounded-xl hover:bg-white/[0.06]" style="color: rgba(255,255,255,0.7);">About</a>
        <a href="https://rathinam.global/#contact" class="block px-4 py-3 text-sm rounded-xl hover:bg-white/[0.06]" style="color: rgba(255,255,255,0.7);">Contact</a>
        <div class="profile-mobile">
            <div class="profile-mobile__head">
                <span class="profile__avatar">{{ $initial }}</span>
                <div>
                    <p>{{ $displayName }}</p>
                    <p class="profile-mobile__email">{{ $displayEmail }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('auth.logout') }}">
                @csrf
                <button type="submit" class="profile__signout">Sign Out</button>
            </form>
        </div>
    </div>
</nav>
