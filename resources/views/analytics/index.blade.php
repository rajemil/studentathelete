<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Analytics</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Predictions, win probability, and lineup suggestions.</div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8" id="analytics-root">
            <div data-analytics="toast" hidden></div>
            <div class="rounded-xl bg-white dark:bg-gray-800 shadow p-6">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Athlete performance prediction</div>

                <form class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-4 items-end" data-analytics="athlete-form">
                    <div>
                        <x-input-label for="athlete_user_id" value="Athlete (student)" />
                        <select id="athlete_user_id" name="user_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Select...</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}">{{ $s->name }} ({{ $s->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="athlete_sport_id" value="Sport (optional)" />
                        <select id="athlete_sport_id" name="sport_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All sports</option>
                            @foreach($sports as $sport)
                                <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <x-input-label for="horizon_days" value="Horizon (days)" />
                        <x-text-input id="horizon_days" name="horizon_days" type="number" min="1" max="90" class="mt-1 block w-full" value="14" required />
                    </div>

                    <div class="flex md:justify-end gap-2">
                        <x-primary-button type="submit">Predict</x-primary-button>
                        <button type="button" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700" data-analytics="recommend-btn">
                            Recommendations
                        </button>
                    </div>
                </form>

                <div class="mt-6 grid grid-cols-1 lg:grid-cols-5 gap-6">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 lg:col-span-2">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Result</div>
                        <div class="mt-2 grid grid-cols-2 gap-3">
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-900/40 p-3">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Predicted score</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100" data-analytics="pred-score">—</div>
                            </div>
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-900/40 p-3">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Confidence</div>
                                <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100" data-analytics="pred-confidence">—</div>
                            </div>
                            <div class="rounded-lg bg-gray-50 dark:bg-gray-900/40 p-3 col-span-2">
                                <div class="text-xs text-gray-500 dark:text-gray-400">Trend</div>
                                <div class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100" data-analytics="pred-trend">—</div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Raw JSON</div>
                            <pre class="mt-2 max-h-64 overflow-auto rounded-lg bg-gray-950 text-gray-100 p-3 text-xs" data-analytics="pred-json">{}</pre>
                        </div>
                    </div>

                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 lg:col-span-3">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Forecast snapshot</div>
                        <div class="mt-3 h-64">
                            <canvas class="w-full h-full" data-analytics="pred-chart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-white dark:bg-gray-800 shadow p-6">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Team win probability</div>
                <form class="mt-4 grid grid-cols-1 lg:grid-cols-5 gap-4 items-end" data-analytics="winprob-form">
                    <div>
                        <x-input-label for="wp_sport_id" value="Sport (optional)" />
                        <select id="wp_sport_id" name="sport_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All sports</option>
                            @foreach($sports as $sport)
                                <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <x-input-label for="team_a" value="Team A (students)" />
                        <select id="team_a" name="team_a_user_ids[]" multiple size="6" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($students as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <x-input-label for="team_b" value="Team B (students)" />
                        <select id="team_b" name="team_b_user_ids[]" multiple size="6" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($students as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-5 flex justify-end">
                        <x-primary-button type="submit">Calculate</x-primary-button>
                    </div>
                </form>

                <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Team A win probability</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100" data-analytics="wp-a">—</div>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Team B win probability</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100" data-analytics="wp-b">—</div>
                    </div>
                </div>
                <pre class="mt-4 max-h-64 overflow-auto rounded-lg bg-gray-950 text-gray-100 p-3 text-xs" data-analytics="wp-json">{}</pre>
            </div>

            <div class="rounded-xl bg-white dark:bg-gray-800 shadow p-6">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Strongest lineup</div>
                <form class="mt-4 grid grid-cols-1 lg:grid-cols-5 gap-4 items-end" data-analytics="lineup-form">
                    <div>
                        <x-input-label for="lu_sport_id" value="Sport (optional)" />
                        <select id="lu_sport_id" name="sport_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">All sports</option>
                            @foreach($sports as $sport)
                                <option value="{{ $sport->id }}">{{ $sport->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="lg:col-span-3">
                        <x-input-label for="lu_candidates" value="Candidates (students)" />
                        <select id="lu_candidates" name="candidate_user_ids[]" multiple size="6" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($students as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <x-input-label for="lineup_size" value="Lineup size" />
                        <x-text-input id="lineup_size" name="lineup_size" type="number" min="1" max="30" class="mt-1 block w-full" value="5" required />
                    </div>
                    <div class="lg:col-span-5 flex justify-end">
                        <x-primary-button type="submit">Suggest lineup</x-primary-button>
                    </div>
                </form>

                <div class="mt-4 grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-xs text-gray-500 dark:text-gray-400">Lineup strength</div>
                        <div class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100" data-analytics="lu-strength">—</div>
                        <div class="mt-4 space-y-2" data-analytics="lu-list"></div>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4">
                        <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Raw JSON</div>
                        <pre class="mt-2 max-h-80 overflow-auto rounded-lg bg-gray-950 text-gray-100 p-3 text-xs" data-analytics="lu-json">{}</pre>
                    </div>
                </div>
            </div>

            <div class="rounded-xl border border-gray-200 dark:border-gray-700 bg-white/60 dark:bg-gray-800/60 p-4 text-sm text-gray-700 dark:text-gray-200">
                Tip: Predictions improve as you add more `performance_scores` history for each athlete.
            </div>
        </div>
    </div>
</x-app-layout>

