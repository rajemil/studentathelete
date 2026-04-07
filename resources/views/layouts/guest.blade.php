<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased min-h-screen bg-future text-white">
        <!-- Background system (matches landing) -->
        <div class="pointer-events-none fixed inset-0 z-0 glow-spots"></div>
        <div class="pointer-events-none fixed inset-0 z-0 stars-overlay"></div>
        <div class="pointer-events-none fixed inset-0 z-0 grid-overlay"></div>
        <div class="pointer-events-none fixed inset-0 z-0 grid-fine-overlay"></div>
        <div class="pointer-events-none fixed inset-0 z-0 particles-overlay"></div>
        <div class="pointer-events-none fixed inset-0 z-0 noise opacity-20"></div>

        <div class="relative z-10 min-h-screen flex items-center justify-center px-6 py-12">
            <a
                href="/"
                class="fixed left-6 top-6 inline-flex items-center gap-2 rounded-xl px-3 py-2 text-sm font-semibold text-white/80
                       bg-white/0 hover:text-white transition-colors
                       hover:shadow-[0_0_0_1px_rgba(255,122,26,0.22),0_0_36px_rgba(255,122,26,0.16)]
                       focus:outline-none focus-visible:ring-2 focus-visible:ring-[#FF7A1A]/40"
                style="animation: slide-in-left 420ms ease-out both;"
                aria-label="Back to Home"
            >
                <span class="accent-orange">←</span>
                <span class="hover:accent-orange">Back to Home</span>
            </a>

            <div class="w-full max-w-md">
                <a href="/" class="inline-flex items-center gap-3">
                    <div class="h-10 w-10 rounded-2xl bg-white/5 border border-white/10 grid place-items-center">
                        <span class="text-sm font-semibold tracking-tight"><span class="accent-orange">A</span>I</span>
                    </div>
                    <div class="text-sm font-semibold tracking-[0.18em]">
                        <span class="accent-orange">A</span>THLETEAI
                    </div>
                </a>

                <div class="mt-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
</html>
