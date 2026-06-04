<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Team recommendations</h2>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $sport->name }} · Predictive analytics and data-driven lineup suggestions</div>
            </div>
            @unless(request()->boolean('modal'))
                <a href="{{ route('sports.show', $sport) }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                    Back
                </a>
            @endunless
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-xl bg-white dark:bg-gray-800 shadow p-6">
                <form method="POST" action="{{ route('sports.team_suggestions.generate', $sport) }}{{ request()->boolean('modal') ? '?modal=1' : '' }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf

                    <div>
                        <x-input-label for="mode" value="Recommendation type" />
                        <select id="mode" name="mode" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="strongest" @selected(old('mode', $result['mode'] ?? 'balanced') === 'strongest')>Strongest team suggestions</option>
                            <option value="balanced" @selected(old('mode', $result['mode'] ?? 'balanced') === 'balanced')>Balanced team suggestions</option>
                            <option value="compatibility" @selected(old('mode', $result['mode'] ?? '') === 'compatibility')>Team compatibility analysis</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('mode')" />
                    </div>

                    <div>
                        <x-input-label for="team_count" value="Teams" />
                        <x-text-input id="team_count" name="team_count" type="number" min="2" max="12" class="mt-1 block w-full" required :value="old('team_count', $result['team_count'] ?? 2)" />
                        <x-input-error class="mt-2" :messages="$errors->get('team_count')" />
                    </div>

                    <div>
                        <x-input-label for="team_size" value="Student athletes per team" />
                        <x-text-input id="team_size" name="team_size" type="number" min="2" max="30" class="mt-1 block w-full" required :value="old('team_size', $result['team_size'] ?? 5)" />
                        <x-input-error class="mt-2" :messages="$errors->get('team_size')" />
                    </div>

                    <div class="flex md:justify-end">
                        <x-primary-button>Generate recommendations</x-primary-button>
                    </div>
                </form>
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    Uses statistical analysis: Elo-style ratings, performance trends, injury risk, fatigue scores, and team balance scoring. This is predictive analytics, not machine learning.
                </p>
            </div>

            @if($result)
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    <div class="rounded-xl bg-white dark:bg-gray-800 shadow p-6 lg:col-span-2">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Summary</div>
                        <div class="mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-200">
                            <div><span class="text-gray-500 dark:text-gray-400">Type:</span> {{ ucfirst(str_replace('_', ' ', $result['mode'])) }}</div>
                            <div><span class="text-gray-500 dark:text-gray-400">Teams:</span> {{ $result['team_count'] }}</div>
                            <div><span class="text-gray-500 dark:text-gray-400">Student athletes per team:</span> {{ $result['team_size'] }}</div>
                            <div><span class="text-gray-500 dark:text-gray-400">Pool size:</span> {{ $result['pool_count'] }}</div>
                        </div>
                    </div>

                    <div class="rounded-xl bg-white dark:bg-gray-800 shadow lg:col-span-3">
                        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Suggested lineups</div>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($result['teams'] as $team)
                                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $team['name'] }}</div>
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $team['explanation'] ?? '' }}</div>
                                        </div>
                                        <div class="text-right text-xs text-gray-600 dark:text-gray-400">
                                            <div>Strength: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($team['team_strength'] ?? $team['avg_score'], 1) }}</span></div>
                                            <div>Confidence: <span class="font-semibold">{{ number_format($team['confidence_score'] ?? 0, 2) }}</span></div>
                                            <div>Balance: <span class="font-semibold">{{ number_format($team['balance_score'] ?? 0, 2) }}</span></div>
                                        </div>
                                    </div>
                                    <div class="mt-3 space-y-2">
                                        @foreach($team['members'] as $m)
                                            <div class="rounded-lg bg-gray-50 dark:bg-gray-900/40 px-3 py-2">
                                                <div class="flex items-center justify-between">
                                                    <div class="text-sm text-gray-900 dark:text-gray-100">{{ $m['name'] }}</div>
                                                    <div class="text-xs text-gray-600 dark:text-gray-400">{{ number_format($m['score'] ?? $m['predicted_score'], 1) }}</div>
                                                </div>
                                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $m['explanation'] ?? '' }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                @if(!empty($result['compatibility']))
                    <div class="rounded-xl bg-white dark:bg-gray-800 shadow p-6">
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Team compatibility analysis</div>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $result['compatibility']['summary'] ?? '' }}</p>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($result['compatibility']['top_pairs'] ?? [] as $pair)
                                <div class="rounded-lg border border-gray-200 dark:border-gray-700 px-3 py-2 text-sm">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $pair['athlete_a'] }} &amp; {{ $pair['athlete_b'] }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Compatibility {{ $pair['compatibility'] }} — {{ $pair['note'] }}</div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($result['win_probabilities']))
                    <div class="rounded-xl bg-white dark:bg-gray-800 shadow">
                        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Win probability (Elo-based)</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Statistical win probability between suggested teams using Elo-style team strength ratings.</div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-900/40">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Team</th>
                                        @foreach($result['teams'] as $teamB)
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">{{ $teamB['name'] }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach($result['teams'] as $i => $teamA)
                                        <tr>
                                            <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $teamA['name'] }}</td>
                                            @foreach($result['teams'] as $j => $teamB)
                                                <td class="px-6 py-4 text-right text-sm text-gray-700 dark:text-gray-200">
                                                    @if($i === $j)
                                                        —
                                                    @else
                                                        {{ $result['win_probabilities'][$i][$j] ?? '' }}%
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
