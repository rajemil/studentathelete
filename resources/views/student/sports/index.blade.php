<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Sports &amp; activities</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Apply to sports you want. Staff are notified and can approve you after reviewing eligibility.</div>
        </div>
    </x-slot>

    <div class="py-6 space-y-4">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($sports as $sport)
                @php
                    $isMember = $joinedIds->contains($sport->id);
                    $app = $applicationsBySportId->get($sport->id);
                @endphp
                <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-5 shadow-sm flex flex-col">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $sport->name }}</div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $sport->slug }}</div>
                        </div>
                        @if($isMember)
                            <span class="text-xs font-semibold rounded-full bg-emerald-500/15 text-emerald-700 dark:text-emerald-300 border border-emerald-500/25 px-2 py-0.5">Member</span>
                        @elseif($app && $app->status === 'pending')
                            <span class="text-xs font-semibold rounded-full bg-amber-500/15 text-amber-800 dark:text-amber-200 border border-amber-500/25 px-2 py-0.5">Pending</span>
                        @elseif($app && $app->status === 'rejected')
                            <span class="text-xs font-semibold rounded-full bg-red-500/15 text-red-700 dark:text-red-300 border border-red-500/25 px-2 py-0.5">Rejected</span>
                        @elseif($app && $app->status === 'withdrawn')
                            <span class="text-xs font-semibold rounded-full bg-gray-500/15 text-gray-700 dark:text-gray-300 border border-gray-500/25 px-2 py-0.5">Withdrawn</span>
                        @endif
                    </div>
                    @if($sport->description)
                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-300 line-clamp-3 flex-1">{{ $sport->description }}</p>
                    @endif
                    <div class="mt-4 text-xs text-gray-500 dark:text-gray-400">{{ $sport->students_count }} students</div>

                    @if($app && $app->status === 'pending' && is_array($app->qualification_detail))
                        <div class="mt-3 rounded-xl border border-gray-200/60 dark:border-white/10 bg-gray-50/80 dark:bg-white/5 px-3 py-2 text-xs text-gray-700 dark:text-gray-300 space-y-1">
                            <div class="font-semibold text-gray-900 dark:text-gray-100">Eligibility check</div>
                            <ul class="list-disc pl-4 space-y-0.5">
                                @foreach($app->qualification_detail as $line)
                                    <li>{{ $line }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if($isMember)
                            <form method="POST" action="{{ route('student.sports.leave', $sport) }}" onsubmit="return confirm('Leave this sport?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-xl border border-gray-200 dark:border-white/15 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                    Leave
                                </button>
                            </form>
                            <a href="{{ route('student.dashboard') }}#sport-activity-summary" class="inline-flex items-center rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-3 py-2 text-sm font-semibold text-white shadow-sm">View stats</a>
                        @elseif($app && $app->status === 'pending')
                            <form method="POST" action="{{ route('student.sports.withdraw', $sport) }}" onsubmit="return confirm('Withdraw your application?');">
                                @csrf
                                <button type="submit" class="rounded-xl border border-gray-200 dark:border-white/15 px-3 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                                    Withdraw application
                                </button>
                            </form>
                        @else
                            <details class="group flex-1 min-w-[200px]">
                                <summary class="cursor-pointer list-none inline-flex items-center rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-3 py-2 text-sm font-semibold text-white shadow-sm glow-border-orange [&::-webkit-details-marker]:hidden">
                                    <span>Apply to this sport</span>
                                </summary>
                                <form method="POST" action="{{ route('student.sports.apply', $sport) }}" class="mt-3 space-y-2 rounded-xl border border-gray-200/60 dark:border-white/10 p-3">
                                    @csrf
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400">Message to staff (optional)</label>
                                    <textarea name="student_message" rows="2" maxlength="1000" class="w-full rounded-lg border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-gray-100 text-sm" placeholder="Why you want to join…"></textarea>
                                    <button type="submit" class="w-full rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-3 py-2 text-sm font-semibold text-white shadow-sm">Submit application</button>
                                </form>
                            </details>
                        @endif
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 p-8 text-sm text-gray-600 dark:text-gray-400 sm:col-span-2 lg:col-span-3 text-center">
                    No sports are available yet.
                </div>
            @endforelse
        </div>

        <div>
            {{ $sports->links() }}
        </div>
    </div>
</x-app-layout>
