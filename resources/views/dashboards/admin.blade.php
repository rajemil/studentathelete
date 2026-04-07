<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Admin Dashboard</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">System overview, activity, and performance trends.</div>
        </div>
    </x-slot>

    <div class="space-y-8">
            <!-- 3-col top row -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="dash-card dash-card-glow rounded-3xl p-6 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-[#FF7A1A]/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Students</div>
                            <div class="mt-2 text-4xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $kpi['students'] }}</div>
                        </div>
                        <div class="h-12 w-12 rounded-2xl bg-[#FF7A1A]/10 flex items-center justify-center text-[#FF7A1A]">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="dash-card dash-card-glow rounded-3xl p-6 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Coaches</div>
                            <div class="mt-2 text-4xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $kpi['coaches'] }}</div>
                        </div>
                        <div class="h-12 w-12 rounded-2xl bg-indigo-500/10 flex items-center justify-center text-indigo-500 dark:text-indigo-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="dash-card dash-card-glow rounded-3xl p-6 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Upcoming events</div>
                            <div class="mt-2 text-4xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $kpi['events_upcoming'] }}</div>
                        </div>
                        <div class="h-12 w-12 rounded-2xl bg-sky-500/10 flex items-center justify-center text-sky-500 dark:text-sky-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2-col bottom row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="dash-card dash-card-glow rounded-3xl p-6 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Sports</div>
                            <div class="mt-2 text-4xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $kpi['sports'] }}</div>
                        </div>
                        <div class="h-12 w-12 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-500 dark:text-emerald-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="dash-card dash-card-glow rounded-3xl p-6 relative overflow-hidden group">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                    <div class="flex items-start justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Teams</div>
                            <div class="mt-2 text-4xl font-bold tracking-tight text-gray-900 dark:text-white">{{ $kpi['teams'] }}</div>
                        </div>
                        <div class="h-12 w-12 rounded-2xl bg-amber-500/10 flex items-center justify-center text-amber-500 dark:text-amber-400">
                            <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <div class="dash-card rounded-3xl p-6 lg:col-span-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Performance trend (last 30 days)</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Average score per day</div>
                        </div>
                    </div>
                    <div class="mt-4 h-72">
                        <canvas
                            class="w-full"
                            data-chart="line"
                            data-chart-label="Avg score"
                            data-chart-labels='@json($chart["performanceTrend"]["labels"])'
                            data-chart-values='@json($chart["performanceTrend"]["values"])'
                        ></canvas>
                    </div>
                </div>

                <div class="dash-card rounded-3xl p-6 lg:col-span-2">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Students by sport</div>
                    <div class="mt-4 h-72">
                        <canvas
                            class="w-full"
                            data-chart="doughnut"
                            data-chart-label="Students"
                            data-chart-labels='@json($chart["sportsDistribution"]["labels"])'
                            data-chart-values='@json($chart["sportsDistribution"]["values"])'
                        ></canvas>
                    </div>
                </div>
            </div>

            @include('partials.insights', ['insights' => $insights])

            <div class="dash-card rounded-3xl overflow-hidden">
                <div class="border-b border-gray-200/60 dark:border-white/10 px-5 py-4">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Injury risk watchlist</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Athletes flagged as Medium/High risk</div>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-white/10">
                    @forelse($riskyAthletes as $athlete)
                        @php
                            $risk = strtolower($athlete->profile->injury_risk ?? 'low');
                            $pill = $risk === 'high'
                                ? 'bg-red-50 text-red-900 border-red-200 dark:bg-red-900/20 dark:text-red-100 dark:border-red-900/40'
                                : 'bg-amber-50 text-amber-900 border-amber-200 dark:bg-amber-900/20 dark:text-amber-100 dark:border-amber-900/40';
                        @endphp
                        <div class="px-5 py-4 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $athlete->name }}</div>
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    Fatigue: {{ $athlete->profile->fatigue_score ?? '—' }} / 100
                                    @if($athlete->profile?->bmi) · BMI {{ number_format((float)$athlete->profile->bmi, 1) }} @endif
                                </div>
                            </div>
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold {{ $pill }}">
                                {{ strtoupper($risk) }}
                            </span>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-sm text-gray-600 dark:text-gray-400">
                            No risky athletes yet. Enter more scores and run `php artisan injury-risk:recompute`.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="dash-card rounded-3xl overflow-hidden">
                <div class="border-b border-gray-200/60 dark:border-white/10 px-6 py-5">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recent activity</div>
                </div>
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($recentActivity as $item)
                        <div class="px-5 py-4 flex items-start justify-between gap-4">
                            <div>
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-200">{{ $item['type'] }}</span>
                                    <span class="ms-2">{{ $item['title'] }}</span>
                                </div>
                            </div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap">
                                {{ optional($item['when'])->diffForHumans() }}
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-sm text-gray-600 dark:text-gray-400">No activity yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

