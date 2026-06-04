<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Edit Event</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Update event details, times, or location.</div>
        </div>
    </x-slot>

    <div class="max-w-3xl rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-6 shadow-sm">
        <form method="POST" action="{{ route('staff.events.update', $event) }}" class="space-y-6">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Title -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Event Title</label>
                    <input type="text" name="title" value="{{ old('title', $event->title) }}" required class="mt-1 block w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm focus:border-orange-500 focus:ring-orange-500" />
                    @error('title') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <!-- Event Type -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Event Type</label>
                    <select name="event_type" required class="mt-1 block w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm focus:border-orange-500 focus:ring-orange-500">
                        @foreach(\App\Support\EventTypes::labels() as $k => $v)
                            <option value="{{ $k }}" @selected(old('event_type', $event->event_type) === $k)>{{ $v }}</option>
                        @endforeach
                    </select>
                    @error('event_type') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <!-- Target Team -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Team</label>
                    <select name="team_id" required class="mt-1 block w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm focus:border-orange-500 focus:ring-orange-500">
                        @foreach($teams as $team)
                            <option value="{{ $team->id }}" @selected(old('team_id', $event->team_id) == $team->id)>{{ $team->name }}</option>
                        @endforeach
                    </select>
                    <div class="mt-1 text-2xs text-gray-500 dark:text-gray-400">If the team is changed, the roster of participants will be automatically updated.</div>
                    @error('team_id') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <!-- Starts At -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Starts At</label>
                    <input type="datetime-local" name="starts_at" value="{{ old('starts_at', $event->starts_at?->format('Y-m-d\TH:i')) }}" required class="mt-1 block w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm focus:border-orange-500 focus:ring-orange-500" />
                    @error('starts_at') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <!-- Ends At -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Ends At</label>
                    <input type="datetime-local" name="ends_at" value="{{ old('ends_at', $event->ends_at?->format('Y-m-d\TH:i')) }}" required class="mt-1 block w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm focus:border-orange-500 focus:ring-orange-500" />
                    @error('ends_at') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <!-- Location -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Location</label>
                    <input type="text" name="location" value="{{ old('location', $event->location) }}" class="mt-1 block w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm focus:border-orange-500 focus:ring-orange-500" />
                    @error('location') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>

                <!-- Description -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-200">Description (optional)</label>
                    <textarea name="description" rows="4" class="mt-1 block w-full rounded-xl border-gray-200 dark:border-white/10 bg-white/90 dark:bg-gray-950/40 text-sm focus:border-orange-500 focus:ring-orange-500">{{ old('description', $event->description) }}</textarea>
                    @error('description') <div class="mt-1 text-xs text-red-600">{{ $message }}</div> @enderror
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-white/10">
                <a href="{{ route('staff.events.index') }}" class="inline-flex justify-center rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-gray-800 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                    Cancel
                </a>
                <button type="submit" class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-90 transition">
                    Update Event
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
