<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">{{ $sport->name }}</h2>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $sport->students_count }} students assigned
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('sports.scores.index', $sport) }}" class="inline-flex items-center rounded-xl border border-gray-200/60 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 transition">
                    Enter scores
                </a>
                <a href="{{ route('sports.rankings.index', $sport) }}" class="inline-flex items-center rounded-xl border border-gray-200/60 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 transition">
                    Rankings
                </a>
                <a href="{{ route('sports.team_suggestions.index', $sport) }}" class="inline-flex items-center rounded-xl border border-gray-200/60 dark:border-white/10 bg-white/70 dark:bg-white/5 px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-white dark:hover:bg-white/10 transition">
                    Team suggestions
                </a>
                <a href="{{ route('sports.edit', $sport) }}" class="inline-flex items-center rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm glow-border-orange hover:shadow-md transition">
                    Edit
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
                    {{ session('status') }}
                </div>
            @endif

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
    </div>
</x-app-layout>
