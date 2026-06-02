<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="description" content="Rathinam Global University — Application Form for Admission to Ph.D. (Doctoral Programmes 2026–27)." />
    <meta name="theme-color" content="#080810" />
    <title>@yield('title', 'Ph.D. Application — Rathinam Global University')</title>

    {{-- Google Fonts: Sora + DM Sans (theme) and Great Vibes (handwritten signature) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800;900&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700;9..40,800&family=Great+Vibes&display=swap" rel="stylesheet" />

    {{-- Tailwind CSS CDN (matches the RGU marketing site) --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sora: ['Sora', 'system-ui', 'sans-serif'],
                        dm:   ['DM Sans', 'system-ui', 'sans-serif'],
                        sign: ['Great Vibes', 'cursive'],
                    },
                    colors: {
                        ink:   '#080810',
                        ink2:  '#050508',
                    },
                },
            },
        };
    </script>

    <link rel="stylesheet" href="{{ asset('css/scholar.css') }}" />
</head>
<body class="font-dm bg-ink text-slate-200 antialiased">

    @include('partials.navbar')

    <main id="top">
        @yield('content')
    </main>

    @include('partials.footer')

    <script src="{{ asset('js/scholar.js') }}"></script>
    @stack('scripts')
</body>
</html>
