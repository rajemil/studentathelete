<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Team suggestions</h2>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $sport->name }} · Strongest team generator / balanced draft</div>
            </div>
            <a href="{{ route('sports.show', $sport) }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                Back
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="rounded-xl bg-white dark:bg-gray-800 shadow p-6">
                <form method="POST" action="{{ route('sports.team_suggestions.generate', $sport) }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                    @csrf

                    <div>
                        <x-input-label for="mode" value="Mode" />
                        <select id="mode" name="mode" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="strongest" @selected(old('mode', $result['mode'] ?? 'balanced') === 'strongest')>Strongest (stack top ranks)</option>
                            <option value="balanced" @selected(old('mode', $result['mode'] ?? 'balanced') === 'balanced')>Balanced (snake draft)</option>
                        </select>
                        <x-input-error class="mt-2" :messages="$errors->get('mode')" />
                    </div>

                    <div>
                        <x-input-label for="team_count" value="Teams" />
                        <x-text-input id="team_count" name="team_count" type="number" min="2" max="12" class="mt-1 block w-full" required :value="old('team_count', $result['team_count'] ?? 2)" />
                        <x-input-error class="mt-2" :messages="$errors->get('team_count')" />
                    </div>

                    <div>
                        <x-input-label for="team_size" value="Players per team" />
                        <x-text-input id="team_size" name="team_size" type="number" min="2" max="30" class="mt-1 block w-full" required :value="old('team_size', $result['team_size'] ?? 5)" />
                        <x-input-error class="mt-2" :messages="$errors->get('team_size')" />
                    </div>

                    <div class="flex md:justify-end">
                        <x-primary-button>Generate</x-primary-button>
                    </div>
                </form>
            </div>

            @if($result)
                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                    <div class="rounded-xl bg-white dark:bg-gray-800 shadow p-6 lg:col-span-2">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Summary</div>
                        <div class="mt-3 space-y-2 text-sm text-gray-700 dark:text-gray-200">
                            <div><span class="text-gray-500 dark:text-gray-400">Mode:</span> {{ ucfirst($result['mode']) }}</div>
                            <div><span class="text-gray-500 dark:text-gray-400">Teams:</span> {{ $result['team_count'] }}</div>
                            <div><span class="text-gray-500 dark:text-gray-400">Players/team:</span> {{ $result['team_size'] }}</div>
                            <div><span class="text-gray-500 dark:text-gray-400">Player pool used:</span> {{ $result['pool_count'] }}</div>
                        </div>
                        <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                            Team “strength” is computed from average of each player’s avg score in this sport.
                        </div>
                    </div>

                    <div class="rounded-xl bg-white dark:bg-gray-800 shadow lg:col-span-3">
                        <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                            <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Suggested teams</div>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($result['teams'] as $team)
                                <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $team['name'] }}</div>
                                        <div class="text-xs text-gray-600 dark:text-gray-400">
                                            Avg: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($team['avg_score'], 2) }}</span>
                                        </div>
                                    </div>
                                    <div class="mt-3 space-y-2">
                                        @foreach($team['members'] as $m)
                                            <div class="flex items-center justify-between rounded-lg bg-gray-50 dark:bg-gray-900/40 px-3 py-2">
                                                <div class="text-sm text-gray-900 dark:text-gray-100">{{ $m['name'] }}</div>
                                                <div class="text-xs text-gray-600 dark:text-gray-400">{{ number_format($m['score'], 2) }}</div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="rounded-xl bg-white dark:bg-gray-800 shadow">
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Win probability (pairwise)</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Probability Team A wins vs Team B (logistic curve based on avg score difference).</div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/40">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Team</th>
                                    @foreach($result['teams'] as $j => $teamB)
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
        </div>
    </div>
</x-app-layout>

