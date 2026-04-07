<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Score entry</h2>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $sport->name }} · PE instructor / coach scoring</div>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('sports.show', $sport) }}" class="inline-flex items-center rounded-2xl border border-gray-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-white transition">
                    Back
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
            <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm p-6 lg:col-span-2">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Add score</div>
                <form class="mt-4 space-y-4" method="POST" action="{{ route('sports.scores.store', $sport) }}">
                    @csrf

                    <div>
                        <x-input-label for="user_id" value="Student" />
                        <select id="user_id" name="user_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-indigo-500 focus:ring-indigo-500" required>
                            <option value="">Select…</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}" @selected(old('user_id')==$s->id)>{{ $s->name }} ({{ $s->email }})</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('user_id')" class="mt-2" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="category" value="Category" />
                            <x-text-input id="category" name="category" class="mt-1 block w-full" type="text" placeholder="overall / speed / stamina" :value="old('category', 'overall')" required />
                            <x-input-error :messages="$errors->get('category')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="score" value="Score (0–100)" />
                            <x-text-input id="score" name="score" class="mt-1 block w-full" type="number" step="0.01" min="0" max="100" :value="old('score')" required />
                            <x-input-error :messages="$errors->get('score')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <x-input-label for="scored_on" value="Date" />
                        <x-text-input id="scored_on" name="scored_on" class="mt-1 block w-full" type="date" :value="old('scored_on', now()->toDateString())" required />
                        <x-input-error :messages="$errors->get('scored_on')" class="mt-2" />
                    </div>

                    <div class="flex justify-end">
                        <x-primary-button>Save</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm p-6 lg:col-span-3">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Average trend (30 days)</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">Avg score per day for this sport</div>
                    </div>
                </div>
                <div class="mt-4 h-72">
                    <canvas
                        class="w-full h-full"
                        data-chart="line"
                        data-chart-label="Avg score"
                        data-chart-labels='@json($chart["labels"])'
                        data-chart-values='@json($chart["values"])'
                    ></canvas>
                </div>
            </div>
        </div>

        <div class="rounded-2xl bg-white/80 dark:bg-gray-900/50 border border-gray-200/60 dark:border-white/10 shadow-sm overflow-hidden">
            <div class="border-b border-gray-200/60 dark:border-white/10 px-6 py-4">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recent scores</div>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                    <thead class="bg-gray-50/70 dark:bg-white/5">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Score</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($recent as $row)
                            <tr class="hover:bg-black/5 dark:hover:bg-white/5 transition">
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200 whitespace-nowrap">{{ $row->scored_on?->format('M j, Y') ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $row->student?->name ?? '—' }}</div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-700 dark:text-gray-200">{{ $row->category }}</td>
                                <td class="px-6 py-4 text-right text-sm font-semibold text-gray-900 dark:text-gray-100">{{ number_format((float)$row->score, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-sm text-gray-600 dark:text-gray-400">No scores yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>

