<aside :class="sidebarOpen ? 'w-60' : 'w-20'"
    class="h-screen flex-shrink-0 bg-[#9D2CAF] text-white overflow-y-auto transition-all duration-300">
    <!-- Logo -->
    <div class="flex items-center gap-3 px-5 justify-center">
        <!-- Logo Image -->
        <div class="flex items-center justify-center h-20">
            <!-- BIG LOGO -->
            <img src="{{ asset('img/logo2.png') }}" class="w-40 object-contain" x-show="sidebarOpen"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
            <!-- SMALL LOGO -->
            <img src="{{ asset('img/logo1.jpeg') }}" class="w-6 object-contain" x-show="!sidebarOpen"
                x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90"
                x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
        </div>
    </div>
    <!-- Menu -->
    <nav class="flex-1 px-3 py-4 space-y-2 text-sm">
        <a href="{{ route('dashboard') }}"
            class="flex items-center gap-3 px-3 py-2 rounded transition-all {{ request()->is('dashboard')
                ? 'bg-[rgb(255,232,232)] text-black font-semibold'
                : 'text-white hover:bg-gray-600' }}">
            <i class="fas fa-tachometer-alt"></i>
            <span x-show="sidebarOpen">Dashboard</span>
        </a>
        <a href="{{ route('applications') }}"
            class="flex items-center gap-3 px-3 py-2 rounded transition-all {{ request()->is('applications')
                ? 'bg-[rgb(255,232,232)] text-black font-semibold'
                : 'text-white hover:bg-gray-600' }}">
            <i class="fas fa-tachometer-alt"></i>
            <span x-show="sidebarOpen">Applications</span>
        </a>
    </nav>
</aside>
