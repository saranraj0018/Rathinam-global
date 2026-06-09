<aside :class="sidebarOpen ? 'w-60' : 'w-20'"
    class="h-screen flex-shrink-0 bg-[#13151d] text-[#eef0f6] border-r border-[#252934] overflow-y-auto transition-all duration-300
           [background-image:radial-gradient(600px_circle_at_50%_-10%,rgba(201,163,90,.08),transparent_60%)]">

    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 justify-center border-b border-[#252934]">
        <div class="flex items-center justify-center h-20">
            <!-- BIG LOGO -->
            <img src="{{ asset('img/logo2.png') }}" class="w-40 object-contain" x-show="sidebarOpen"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">

            <!-- SMALL LOGO (collapsed) — gold gradient tile to match the console -->
            <div x-show="!sidebarOpen"
                class="w-11 h-11 rounded-[13px] grid place-items-center
                       bg-gradient-to-br from-[#c9a35a] to-[#e6c98b]
                       shadow-[0_8px_24px_-8px_rgba(201,163,90,.6),0_0_0_1px_rgba(230,201,139,.2)_inset]"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
                <img src="{{ asset('img/logo1.jpeg') }}" class="w-6 h-6 object-contain rounded">
            </div>
        </div>
    </div>

    <!-- Menu -->
    <nav class="flex-1 px-3 py-4 space-y-1.5 text-sm">

        {{-- <a href="{{ route('dashboard') }}"
            class="group relative flex items-center gap-3 px-3 py-2.5 rounded-[10px] transition-all duration-150
                {{ request()->is('dashboard')
                    ? 'bg-gradient-to-r from-[#c9a35a] to-[#e6c98b] text-[#1a1408] font-semibold shadow-[0_8px_22px_-10px_rgba(201,163,90,.7)]'
                    : 'text-[#8b91a6] hover:bg-[#1a1d27] hover:text-[#eef0f6]' }}">
            <!-- active accent bar -->
            @if(request()->is('dashboard'))
                <span class="absolute left-0 top-1/2 -translate-y-1/2 h-5 w-[3px] rounded-r bg-[#1a1408]/40"></span>
            @endif
            <i class="fas fa-tachometer-alt w-5 text-center {{ request()->is('dashboard') ? '' : 'text-[#c9a35a] group-hover:text-[#e6c98b]' }}"></i>
            <span x-show="sidebarOpen">Dashboard</span>
        </a> --}}

        <a href="{{ route('applications') }}"
            class="group relative flex items-center gap-3 px-3 py-2.5 rounded-[10px] transition-all duration-150
                {{ request()->is('applications')
                    ? 'bg-gradient-to-r from-[#c9a35a] to-[#e6c98b] text-[#1a1408] font-semibold shadow-[0_8px_22px_-10px_rgba(201,163,90,.7)]'
                    : 'text-[#8b91a6] hover:bg-[#1a1d27] hover:text-[#eef0f6]' }}">
            @if(request()->is('applications'))
                <span class="absolute left-0 top-1/2 -translate-y-1/2 h-5 w-[3px] rounded-r bg-[#1a1408]/40"></span>
            @endif
            <i class="fas fa-file-alt w-5 text-center {{ request()->is('applications') ? '' : 'text-[#c9a35a] group-hover:text-[#e6c98b]' }}"></i>
            <span x-show="sidebarOpen">Applications</span>
        </a>

    </nav>
</aside>
