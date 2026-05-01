<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Injury records</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create and review injury records for athletes you coach.</div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1 rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-5 shadow-sm">
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">New record</div>
            <form class="mt-4 space-y-3" method="POST" action="{{ route('staff.injury_records.store') }}">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Athlete</label>
                    <select name="athlete_user_id" class="mt-1 w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm">
                        @foreach($athletes as $a)
                            <option value="{{ $a->id }}">{{ $a->name }}</option>
                        @endforeach
                    </select>
                    @error('athlete_user_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

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
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Title</label>
                    <input name="title" value="{{ old('title') }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm" />
                    @error('title') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Status</label>
                    <select name="status" class="mt-1 w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm">
                        @foreach(['open' => 'Open', 'monitoring' => 'Monitoring', 'cleared' => 'Cleared'] as $k => $v)
                            <option value="{{ $k }}" @selected(old('status', 'open') === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                    @error('status') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Occurred on</label>
                    <input type="date" name="occurred_on" value="{{ old('occurred_on', now()->toDateString()) }}" class="mt-1 w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm" />
                    @error('occurred_on') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Description (optional)</label>
                    <textarea name="description" rows="4" class="mt-1 w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm">{{ old('description') }}</textarea>
                    @error('description') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <button class="w-full inline-flex justify-center rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm">
                    Save record
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 shadow-sm overflow-hidden">
            <div class="border-b border-gray-200/60 dark:border-white/10 px-5 py-4 flex items-center justify-between gap-4">
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Recent records</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Most recent injuries first.</div>
                </div>
            </div>

            <div class="divide-y divide-gray-200 dark:divide-white/10">
                @forelse($records as $r)
                    @php
                        $status = strtolower((string) $r->status);
                        $pill = match ($status) {
                            'cleared' => 'bg-emerald-50 text-emerald-900 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-100 dark:border-emerald-900/40',
                            'monitoring' => 'bg-amber-50 text-amber-900 border-amber-200 dark:bg-amber-900/20 dark:text-amber-100 dark:border-amber-900/40',
                            default => 'bg-red-50 text-red-900 border-red-200 dark:bg-red-900/20 dark:text-red-100 dark:border-red-900/40',
                        };
                    @endphp
                    <div class="px-5 py-4 flex flex-col gap-2">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ $r->title }}
                                </div>
                                <div class="mt-1 text-xs text-gray-600 dark:text-gray-400">
                                    {{ $r->athlete?->name ?? 'Unknown athlete' }}
                                    @if($r->sport) · {{ $r->sport->name }} @endif
                                    · {{ \Illuminate\Support\Carbon::parse($r->occurred_on)->toFormattedDateString() }}
                                </div>
                            </div>
                            <span class="inline-flex items-center rounded-full border px-3 py-1 text-xs font-semibold shrink-0 {{ $pill }}">
                                {{ strtoupper($status) }}
                            </span>
                        </div>
                        @if($r->description)
                            <div class="text-sm text-gray-700 dark:text-gray-200">
                                {{ $r->description }}
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-5 py-8 text-sm text-gray-600 dark:text-gray-400">No injury records yet.</div>
                @endforelse
            </div>

            @if(method_exists($records, 'links'))
                <div class="px-5 py-4">
                    {{ $records->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>

