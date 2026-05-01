<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Participation logs</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Log training, competition, and recovery to track workload over time.</div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-5 shadow-sm">
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">New log</div>
            <form class="mt-4 space-y-3" method="POST" action="{{ route('student.participation_logs.store') }}">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Sport (optional)</label>
                    <select name="sport_id" class="mt-1 w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm">
                        <option value="">General</option>
                        @foreach($sports as $s)
                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                        @endforeach
                    </select>
                    @error('sport_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Activity type</label>
                    <select name="activity_type" class="mt-1 w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm">
                        @foreach(['training' => 'Training', 'competition' => 'Competition', 'recovery' => 'Recovery', 'other' => 'Other'] as $k => $v)
                            <option value="{{ $k }}" @selected(old('activity_type', 'training') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                    @error('activity_type') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Duration (minutes)</label>
                    <input name="duration_minutes" value="{{ old('duration_minutes') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm" />
                    @error('duration_minutes') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Logged on</label>
                    <input type="date" name="logged_on" value="{{ old('logged_on', now()->toDateString()) }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm" />
                    @error('logged_on') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Notes (optional)</label>
                    <textarea name="notes" rows="4" class="mt-1 w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm">{{ old('notes') }}</textarea>
                    @error('notes') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <button class="w-full inline-flex justify-center rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm">
                    Save log
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 shadow-sm overflow-hidden">
            <div class="border-b border-gray-200/60 dark:border-white/10 px-5 py-4">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Your recent activity</div>
                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">This is private to you by default (staff views can be added next).</div>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-white/10">
                @forelse($logs as $log)
                    <div class="px-5 py-4">
                        <div class="flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ ucfirst($log->activity_type) }}
                                    @if($log->sport) · {{ $log->sport->name }} @endif
                                </div>
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    {{ \Illuminate\Support\Carbon::parse($log->logged_on)->toFormattedDateString() }}
                                    @if($log->duration_minutes) · {{ $log->duration_minutes }} min @endif
                                </div>
                                @if($log->notes)
                                    <div class="mt-2 text-sm text-gray-700 dark:text-gray-200">{{ $log->notes }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="px-5 py-8 text-sm text-gray-600 dark:text-gray-400">No logs yet.</div>
                @endforelse
            </div>

            @if(method_exists($logs, 'links'))
                <div class="px-5 py-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

