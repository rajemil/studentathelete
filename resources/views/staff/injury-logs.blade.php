<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Injury &amp; health logs</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Fatigue and injury risk for athletes on your teams (from profiles and scoring data).</div>
        </div>
    </x-slot>

    <div class="space-y-8">
        @if($teamsBySport->isEmpty())
            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-8 text-sm text-gray-600 dark:text-gray-400">
                You are not assigned to any teams yet. Once you coach a team, athlete health metrics will appear here.
            </div>
        @else
            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 shadow-sm overflow-hidden">
                <div class="border-b border-gray-200/60 dark:border-white/10 px-5 py-4">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Your coached teams</div>
                </div>
                <div class="p-5 space-y-4">
                    @foreach($teamsBySport as $sportName => $teams)
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $sportName }}</div>
                            <ul class="mt-2 flex flex-wrap gap-2">
                                @foreach($teams as $team)
                                    <li class="rounded-full border border-gray-200 dark:border-white/10 px-3 py-1 text-sm text-gray-800 dark:text-gray-200">{{ $team->name }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 shadow-sm overflow-hidden">
            <div class="border-b border-gray-200/60 dark:border-white/10 px-5 py-4">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Athlete injury risk &amp; fatigue</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Recompute system-wide with <code class="text-xs bg-gray-100 dark:bg-white/10 px-1 rounded">php artisan injury-risk:recompute</code></div>
            </div>
            <div class="divide-y divide-gray-200 dark:divide-white/10">
                @forelse($athletes as $athlete)
                    @php
                        $risk = strtolower($athlete->profile->injury_risk ?? 'low');
                        $pill = match ($risk) {
                            'high' => 'bg-red-50 text-red-900 border-red-200 dark:bg-red-900/20 dark:text-red-100 dark:border-red-900/40',
                            'medium' => 'bg-amber-50 text-amber-900 border-amber-200 dark:bg-amber-900/20 dark:text-amber-100 dark:border-amber-900/40',
                            default => 'bg-emerald-50 text-emerald-900 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-100 dark:border-emerald-900/40',
                        };
                    @endphp
                    <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $athlete->name }}</div>
                            <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                Fatigue: {{ $athlete->profile->fatigue_score ?? '—' }} / 100
                                @if($athlete->profile?->bmi) · BMI {{ number_format((float) $athlete->profile->bmi, 1) }} @endif
                            </div>
                        </div>
                        <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold shrink-0 {{ $pill }}">
                            {{ strtoupper($risk) }}
                        </span>
                    </div>
                @empty
                    <div class="px-5 py-8 text-sm text-gray-600 dark:text-gray-400">No athletes on your teams yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
