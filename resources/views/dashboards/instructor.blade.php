<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Instructor Dashboard</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Assigned groups, student performance, and health signals.</div>
        </div>
    </x-slot>

    <div class="space-y-8">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm p-5 hover:shadow-md transition">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Teams / classes</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $kpi['teams'] }}</div>
                </div>
                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm p-5 hover:shadow-md transition">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Students</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $kpi['athletes'] }}</div>
                </div>
                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm p-5 hover:shadow-md transition">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Upcoming events</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $kpi['events_upcoming'] }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm p-5 lg:col-span-3">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Group performance (last 30 days)</div>
                    <div class="mt-4 h-72">
                        <canvas
                            class="w-full"
                            data-chart="line"
                            data-chart-label="Avg score"
                            data-chart-labels='@json($chart["teamPerformance"]["labels"])'
                            data-chart-values='@json($chart["teamPerformance"]["values"])'
                        ></canvas>
                    </div>
                </div>

                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm p-5 lg:col-span-2">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Upcoming / recent events</div>
                    <div class="mt-4 space-y-3">
                        @forelse($recentEvents as $event)
                            <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $event->title }}</div>
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    {{ $event->starts_at?->format('M j, Y g:i A') ?? 'TBD' }}
                                    @if($event->location) · {{ $event->location }} @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-600 dark:text-gray-400">No events yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            @include('partials.insights', ['insights' => $insights])

            <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm overflow-hidden">
                <div class="border-b border-gray-200/60 dark:border-white/10 px-5 py-4">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Health &amp; injury watchlist</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">Medium/High risk based on BMI, activity, and drops</div>
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
                            No at-risk students yet.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm">
                <div class="border-b border-gray-200/60 dark:border-white/10 px-5 py-4">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Assigned groups &amp; top students</div>
                </div>
                <div class="p-5 grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @forelse($teams as $team)
                        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $team->name }}</div>
                                    <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">{{ $team->sport?->name ?? 'General' }}</div>
                                </div>
                            </div>

                            <div class="mt-3">
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Top students (by rank)</div>
                                <div class="mt-2 space-y-2">
                                    @forelse($team->students as $student)
                                        <div class="flex items-center justify-between rounded-2xl bg-gray-50 dark:bg-gray-900/40 px-3 py-2">
                                            <div class="text-sm text-gray-900 dark:text-gray-100">{{ $student->name }}</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">Rank #{{ $student->pivot->rank }}</div>
                                        </div>
                                    @empty
                                        <div class="text-sm text-gray-600 dark:text-gray-400">No students assigned.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-sm text-gray-600 dark:text-gray-400">No assigned groups yet.</div>
                    @endforelse
                </div>
            </div>
    </div>
</x-app-layout>
