<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Student Dashboard</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Your progress, performance trends, and recommendations.</div>
        </div>
    </x-slot>

    <div class="space-y-8">
            @if(isset($risk['injury_risk']) && in_array(strtolower((string)$risk['injury_risk']), ['medium','high'], true))
                @php
                    $riskLevel = strtolower((string)$risk['injury_risk']);
                    $pill = $riskLevel === 'high'
                        ? 'border-red-200 bg-red-50 text-red-900 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-100'
                        : 'border-amber-200 bg-amber-50 text-amber-900 dark:border-amber-900/40 dark:bg-amber-900/20 dark:text-amber-100';
                @endphp
                <div class="rounded-2xl border px-5 py-4 {{ $pill }}">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold">Injury risk: {{ strtoupper($riskLevel) }}</div>
                            <div class="mt-1 text-sm opacity-90">
                                Fatigue score: {{ $risk['fatigue_score'] ?? '—' }} / 100. Consider adjusting training load and focusing on recovery.
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm p-5 hover:shadow-md transition">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Sports</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $kpi['sports'] }}</div>
                </div>
                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm p-5 hover:shadow-md transition">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Teams</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $kpi['teams'] }}</div>
                </div>
                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm p-5 hover:shadow-md transition">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Avg score (30d)</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($kpi['avg_score_30d'], 1) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm p-5 lg:col-span-3">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Performance trend (last 30 days)</div>
                    <div class="mt-4 h-72">
                        <canvas
                            class="w-full"
                            data-chart="line"
                            data-chart-label="Avg score"
                            data-chart-labels='@json($chart["scoreTrend"]["labels"])'
                            data-chart-values='@json($chart["scoreTrend"]["values"])'
                        ></canvas>
                    </div>
                </div>

                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm p-5 lg:col-span-2">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Upcoming events</div>
                    <div class="mt-4 space-y-3">
                        @forelse($upcomingEvents as $event)
                            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 p-3">
                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $event->title }}</div>
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    {{ $event->starts_at?->format('M j, Y g:i A') ?? 'TBD' }}
                                    @if($event->location) · {{ $event->location }} @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-600 dark:text-gray-400">No upcoming events.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            @include('partials.insights', ['insights' => $insights])

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm">
                    <div class="border-b border-gray-200/60 dark:border-white/10 px-5 py-4">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Recent performance</div>
                    </div>
                    <div class="p-5 space-y-3">
                        @forelse($recentScores as $score)
                            <div class="rounded-2xl bg-gray-50 dark:bg-gray-900/40 px-4 py-3 flex items-center justify-between gap-4">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($score->category) }}</div>
                                    <div class="text-xs text-gray-600 dark:text-gray-400">{{ $score->scored_on?->format('M j, Y') ?? 'TBD' }}</div>
                                </div>
                                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ number_format((float)$score->score, 1) }}</div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-600 dark:text-gray-400">No scores yet.</div>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm">
                    <div class="border-b border-gray-200/60 dark:border-white/10 px-5 py-4">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Training recommendations</div>
                    </div>
                    <div class="p-5 space-y-3">
                        @forelse($recommendations as $rec)
                            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 p-4">
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $rec->title }}</div>
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    {{ $rec->created_at?->diffForHumans() }}
                                    · Status: {{ ucfirst($rec->status) }}
                                </div>
                                <div class="mt-2 text-sm text-gray-700 dark:text-gray-200 line-clamp-3">
                                    {{ $rec->recommendation }}
                                </div>
                            </div>
                        @empty
                            <div class="text-sm text-gray-600 dark:text-gray-400">No recommendations yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
    </div>
</x-app-layout>

