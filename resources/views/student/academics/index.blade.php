<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">My Academic & Eligibility Status</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">View your personal GPAs, class attendance, and official eligibility reviews.</div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Personal Summary Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 shadow-sm p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Cumulative GPA</div>
                <div class="mt-2 flex items-baseline gap-2">
                    @php
                        $avgGpa = $academicRecords->avg('gpa');
                    @endphp
                    <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $avgGpa !== null ? number_format($avgGpa, 2) : 'N/A' }}
                    </div>
                    <div class="text-sm text-gray-500">/ 4.00</div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 shadow-sm p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Overall Attendance</div>
                <div class="mt-2 flex items-baseline gap-2">
                    @php
                        $present = $attendanceRecords->whereIn('status', ['present', 'tardy'])->count();
                        $total = $attendanceRecords->count();
                        $rate = $total > 0 ? round(($present / $total) * 100, 1) : null;
                    @endphp
                    <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $rate !== null ? $rate . '%' : 'N/A' }}
                    </div>
                    <div class="text-sm text-gray-500">required: > 80%</div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 shadow-sm p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Eligibility Status</div>
                <div class="mt-2 flex items-baseline gap-2">
                    @php
                        $latestReview = $eligibilityReviews->sortByDesc('review_date')->first();
                        $latestRecord = $academicRecords->sortByDesc('semester')->first();
                        $status = 'pending';
                        if ($latestReview) {
                            $status = $latestReview->status;
                        } elseif ($latestRecord) {
                            $status = $latestRecord->status;
                        }
                    @endphp
                    <div class="text-2xl font-bold tracking-wider uppercase">
                        @if($status === 'eligible' || $status === 'good_standing')
                            <span class="text-emerald-600 dark:text-emerald-400">Eligible</span>
                        @elseif($status === 'warning')
                            <span class="text-amber-500 dark:text-amber-400">Warning</span>
                        @elseif($status === 'probation')
                            <span class="text-orange-500 dark:text-orange-400">Probation</span>
                        @elseif($status === 'ineligible')
                            <span class="text-rose-600 dark:text-rose-400">Ineligible</span>
                        @else
                            <span class="text-gray-500 dark:text-gray-400">Pending</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
            <!-- GPA Records -->
            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Semester GPA Records</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-50 dark:bg-gray-800/40 text-xs text-gray-700 dark:text-gray-300 uppercase">
                            <tr>
                                <th class="px-6 py-3">Semester</th>
                                <th class="px-6 py-3">GPA</th>
                                <th class="px-6 py-3">Credits Earned</th>
                                <th class="px-6 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200/60 dark:divide-white/5">
                            @forelse($academicRecords as $record)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $record->semester }}</td>
                                    <td class="px-6 py-4 font-semibold text-gray-900 dark:text-gray-100">{{ number_format($record->gpa, 2) }}</td>
                                    <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $record->credits_earned }}</td>
                                    <td class="px-6 py-4">
                                        @if($record->status === 'good_standing')
                                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-500/20">Good Standing</span>
                                        @elseif($record->status === 'warning')
                                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/10 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-500/20">Warning</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-600/10 dark:bg-rose-400/10 dark:text-rose-400 dark:ring-rose-500/20">Ineligible</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-gray-400">No semester GPA records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Attendance Records -->
            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex justify-between items-center">
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Class Attendance Log</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                        <thead class="bg-gray-50 dark:bg-gray-800/40 text-xs text-gray-700 dark:text-gray-300 uppercase">
                            <tr>
                                <th class="px-6 py-3">Course</th>
                                <th class="px-6 py-3">Date</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Notes</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200/60 dark:divide-white/5">
                            @forelse($attendanceRecords as $att)
                                <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                    <td class="px-6 py-4 font-medium text-gray-900 dark:text-gray-100">{{ $att->course_name }}</td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ $att->date->toDateString() }}</td>
                                    <td class="px-6 py-4">
                                        @if($att->status === 'present')
                                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-500/20">Present</span>
                                        @elseif($att->status === 'tardy')
                                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/10 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-500/20">Tardy</span>
                                        @elseif($att->status === 'excused')
                                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-600/10 dark:bg-indigo-400/10 dark:text-indigo-400 dark:ring-indigo-500/20">Excused</span>
                                        @else
                                            <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-600/10 dark:bg-rose-400/10 dark:text-rose-400 dark:ring-rose-500/20">Absent</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-gray-500 dark:text-gray-400 italic">{{ Str::limit($att->notes, 40) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-6 text-center text-gray-400">No attendance logs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Eligibility Reviews -->
        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex justify-between items-center">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Official Eligibility Reviews</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 dark:bg-gray-800/40 text-xs text-gray-700 dark:text-gray-300 uppercase">
                        <tr>
                            <th class="px-6 py-3">Review Date</th>
                            <th class="px-6 py-3">Reviewer</th>
                            <th class="px-6 py-3">Decision</th>
                            <th class="px-6 py-3">Reviewer Comments</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/60 dark:divide-white/5">
                        @forelse($eligibilityReviews as $review)
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                <td class="px-6 py-4 text-gray-950 dark:text-white font-medium">{{ $review->review_date->toDateString() }}</td>
                                <td class="px-6 py-4 text-gray-700 dark:text-gray-300">{{ $review->reviewer?->name ?? 'System' }}</td>
                                <td class="px-6 py-4">
                                    @if($review->status === 'eligible')
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-500/20">Eligible</span>
                                    @elseif($review->status === 'probation')
                                        <span class="inline-flex items-center rounded-md bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/10 dark:bg-orange-400/10 dark:text-orange-400 dark:ring-orange-500/20">Probation</span>
                                    @elseif($review->status === 'ineligible')
                                        <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-600/10 dark:bg-rose-400/10 dark:text-rose-400 dark:ring-rose-500/20">Ineligible</span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-600/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-500/20">Pending</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-gray-900 dark:text-gray-100 font-medium break-words">{{ $review->comments }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-6 text-center text-gray-400">No official reviews have been recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
