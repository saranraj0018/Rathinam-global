<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="{{ asset('admin/js/custom.js') }}"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- <script src="{{ asset('admin/js/security.js') }}"></script> --}}
</head>

<body x-data="{ sidebarOpen: true }" class="h-screen overflow-hidden">
    <div class="{{ request()->is('login') ? '' : 'flex h-screen' }}">
        @if (!request()->is('login'))
            @include('admin.components.sidebar')
        @endif
        <div class="flex-1 flex flex-col h-screen">
            @if (!request()->is('login'))
                <!-- Navbar -->
                <div class="bg-white shadow p-3 flex justify-between flex-shrink-0">
                    <button @click="sidebarOpen = !sidebarOpen">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div class="flex items-center gap-6">
                        <button onclick="toggleFullScreen()" class="text-lg">
                            <i id="fullscreenIcon" class="fas fa-expand"></i>
                        </button>
                        <div class="relative">
                            <img id="profileToggle" src="https://i.pravatar.cc/40"
                                class="w-10 h-10 rounded-full cursor-pointer">
                            <div id="profileDropdown"
                                class="hidden absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-lg border z-50">
                                <div class="px-4 py-3 text-sm text-gray-700 border-b">
                                    saranmarkiv18@gmail.com
                                </div>
                                {{-- <a href="#" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100">
                                    <i class="fas fa-user-cog"></i> Settings
                                </a>
                                <a href="#" class="flex items-center gap-3 px-4 py-2 hover:bg-gray-100">
                                    <i class="fas fa-lock"></i> Change Password
                                </a> --}}
                                <form method="POST" action="{{ route('user_logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-red-500 text-left px-4 py-2 hover:bg-gray-100 flex items-center gap-2">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <main class="flex-1 overflow-y-auto p-6 bg-gray-50">
                @yield('content')
            </main>
        </div>
        <div id="toast-container" class="fixed top-5 right-5 space-y-2 z-50"></div>
    </div>
</body>
<script src="//unpkg.com/alpinejs" defer></script>
</html>
<script>
    $(document).ready(function() {
        // Ensure dropdown is closed on page load/refresh
        $("#profileDropdown").hide();
        // Toggle dropdown
        $("#profileToggle").on("click", function(e) {
            e.stopPropagation();
            $("#profileDropdown").toggle();
        });
        // Close when clicking outside
        $(document).on("click", function(e) {
            if (!$(e.target).closest("#profileDropdown, #profileToggle").length) {
                $("#profileDropdown").hide();
            }
        });
    });

    function toggleFullScreen() {
        const icon = document.getElementById("fullscreenIcon");

        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
            icon.classList.remove("fa-expand");
            icon.classList.add("fa-compress"); // change icon
        } else {
            document.exitFullscreen();
            icon.classList.remove("fa-compress");
            icon.classList.add("fa-expand");
        }
    }
</script>
