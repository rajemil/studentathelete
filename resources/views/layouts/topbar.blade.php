@php
    $isModal = request()->boolean('modal');
@endphp

<div class="w-full mb-6 sticky top-6 z-30">
    <div class="w-full rounded-2xl bg-white border border-gray-200/60 shadow-sm transition dark:bg-white/5 dark:backdrop-blur-xl dark:border-white/10">
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
                    <div class="flex-1 min-w-0 py-1">
                        {{ $header }}
                    </div>
                @endisset
            </div>

            <div class="flex items-center gap-3">
                @unless($isModal)
                <!-- Theme toggle -->
                <button
                    type="button"
                    class="h-10 w-10 flex items-center justify-center rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 transition group overflow-hidden"
                    x-on:click="toggleTheme()"
                    aria-label="Toggle theme"
                >
                    <div class="relative w-5 h-5 flex items-center justify-center">
                        <svg x-show="theme !== 'dark'" class="absolute inset-0 w-5 h-5 text-amber-500 transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <svg x-show="theme === 'dark'" style="display: none;" class="absolute inset-0 w-5 h-5 text-[#FF7A1A] transition-all duration-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
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
                            class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-[#FF7A1A] text-white text-[11px] font-bold flex items-center justify-center shadow-lg shadow-[#FF7A1A]/30 animate-pulse"
                        ></span>
                    </button>

                    <div
                        x-show="open"
                        x-transition
                        x-on:click.outside="open=false"
                        class="absolute right-0 mt-2 w-96 max-w-[90vw] rounded-3xl bg-white/95 dark:bg-gray-900/95 backdrop-blur-xl border border-gray-200/70 dark:border-white/20 shadow-2xl overflow-hidden z-50"
                    >
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between gap-3 bg-gray-50/50 dark:bg-white/5">
                            <div class="text-base font-bold text-gray-900 dark:text-white">Notifications</div>
                            <button type="button" class="text-xs font-bold text-[#FF7A1A] hover:opacity-80 transition" x-on:click="readAll()">
                                Mark all read
                            </button>
                        </div>

                        <div class="max-h-[32rem] overflow-auto divide-y divide-gray-100 dark:divide-white/5">
                            <template x-if="loading">
                                <div class="px-5 py-10 text-center">
                                    <div class="inline-block h-6 w-6 animate-spin rounded-full border-2 border-solid border-[#FF7A1A] border-r-transparent"></div>
                                    <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">Loading alerts…</div>
                                </div>
                            </template>

                            <template x-if="!loading && notifications.length === 0">
                                <div class="px-5 py-12 text-center">
                                    <div class="h-12 w-12 mx-auto rounded-2xl bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-400 dark:text-gray-500 mb-3">
                                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                                    </div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">All caught up!</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">No notifications yet.</div>
                                </div>
                            </template>

                            <template x-for="n in notifications" :key="n.id">
                                <div class="w-full text-left px-5 py-4 hover:bg-black/2 dark:hover:bg-white/5 transition flex items-start gap-4 relative">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-start justify-between gap-2 mb-1">
                                            <div class="text-sm font-bold text-gray-900 dark:text-white truncate" x-text="n.data?.title || 'Notification'"></div>
                                            <template x-if="!n.read_at">
                                                <span class="flex h-2 w-2 rounded-full bg-[#FF7A1A] shadow-[0_0_10px_rgba(255,122,26,0.6)]"></span>
                                            </template>
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed" x-text="n.data?.message || ''"></div>
                                        <div class="mt-3 flex items-center justify-between">
                                            <div class="text-[10px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest" x-text="formatTime(n.created_at)"></div>
                                            <button type="button" x-on:click="markRead(n.id)" class="text-[10px] font-black text-gray-400 hover:text-[#FF7A1A] transition uppercase tracking-widest px-2 py-1 rounded-lg hover:bg-[#FF7A1A]/10">Mark Read</button>
                                        </div>
                                        
                                        <template x-if="n.data?.sport_application_id">
                                            <div class="mt-4">
                                                <a 
                                                    :href="window.userRole === 'student' ? '/student/sports' : (window.userRole === 'coach' ? '/staff/students' : `/sports/${n.data.sport_slug || n.data.sport_id}#pending-applications`)"
                                                    class="block w-full text-center text-xs font-black text-white px-4 py-2.5 rounded-2xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] shadow-lg shadow-orange-500/20 hover:shadow-orange-500/40 transition hover:scale-[1.02] active:scale-95"
                                                    x-text="window.userRole === 'student' ? 'View My Sports' : 'View Applications Table'"
                                                ></a>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <div class="hidden sm:flex items-center gap-3 rounded-2xl px-3 py-2 bg-gray-50/70 dark:bg-white/5 border border-gray-200/60 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 transition">
                    <div class="h-9 w-9 rounded-2xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white flex items-center justify-center text-sm font-semibold shadow-sm glow-border-orange relative overflow-hidden group">
                        <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                        @if(Auth::user()->profile?->photo_path)
                            <img src="{{ asset('storage/' . Auth::user()->profile->photo_path) }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover relative z-10" />
                        @else
                            <span class="relative z-10">{{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}</span>
                        @endif
                    </div>
                    <div class="leading-tight min-w-0 hidden md:block">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</div>
                    </div>
                    <a href="{{ route('profile.edit') }}" class="text-sm font-semibold text-gray-700 hover:text-gray-900 dark:text-gray-200 dark:hover:text-white ml-2">
                        Profile
                    </a>
                </div>
                @endunless
            </div>
        </div>
    </div>
</div>
