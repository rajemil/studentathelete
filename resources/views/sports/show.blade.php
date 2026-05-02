<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                Sport: {{ $sport->name }}
            </div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ actionOpen: false, actionTitle: '', actionSrc: '' }">
            <div class="space-y-3">
                @unless(request()->boolean('modal'))
                    <a href="{{ route('sports.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M15 18l-6-6 6-6"></path>
                        </svg>
                        Back to sports
                    </a>
                @endunless

                <div class="min-w-0">
                    <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100 break-words">
                        {{ $sport->name }}
                    </h2>
                    <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ $sport->students_count }} students assigned
                    </div>
                </div>
            </div>

            @if (session('status'))
                <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Action bar (kept layout) -->
            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 shadow-sm px-4 py-3">
                <div class="flex flex-wrap gap-2">
                    <a
                        href="{{ route('sports.scores.index', $sport) }}?modal=1"
                        @click.prevent="actionTitle = 'Enter scores'; actionSrc = $event.currentTarget.href; actionOpen = true"
                        class="inline-flex items-center rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 transition"
                    >
                        Enter scores
                    </a>
                    <a
                        href="{{ route('sports.rankings.index', $sport) }}?modal=1"
                        @click.prevent="actionTitle = 'Rankings'; actionSrc = $event.currentTarget.href; actionOpen = true"
                        class="inline-flex items-center rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 transition"
                    >
                        Rankings
                    </a>
                    <a
                        href="{{ route('sports.team_suggestions.index', $sport) }}?modal=1"
                        @click.prevent="actionTitle = 'Team suggestions'; actionSrc = $event.currentTarget.href; actionOpen = true"
                        class="inline-flex items-center rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 transition"
                    >
                        Team suggestions
                    </a>
                    <a
                        href="{{ route('sports.edit', $sport) }}?modal=1"
                        @click.prevent="actionTitle = 'Edit sport'; actionSrc = $event.currentTarget.href; actionOpen = true"
                        class="inline-flex items-center rounded-2xl bg-gradient-to-r from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-95 transition"
                    >
                        Edit
                    </a>
                </div>
            </div>

            <!-- Shared action modal (renders existing pages without navigation) -->
            <div x-show="actionOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60" @click="actionOpen = false"></div>
                <div class="relative w-full max-w-6xl rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                    <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex items-center justify-between shrink-0">
                        <div class="min-w-0">
                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100 truncate" x-text="actionTitle"></div>
                            <div class="text-sm text-gray-600 dark:text-gray-400 truncate">{{ $sport->name }}</div>
                        </div>
                        <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="actionOpen = false">Close</button>
                    </div>

                    <div class="flex-1 bg-white dark:bg-gray-950">
                        <iframe :src="actionSrc" class="w-full h-[80vh] border-0 bg-white dark:bg-gray-950"></iframe>
                    </div>
                </div>
            </div>

            @if($sport->description)
                <div class="dash-card rounded-3xl p-6">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">About</div>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $sport->description }}</div>
                </div>
            @endif

            <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                {{-- Assign student form --}}
                <div class="dash-card rounded-3xl p-6 lg:col-span-2">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Assign student</div>
                    <form method="POST" action="{{ route('sports.students.store', $sport) }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="user_id" value="Student" />
                            <select id="user_id" name="user_id" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-gray-100 focus:border-[#FF7A1A] focus:ring-[#FF7A1A] transition">
                                <option value="">Select a student...</option>
                                @foreach($availableStudents as $student)
                                    <option value="{{ $student->id }}" @selected(old('user_id') == $student->id)>
                                        {{ $student->name }} ({{ $student->email }})
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('user_id')" />
                        </div>

                        <button type="submit" class="inline-flex items-center rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm glow-border-orange hover:shadow-md transition">
                            Assign
                        </button>
                    </form>
                </div>

                {{-- Assigned students list --}}
                <div class="dash-card rounded-3xl overflow-hidden lg:col-span-3">
                    <div class="border-b border-gray-200/60 dark:border-white/10 px-6 py-5 flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Assigned students</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Manage who participates in this sport.</div>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-200/60 dark:divide-white/10">
                        @forelse($students as $student)
                            <div class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $student->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $student->email }}</div>
                                </div>
                                <form method="POST" action="{{ route('sports.students.destroy', [$sport, $student]) }}" onsubmit="return confirm('Remove this student from the sport?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium text-red-400 hover:text-red-300 transition">Remove</button>
                                </form>
                            </div>
                        @empty
                            <div class="px-6 py-10 text-sm text-gray-500 dark:text-gray-400 text-center">No students assigned yet.</div>
                        @endforelse
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200/60 dark:border-white/10">
                        {{ $students->links() }}
                    </div>
                </div>
            </div>
    </div>
</x-app-layout>
