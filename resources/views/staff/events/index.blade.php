<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Schedule & Events</h2>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage intramurals, PE days, UWeek, school sports programs, and team schedules.</div>
            </div>
            <div>
                <a href="{{ route('staff.events.create') }}" class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-90 transition">
                    <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Schedule Event
                </a>
            </div>
        </div>
    </x-slot>

    @if (session('status'))
        <div class="mb-5 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
            {{ session('status') }}
        </div>
    @endif

    <!-- Filters -->
    <div class="mb-6 rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-4 shadow-sm">
        <form method="GET" action="{{ route('staff.events.index') }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Team</label>
                <select name="team_id" class="mt-1 w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm">
                    <option value="">All Teams</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}" @selected(request('team_id') == $team->id)>
                            {{ $team->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="w-full sm:w-auto min-w-[150px]">
                <label class="block text-xs font-semibold text-gray-600 dark:text-gray-300">Type</label>
                <select name="event_type" class="mt-1 w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm">
                    <option value="">All Types</option>
                    @foreach(\App\Support\EventTypes::labels() as $k => $v)
                        <option value="{{ $k }}" @selected(request('event_type') === $k)>{{ $v }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="inline-flex justify-center rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    Filter
                </button>
                @if(request()->anyFilled(['team_id', 'event_type']))
                    <a href="{{ route('staff.events.index') }}" class="inline-flex justify-center rounded-xl bg-gray-100 dark:bg-gray-800 px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700 transition">
                        Clear
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Events List -->
    <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 shadow-sm overflow-hidden">
        <div class="divide-y divide-gray-200 dark:divide-white/10">
            @forelse($events as $event)
                @php
                    $type = strtolower((string) $event->event_type);
                    $badgeColor = match ($type) {
                        'game' => 'bg-orange-50 text-orange-900 border-orange-200 dark:bg-orange-900/20 dark:text-orange-100 dark:border-orange-900/40',
                        'tryout' => 'bg-purple-50 text-purple-900 border-purple-200 dark:bg-purple-900/20 dark:text-purple-100 dark:border-purple-900/40',
                        'meeting' => 'bg-blue-50 text-blue-900 border-blue-200 dark:bg-blue-900/20 dark:text-blue-100 dark:border-blue-900/40',
                        'intramurals', 'pe_day', 'uweek', 'school_sports_program' => 'bg-amber-50 text-amber-900 border-amber-200 dark:bg-amber-900/20 dark:text-amber-100 dark:border-amber-900/40',
                        default => 'bg-teal-50 text-teal-900 border-teal-200 dark:bg-teal-900/20 dark:text-teal-100 dark:border-teal-900/40',
                    };
                @endphp
                <div class="px-5 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold uppercase {{ $badgeColor }}">
                                {{ \App\Support\EventTypes::label($type) }}
                            </span>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                                {{ $event->title }}
                            </div>
                        </div>

                        <div class="mt-2 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-y-1 gap-x-4 text-xs text-gray-500 dark:text-gray-400">
                            <!-- Team & Sport -->
                            <div class="flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <span class="truncate">
                                    {{ $event->team?->name ?? 'No Team' }}
                                    @if($event->team?->sport)
                                        <span class="text-gray-300 dark:text-gray-600">({{ $event->team->sport->name }})</span>
                                    @endif
                                </span>
                            </div>

                            <!-- DateTime -->
                            <div class="flex items-center gap-1">
                                <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>
                                    {{ $event->starts_at?->format('M j, Y g:i A') ?? 'N/A' }}
                                    -
                                    {{ $event->ends_at?->format('g:i A') ?? 'N/A' }}
                                </span>
                            </div>

                            <!-- Location -->
                            @if($event->location)
                                <div class="flex items-center gap-1">
                                    <svg class="h-3.5 w-3.5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="truncate">{{ $event->location }}</span>
                                </div>
                            @endif
                        </div>

                        @if($event->description)
                            <div class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                                {{ $event->description }}
                            </div>
                        @endif

                        <div class="mt-2 flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-2xs font-medium text-gray-600 dark:text-gray-400">
                                {{ $event->participants_count }} participant(s)
                            </span>
                            @if($event->creator)
                                <span class="text-2xs text-gray-400 dark:text-gray-500">
                                    Scheduled by {{ $event->creator->name }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-2 sm:self-center shrink-0">
                        <a href="{{ route('staff.events.edit', $event) }}" class="inline-flex items-center justify-center h-8.5 px-3 rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                            Edit
                        </a>
                        <form method="POST" action="{{ route('staff.events.destroy', $event) }}" onsubmit="return confirm('Are you sure you want to cancel this event?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center h-8.5 px-3 rounded-lg border border-transparent bg-red-50 dark:bg-red-950/20 text-xs font-semibold text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-950/40 transition">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="px-5 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">No events found</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by scheduling a new event.</p>
                </div>
            @endforelse
        </div>

        @if($events->hasPages())
            <div class="px-5 py-4 border-t border-gray-200 dark:border-white/10">
                {{ $events->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
