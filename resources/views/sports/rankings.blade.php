<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Rankings</h2>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $sport->name }} · Draft list and performance ranking</div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('sports.show', $sport) }}" class="inline-flex items-center rounded-lg border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-800 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:hover:bg-gray-700">
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                <div class="rounded-xl bg-white dark:bg-gray-800 shadow p-5 lg:col-span-2">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Top 10 (avg score)</div>
                    <div class="mt-4 h-64">
                        <canvas
                            class="w-full h-full"
                            data-chart="bar"
                            data-chart-label="Avg score"
                            data-chart-labels='@json($chart["labels"])'
                            data-chart-values='@json($chart["values"])'
                        ></canvas>
                    </div>
                    <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        Ranking is computed from `performance_scores` for this sport (average per student).
                    </div>
                </div>

                <div class="rounded-xl bg-white dark:bg-gray-800 shadow lg:col-span-3">
                    <div class="border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Draft list</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Sorted by avg score, then # of scores, then name.</div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-900/40">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Rank</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Avg score</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Scores</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($ranked as $row)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-900/40">
                                        <td class="px-6 py-4 text-sm font-medium text-gray-900 dark:text-gray-100">#{{ $row['rank'] }}</td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $row['name'] }}</div>
                                            <div class="text-xs text-gray-600 dark:text-gray-400">{{ $row['email'] }}</div>
                                        </td>
                                        <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format($row['avg_score'], 2) }}</td>
                                        <td class="px-6 py-4 text-right text-sm text-gray-700 dark:text-gray-200">{{ $row['score_count'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-10 text-sm text-gray-600 dark:text-gray-400">No students assigned yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

