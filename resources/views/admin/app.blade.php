<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }}</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    <!-- jQuery must load before any plugin/custom script that uses $ -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>
    <script src="{{ asset('admin/js/custom.js') }}"></script>
    {{-- <script src="{{ asset('admin/js/security.js') }}"></script> --}}

    <!-- Alpine: defer so it initializes after DOM is ready -->
    <script src="//unpkg.com/alpinejs" defer></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="{ sidebarOpen: true }" class="h-screen overflow-hidden bg-ink text-ftext font-sans">
    <div class="{{ request()->is('login') ? '' : 'flex h-screen' }}">
        @if (!request()->is('login'))
            @include('admin.components.sidebar')
        @endif

        <div class="flex-1 flex flex-col h-screen">
            @if (!request()->is('login'))
                <!-- Navbar -->
                <div class="bg-surface border-b border-line p-3 flex justify-between items-center flex-shrink-0
                            [background-image:radial-gradient(700px_circle_at_85%_-40%,rgba(201,163,90,.08),transparent_55%)]">
                    <button @click="sidebarOpen = !sidebarOpen"
                        class="w-10 h-10 grid place-items-center rounded-[10px] text-fmuted
                               hover:text-gold2 hover:bg-surface2 transition-colors">
                        <i class="fas fa-bars text-lg"></i>
                    </button>

                    <div class="flex items-center gap-5">
                        <button onclick="toggleFullScreen()"
                            class="w-10 h-10 grid place-items-center rounded-[10px] text-fmuted
                                   hover:text-gold2 hover:bg-surface2 transition-colors">
                            <i id="fullscreenIcon" class="fas fa-expand"></i>
                        </button>

                        <div class="relative">
                            <img id="profileToggle" src="https://i.pravatar.cc/40"
                                class="w-10 h-10 rounded-full cursor-pointer ring-2 ring-line hover:ring-gold transition">
                            <div id="profileDropdown"
                                class="hidden absolute right-0 mt-2 w-64 bg-surface rounded-xl shadow-[0_20px_50px_-20px_rgba(0,0,0,.8)] border border-line2 overflow-hidden z-50">
                                <div class="px-4 py-3 text-sm text-fmuted border-b border-line">
                                    saranmarkiv18@gmail.com
                                </div>
                                {{-- <a href="#" class="flex items-center gap-3 px-4 py-2 text-ftext hover:bg-surface2">
                                    <i class="fas fa-user-cog text-gold"></i> Settings
                                </a>
                                <a href="#" class="flex items-center gap-3 px-4 py-2 text-ftext hover:bg-surface2">
                                    <i class="fas fa-lock text-gold"></i> Change Password
                                </a> --}}
                                <form method="POST" action="{{ route('user_logout') }}">
                                    @csrf
                                    <button type="submit"
                                        class="w-full text-left px-4 py-2.5 text-[#e87878] hover:bg-surface2 flex items-center gap-2.5 transition-colors">
                                        <i class="fas fa-sign-out-alt"></i> Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <main class="flex-1 overflow-y-auto bg-ink {{ request()->is('login') ? '' : 'p-0' }}">
                @yield('content')
            </main>
        </div>

        <div id="toast-container" class="fixed top-5 right-5 space-y-2 z-50"></div>
    </div>

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

        function toggleFullScreen() {
            const icon = document.getElementById("fullscreenIcon");
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen();
                icon.classList.remove("fa-expand");
                icon.classList.add("fa-compress");
            } else {
                document.exitFullscreen();
                icon.classList.remove("fa-compress");
                icon.classList.add("fa-expand");
            }
        }
    </script>
</body>

</html>
