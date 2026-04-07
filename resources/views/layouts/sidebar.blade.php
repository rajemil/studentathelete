@php
    $role = Auth::user()->role ?? 'student';
@endphp

<aside class="hidden lg:flex lg:flex-col lg:w-72 lg:shrink-0 h-[calc(100vh-3rem)] sticky top-6">
    <div class="h-full flex flex-col rounded-2xl bg-white border border-gray-200/60 shadow-sm transition dark:bg-white/5 dark:backdrop-blur-xl dark:border-white/10">
        <div class="px-5 py-5 flex items-center gap-3">
            <div class="h-10 w-10 rounded-2xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white flex items-center justify-center shadow-sm relative group overflow-hidden">
                <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                <span class="text-sm font-bold tracking-tight relative z-10">SA</span>
            </div>
            <div class="leading-tight">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">SAIMS</div>
                <div class="text-xs text-gray-500 dark:text-gray-400">Sports Analytics</div>
            </div>
        </div>

        <div class="px-3 pb-5">
            <nav class="space-y-1">
                <a href="{{ route('dashboard') }}"
                    class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition
                        {{ request()->routeIs('dashboard') || request()->routeIs('*.dashboard') ? 'bg-gray-900 text-white shadow-md dark:bg-white/10 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                    <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors {{ request()->routeIs('dashboard') || request()->routeIs('*.dashboard') ? 'bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white shadow-sm glow-border-orange' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]' }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM13 3h8v6h-8V3zM3 21h8v-6H3v6z"></path>
                        </svg>
                    </span>
                    Dashboard
                </a>

                @if(in_array($role, ['admin','coach'], true))
                    <a href="{{ route('sports.index') }}"
                        class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition
                            {{ request()->routeIs('sports.*') ? 'bg-gray-900 text-white shadow-md dark:bg-white/10 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                        <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors {{ request()->routeIs('sports.*') ? 'bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white shadow-sm glow-border-orange' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19c0-3 2-5 5-5h6c3 0 5 2 5 5"></path>
                                <path d="M9 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path>
                                <path d="M15 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path>
                            </svg>
                        </span>
                        Sports
                    </a>

                    <a href="{{ route('analytics.index') }}"
                        class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition
                            {{ request()->routeIs('analytics.*') ? 'bg-gray-900 text-white shadow-md dark:bg-white/10 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                        <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors {{ request()->routeIs('analytics.*') ? 'bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white shadow-sm glow-border-orange' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19V5"></path>
                                <path d="M4 19h16"></path>
                                <path d="M7 15l3-3 3 2 5-6"></path>
                            </svg>
                        </span>
                        Analytics
                    </a>
                @endif

                <a href="{{ route('profile.edit') }}"
                    class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition
                        {{ request()->routeIs('profile.*') ? 'bg-gray-900 text-white shadow-md dark:bg-white/10 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                    <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors {{ request()->routeIs('profile.*') ? 'bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white shadow-sm glow-border-orange' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]' }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21a8 8 0 1 0-16 0"></path>
                            <path d="M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path>
                        </svg>
                    </span>
                    Profile
                </a>
            </nav>
        </div>

        <div class="mt-auto px-5 py-5 border-t border-gray-200/60 dark:border-white/10">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full rounded-2xl px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50 transition dark:text-gray-200 dark:hover:bg-white/5">
                    Log out
                </button>
            </form>
        </div>
    </div>
</aside>

