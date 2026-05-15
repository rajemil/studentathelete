<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'SAIMS') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            window.userRole = '{{ auth()->user() ? auth()->user()->role : "" }}';
            (function () {
                try {
                    var saved = localStorage.getItem('theme');
                    // Default to dark mode instead of light
                    var prefersDark = true;
                    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) {
                        prefersDark = false;
                    }
                    var theme = saved || (prefersDark ? 'dark' : 'light');
                    document.documentElement.classList.toggle('dark', theme === 'dark');
                } catch (e) {}
            })();
        </script>
        
        <style>
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body
        class="font-sans antialiased bg-[#FAFAFA] text-gray-900 dark:bg-future dark:text-gray-100"
        x-data="{
            theme: (localStorage.getItem('theme') || 'dark'),
            mobileNavOpen: false,
            actionOpen: false,
            actionTitle: '',
            actionSrc: '',
            toggleTheme() {
                this.theme = this.theme === 'dark' ? 'light' : 'dark';
                localStorage.setItem('theme', this.theme);
                document.documentElement.classList.toggle('dark', this.theme === 'dark');
            }
        }"
        x-on:close-modal.window="actionOpen = false"
        x-on:toggle-sidebar.window="mobileNavOpen = !mobileNavOpen"
        x-init="
            if (!localStorage.getItem('theme')) {
                localStorage.setItem('theme', 'dark');
                document.documentElement.classList.add('dark');
            }
        "
    >
        <!-- Dark Mode Background System (similar to landing page) -->
        <div class="fixed inset-0 z-0 pointer-events-none transition-opacity duration-500 opacity-0 dark:opacity-100">
            <div class="absolute inset-0 glow-spots"></div>
            <div class="absolute inset-0 stars-overlay"></div>
            <div class="absolute inset-0 grid-overlay"></div>
            <div class="absolute inset-0 grid-fine-overlay"></div>
            <div class="absolute inset-0 particles-overlay"></div>
            <div class="absolute inset-0 noise opacity-20"></div>
        </div>

        @php
            $isModal = request()->boolean('modal');
        @endphp

        <div class="min-h-screen relative z-10">
            @if($isModal)
                <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8 py-6">
                    <main>
                        {{ $slot }}
                    </main>
                </div>
            @else
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
                    <div class="flex gap-6">
                        @include('layouts.sidebar')

                        <!-- Mobile sidebar overlay -->
                        <div class="lg:hidden">
                            <div
                                class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm"
                                x-show="mobileNavOpen"
                                x-transition.opacity
                                x-on:click="mobileNavOpen = false"
                            ></div>
                            <div
                                class="fixed left-3 right-12 top-3 z-50"
                                x-show="mobileNavOpen"
                                x-transition
                            >
                                <div class="rounded-2xl bg-white/90 dark:bg-gray-900/90 backdrop-blur border border-gray-200/60 dark:border-white/10 shadow-xl p-3">
                                    <div class="flex items-center justify-between px-2 py-2">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Navigation</div>
                                        <button type="button" class="h-9 w-9 rounded-2xl hover:bg-black/5 dark:hover:bg-white/5" x-on:click="mobileNavOpen=false" aria-label="Close">
                                            <svg class="h-5 w-5 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M6 18L18 6M6 6l12 12"></path>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="px-2 pb-2 max-h-[70vh] overflow-y-auto">
                                        @include('layouts.nav-mobile-links')
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            @include('layouts.topbar')

                            <main class="mt-6">
                                {{ $slot }}
                            </main>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Global Action Modal (Premium Glassmorphism) -->
        <div
            x-show="actionOpen"
            class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
            x-cloak
        >
            <div
                x-show="actionOpen"
                x-transition.opacity
                class="absolute inset-0 bg-black/60 backdrop-blur-md"
                x-on:click="actionOpen = false"
            ></div>

            <div
                x-show="actionOpen"
                x-transition.scale.95
                class="relative w-full max-w-5xl h-[85vh] bg-white dark:bg-gray-900 rounded-[2.5rem] shadow-2xl border border-gray-200/50 dark:border-white/10 overflow-hidden flex flex-col"
            >
                <div class="px-8 py-5 border-b border-gray-100 dark:border-white/5 flex items-center justify-between bg-gray-50/50 dark:bg-white/2 backdrop-blur-xl">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white" x-text="actionTitle"></h3>
                    <button
                        type="button"
                        class="h-10 w-10 flex items-center justify-center rounded-2xl bg-white dark:bg-white/5 border border-gray-200/60 dark:border-white/10 text-gray-500 hover:text-gray-900 dark:hover:text-white transition shadow-sm"
                        x-on:click="actionOpen = false"
                    >
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-hidden bg-gray-50 dark:bg-black/20 relative">
                    <template x-if="actionOpen">
                        <iframe 
                            :src="actionSrc" 
                            class="w-full h-full border-0" 
                            style="color-scheme: dark;"
                            loading="lazy"
                        ></iframe>
                    </template>
                </div>
            </div>
        </div>

        @if(session('status') && request()->boolean('modal'))
            <script>
                if (window.parent && window.parent !== window) {
                    // Refresh parent to show updated data
                    window.parent.location.reload();
                }
            </script>
        @endif
    </body>
</html>
