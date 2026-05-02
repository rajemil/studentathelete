@php
    $role = Auth::user()->role ?? 'student';
    $isAdmin = $role === 'admin';
    $isCoach = $role === 'coach';
    $isInstructor = $role === 'instructor';
    $isStudent = $role === 'student';
    $staffSports = $isAdmin || $isCoach || $isInstructor;
    $coachLike = $isCoach || $isInstructor;
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

        <div class="px-3 pb-5 flex-1 overflow-y-auto">
            <nav class="space-y-1">
                <a href="{{ route('dashboard') }}"
                    class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition
                        {{ request()->routeIs('dashboard') || request()->routeIs('*.dashboard') ? 'bg-gray-900 text-white shadow-md dark:bg-white/10 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                    <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors {{ request()->routeIs('dashboard') || request()->routeIs('*.dashboard') ? 'bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white shadow-sm glow-border-orange' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]' }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M3 13h8V3H3v10zM13 21h8V11h-8v10zM13 3h8v6h-8V3zM3 21h8v-6H3v6z"></path>
                        </svg>
                    </span>
                    @if($isAdmin)
                        Admin dashboard
                    @elseif($isCoach)
                        Coach dashboard
                    @elseif($isInstructor)
                        Instructor dashboard
                    @else
                        Dashboard
                    @endif
                </a>

                @if($isAdmin)
                    <div class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Faculty management</div>
                    <a href="{{ route('admin.users.index') }}"
                        class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition
                            {{ request()->routeIs('admin.users.*') ? 'bg-gray-900 text-white shadow-md dark:bg-white/10 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                        <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors {{ request()->routeIs('admin.users.*') ? 'bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white shadow-sm glow-border-orange' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </span>
                        Faculty management
                    </a>

                    <div class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Team & sport</div>
                @endif

                @if($staffSports)
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
                        @if($isAdmin)
                            Sports & teams
                        @elseif($isInstructor)
                            Assigned classes & sports
                        @else
                            Coached teams & sports
                        @endif
                    </a>
                @endif

                @if($coachLike)
                    <div class="px-3 pt-2 pb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">{{ $isInstructor ? 'Instruction' : 'Coaching' }}</div>
                    <a href="{{ route('staff.performance_scores.hub') }}"
                        class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition
                            {{ request()->routeIs('staff.performance_scores.hub') ? 'bg-gray-900 text-white shadow-md dark:bg-white/10 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                        <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors {{ request()->routeIs('staff.performance_scores.hub') ? 'bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white shadow-sm glow-border-orange' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </span>
                        {{ $isInstructor ? 'Student performance scores' : 'Performance scores' }}
                    </a>
                    <a href="{{ route('staff.injury_logs.index') }}"
                        class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition
                            {{ request()->routeIs('staff.injury_logs.index') ? 'bg-gray-900 text-white shadow-md dark:bg-white/10 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                        <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors {{ request()->routeIs('staff.injury_logs.index') ? 'bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white shadow-sm glow-border-orange' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                        </span>
                        {{ $isInstructor ? 'Health & injury logs' : 'Injury & health logs' }}
                    </a>
                    <a href="{{ route('staff.ai_recommendations.hub') }}"
                        class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition
                            {{ request()->routeIs('staff.ai_recommendations.hub') ? 'bg-gray-900 text-white shadow-md dark:bg-white/10 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                        <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors {{ request()->routeIs('staff.ai_recommendations.hub') ? 'bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white shadow-sm glow-border-orange' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </span>
                        AI recommendations
                    </a>
                @endif

                @if($staffSports)
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

                @if($isAdmin)
                    <div class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Insights & system</div>
                    <a href="{{ route('admin.reports.index') }}"
                        class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition
                            {{ request()->routeIs('admin.reports.index') ? 'bg-gray-900 text-white shadow-md dark:bg-white/10 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                        <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors {{ request()->routeIs('admin.reports.index') ? 'bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white shadow-sm glow-border-orange' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M9 17v-2m3 2v-4m3 4v-6m2 18H4a2 2 0 01-2-2V4a2 2 0 012-2h16a2 2 0 012 2v16a2 2 0 01-2 2z"></path>
                            </svg>
                        </span>
                        Reports
                    </a>
                    <a href="{{ route('notifications.index') }}"
                        class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition
                            {{ request()->routeIs('notifications.*') ? 'bg-gray-900 text-white shadow-md dark:bg-white/10 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                        <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors {{ request()->routeIs('notifications.*') ? 'bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white shadow-sm glow-border-orange' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </span>
                        Notifications
                    </a>
                    <a href="{{ route('admin.system.index') }}"
                        class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition
                            {{ request()->routeIs('admin.system.index') ? 'bg-gray-900 text-white shadow-md dark:bg-white/10 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                        <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors {{ request()->routeIs('admin.system.index') ? 'bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white shadow-sm glow-border-orange' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </span>
                        System config
                    </a>
                @endif

                @if($isStudent)
                    <a href="{{ route('student.sports.index') }}"
                        class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition
                            {{ request()->routeIs('student.sports.*') ? 'bg-gray-900 text-white shadow-md dark:bg-white/10 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                        <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors {{ request()->routeIs('student.sports.*') ? 'bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white shadow-sm glow-border-orange' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M4 19c0-3 2-5 5-5h6c3 0 5 2 5 5"></path>
                                <path d="M9 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"></path>
                            </svg>
                        </span>
                        Sports & registration
                    </a>
                    <a href="{{ route('student.dashboard') }}#ai-recommendations"
                        class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white">
                        <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </span>
                        AI recommendations
                    </a>
                @endif

                <div class="px-3 pt-4 pb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-400 dark:text-gray-500">Account</div>
                <a href="{{ route('profile.edit') }}"
                    class="group flex items-center gap-3 rounded-2xl px-3 py-2 text-sm font-medium transition
                        {{ request()->routeIs('profile.*') ? 'bg-gray-900 text-white shadow-md dark:bg-white/10 dark:text-white' : 'text-gray-700 hover:bg-gray-50 dark:text-gray-300 dark:hover:bg-white/5 dark:hover:text-white' }}">
                    <span class="h-9 w-9 rounded-2xl flex items-center justify-center transition-colors {{ request()->routeIs('profile.*') ? 'bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white shadow-sm glow-border-orange' : 'bg-gray-100 text-gray-500 dark:bg-white/5 dark:text-gray-400 group-hover:text-[#FF7A1A] dark:group-hover:text-[#FFB24D]' }}">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21a8 8 0 1 0-16 0"></path>
                            <path d="M12 13a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"></path>
                        </svg>
                    </span>
                    @if($isAdmin)
                        Settings
                    @else
                        Profile & settings
                    @endif
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
