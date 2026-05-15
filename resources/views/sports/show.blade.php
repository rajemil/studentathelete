<x-app-layout>
    <x-slot name="header">
        <div class="min-w-0">
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">
                Sport: {{ $sport->name }}
            </div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ 
        confirmOpen: false, confirmTitle: '', confirmMessage: '', confirmAction: '', confirmMethod: 'POST'
    }">
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
                        class="inline-flex items-center rounded-2xl bg-[#FF7A1A]/10 px-4 py-2 text-sm font-semibold text-[#FF7A1A] border border-[#FF7A1A]/20 hover:bg-[#FF7A1A]/20 transition"
                    >
                        Edit Requirements
                    </a>
                    @if(auth()->user()->role === 'admin')
                    <button
                        type="button"
                        @click="actionTitle = 'Assign Student'; actionSrc = '{{ route('sports.show', $sport) }}?assign=1&modal=1'; actionOpen = true"
                        class="inline-flex items-center rounded-2xl bg-gradient-to-r from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-95 transition"
                    >
                        Assign Student
                    </button>
                    @endif
                </div>
            </div>



            @if($sport->description)
                <div class="dash-card rounded-3xl p-6">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">About</div>
                    <div class="mt-2 text-sm text-gray-600 dark:text-gray-300 leading-relaxed">{{ $sport->description }}</div>
                </div>
            @endif

            @can('assignStudents', $sport)
                @if($pendingApplications->isNotEmpty())
                    <div id="pending-applications" class="dash-card rounded-3xl overflow-hidden">
                        <div class="border-b border-gray-200/60 dark:border-white/10 px-6 py-4">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Pending student applications</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">Review requests from the student dashboard. Eligibility is computed from profile + rules below.</div>
                        </div>
                        <div class="divide-y divide-gray-200/60 dark:divide-white/10">
                            @foreach($pendingApplications as $application)
                                <div class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-black/[0.02] transition">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-[#FF7A1A] font-bold text-xs overflow-hidden">
                                            @if($application->user->profile?->photo_path)
                                                <img src="{{ Storage::url($application->user->profile->photo_path) }}" alt="{{ $application->user->name }}" class="h-full w-full object-cover">
                                            @else
                                                {{ substr($application->user->name, 0, 1) }}
                                            @endif
                                        </div>
                                        <div>
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $application->user->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Applied {{ $application->created_at->diffForHumans() }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button
                                            type="button"
                                            @click="actionTitle = 'Review Application'; actionSrc = '{{ route('sports.applications.review', [$sport, $application]) }}?modal=1'; actionOpen = true"
                                            class="rounded-xl bg-gray-900 dark:bg-white/10 px-4 py-2 text-xs font-bold text-white hover:opacity-90 transition"
                                        >
                                            Review
                                        </button>
                                        <button
                                            type="button"
                                            @click="confirmTitle = 'Approve Student'; confirmMessage = 'Are you sure you want to approve and assign this student to the sport?'; confirmAction = '{{ route('sports.applications.approve', [$sport, $application]) }}'; confirmOpen = true"
                                            class="rounded-xl bg-[#FF7A1A] px-4 py-2 text-xs font-bold text-white shadow-sm hover:opacity-90 transition"
                                        >
                                            Approve
                                        </button>
                                        <button
                                            type="button"
                                            @click="confirmTitle = 'Reject Application'; confirmMessage = 'Are you sure you want to reject this application?'; confirmAction = '{{ route('sports.applications.reject', [$sport, $application]) }}'; confirmOpen = true"
                                            class="rounded-xl border border-gray-200 dark:border-white/15 px-4 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 hover:bg-black/5 transition"
                                        >
                                            Reject
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endcan

            <div class="grid grid-cols-1 gap-6">
                @if(request()->boolean('assign'))
                {{-- Assign student form (rendered in modal) --}}
                <div class="dash-card rounded-3xl p-6">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Assign student manually</div>
                    <form method="POST" action="{{ route('sports.students.store', $sport) }}" class="mt-4 space-y-4">
                        @csrf
                        <input type="hidden" name="modal" value="1">
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

                        <button type="submit" class="inline-flex items-center rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-6 py-2.5 text-sm font-semibold text-white shadow-sm glow-border-orange hover:shadow-md transition">
                            Assign to Sport
                        </button>
                    </form>
                </div>
                @endif

                @unless(request()->boolean('modal'))

                {{-- Assigned students list --}}
                <div class="dash-card rounded-3xl overflow-hidden col-span-1">
                    <div class="border-b border-gray-200/60 dark:border-white/10 px-6 py-5 flex items-center justify-between gap-4">
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Assigned students</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">Manage who participates in this sport.</div>
                        </div>
                    </div>
                    <div class="divide-y divide-gray-200/60 dark:divide-white/10">
                        @forelse($students as $student)
                            <div class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-black/[0.02] dark:hover:bg-white/[0.03] transition">
                                <div class="flex items-center gap-3">
                                    <div class="h-10 w-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center text-[#FF7A1A] font-bold text-xs overflow-hidden">
                                        @if($student->profile?->photo_path)
                                            <img src="{{ Storage::url($student->profile->photo_path) }}" alt="{{ $student->name }}" class="h-full w-full object-cover">
                                        @else
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        @endif
                                    </div>
                                    <div>
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $student->name }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $student->email }}</div>
                                    </div>
                                </div>
                                <button 
                                    type="button" 
                                    @click="confirmTitle = 'Remove Student'; confirmMessage = 'Are you sure you want to remove this student from the sport?'; confirmAction = '{{ route('sports.students.destroy', [$sport, $student]) }}'; confirmMethod = 'DELETE'; confirmOpen = true"
                                    class="text-sm font-medium text-red-400 hover:text-red-300 transition"
                                >
                                    Remove
                                </button>
                            </div>
                        @empty
                            <div class="px-6 py-10 text-sm text-gray-500 dark:text-gray-400 text-center">No students assigned yet.</div>
                        @endforelse
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200/60 dark:border-white/10">
                        {{ $students->links() }}
                    </div>
                </div>
                @endunless
            </div>

        {{-- Custom Confirmation Modal --}}
        <div
            x-show="confirmOpen"
            x-cloak
            class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            @keydown.escape.window="confirmOpen = false"
        >
            <div
                x-show="confirmOpen"
                x-transition
                class="w-full max-w-md rounded-3xl bg-white dark:bg-gray-900 shadow-2xl border border-gray-200 dark:border-white/10 overflow-hidden"
                @click.outside="confirmOpen = false"
            >
                <div class="p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100" x-text="confirmTitle"></h3>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400" x-text="confirmMessage"></p>
                    
                    <div class="mt-6 flex items-center justify-end gap-3">
                        <button
                            type="button"
                            @click="confirmOpen = false"
                            class="rounded-2xl px-4 py-2 text-sm font-semibold text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 transition"
                        >
                            Cancel
                        </button>
                        <form :action="confirmAction" method="POST">
                            @csrf
                            <input type="hidden" name="_method" :value="confirmMethod">
                            <button
                                type="submit"
                                class="rounded-2xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-6 py-2 text-sm font-bold text-white shadow-lg shadow-orange-500/20 hover:scale-[1.02] transition"
                            >
                                Confirm Action
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
