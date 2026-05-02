@php
    $isModal = request()->boolean('modal');
@endphp

<div class="sticky top-0 z-30">
    <div class="rounded-2xl bg-white border border-gray-200/60 shadow-sm transition dark:bg-white/5 dark:backdrop-blur-xl dark:border-white/10">
        <div class="h-16 px-4 sm:px-6 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                @unless($isModal)
                    <button
                        type="button"
                        class="lg:hidden h-10 w-10 rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 text-gray-700 dark:text-gray-200 hover:bg-white transition"
                        x-on:click="$dispatch('toggle-sidebar')"
                        aria-label="Open navigation"
                    >
                        <svg class="h-5 w-5 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                @endunless

                @isset($header)
                    <div class="min-w-0">
                        {{ $header }}
                    </div>
                @endisset
            </div>

            @unless($isModal)
            <div class="flex items-center gap-2">
                <!-- Theme toggle -->
                <button
                    type="button"
                    class="h-10 w-10 flex items-center justify-center rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 transition group overflow-hidden"
                    x-on:click="toggleTheme()"
                    aria-label="Toggle theme"
                >
                    <div class="relative w-5 h-5 flex items-center justify-center">
                        <svg x-show="theme !== 'dark'" class="absolute inset-0 w-5 h-5 text-amber-500 theme-icon-transition-enter" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="theme === 'dark'" style="display: none;" class="absolute inset-0 w-5 h-5 text-[#FF7A1A] theme-icon-transition-enter" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </div>
                </button>

                <!-- Notifications -->
                <div class="relative" x-data="notificationsDropdown()">
                    <button
                        type="button"
                        class="h-10 w-10 rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 text-gray-700 dark:text-gray-200 hover:bg-white transition relative"
                        x-on:click="open = !open; if(open) refresh()"
                        aria-label="Notifications"
                    >
                        <svg class="h-5 w-5 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 8a6 6 0 10-12 0c0 7-3 7-3 7h18s-3 0-3-7"></path>
                            <path d="M13.73 21a2 2 0 01-3.46 0"></path>
                        </svg>
                        <span
                            x-show="unreadCount > 0"
                            x-text="unreadCount"
                            class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-[#FF7A1A] text-white text-[11px] font-bold flex items-center justify-center shadow-lg shadow-[#FF7A1A]/30"
                        ></span>
                    </button>

                    <div
                        x-show="open"
                        x-transition
                        x-on:click.outside="open=false"
                        class="absolute right-0 mt-2 w-96 max-w-[90vw] rounded-3xl bg-white/95 dark:bg-white/10 backdrop-blur-xl border border-gray-200/70 dark:border-white/20 shadow-2xl overflow-hidden z-50"
                    >
                        <div class="px-4 py-3 border-b border-gray-200/70 dark:border-white/10 flex items-center justify-between gap-3">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Notifications</div>
                            <button type="button" class="text-xs font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white" x-on:click="readAll()">
                                Mark all read
                            </button>
                        </div>

                        <div class="max-h-96 overflow-auto divide-y divide-gray-200/70 dark:divide-white/10">
                            <template x-if="loading">
                                <div class="px-4 py-6 text-sm text-gray-600 dark:text-gray-300">Loading…</div>
                            </template>

                            <template x-if="!loading && notifications.length === 0">
                                <div class="px-4 py-6 text-sm text-gray-600 dark:text-gray-300">No notifications yet.</div>
                            </template>

                            <template x-for="n in notifications" :key="n.id">
                                <button
                                    type="button"
                                    class="w-full text-left px-4 py-3 hover:bg-black/5 dark:hover:bg-white/5 transition"
                                    x-on:click="markRead(n.id)"
                                >
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="n.data?.title || 'Notification'"></div>
                                            <div class="mt-1 text-sm text-gray-700 dark:text-gray-200 line-clamp-2" x-text="n.data?.message || ''"></div>
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400" x-text="formatTime(n.created_at)"></div>
                                        </div>
                                        <span
                                            x-show="!n.read_at"
                                            class="mt-1 h-2 w-2 rounded-full bg-[#FF7A1A] shadow-[0_0_8px_rgba(255,122,26,0.5)] shrink-0"
                                        ></span>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="hidden sm:flex items-center gap-3 rounded-2xl px-3 py-2 bg-gray-50/70 dark:bg-white/5 border border-gray-200/60 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 transition">
                    <div class="h-9 w-9 rounded-2xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white flex items-center justify-center text-sm font-semibold shadow-sm glow-border-orange relative overflow-hidden group">
                        <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                        <span class="relative z-10">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</span>
                    </div>
                    <div class="leading-tight min-w-0">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</div>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="text-sm font-semibold text-gray-700 hover:text-gray-900 dark:text-gray-200 dark:hover:text-white">
                        Profile
                    </a>
                </div>
            </div>
            @endunless
        </div>
    </div>
</div>

