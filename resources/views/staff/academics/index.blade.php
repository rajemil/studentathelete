<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Team Academic Standing</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Monitor academic performance, attendance, and eligibility warnings for your team rosters.</div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <!-- Student Rosters -->
        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex justify-between items-center">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Roster Eligibility List</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 dark:bg-gray-800/40 text-xs text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Athlete</th>
                            <th class="px-6 py-3">GPA (Avg)</th>
                            <th class="px-6 py-3">Attendance Rate</th>
                            <th class="px-6 py-3">Standing</th>
                            <th class="px-6 py-3">Last Academic Record</th>
                            <th class="px-6 py-3">Latest Review Details</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/60 dark:divide-white/5">
                        @forelse($students as $student)
                            @php
                                $avgGpa = $student->academicRecords->avg('gpa');
                                $present = $student->attendanceRecords->whereIn('status', ['present', 'tardy'])->count();
                                $totalAtt = $student->attendanceRecords->count();
                                $attRate = $totalAtt > 0 ? round(($present / $totalAtt) * 100, 1) : null;
                                
                                $recentRecord = $student->academicRecords->sortByDesc('semester')->first();
                                $recentReview = $student->eligibilityReviews->sortByDesc('review_date')->first();
                                
                                $standing = 'good_standing';
                                if ($recentReview) {
                                    $standing = $recentReview->status;
                                } elseif ($recentRecord) {
                                    $standing = $recentRecord->status;
                                }
                            @endphp
                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $student->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $student->email }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($avgGpa !== null)
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ number_format($avgGpa, 2) }}</span>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($attRate !== null)
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $attRate }}%</span>
                                    @else
                                        <span class="text-gray-400">N/A</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($standing === 'eligible' || $standing === 'good_standing')
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/10 dark:bg-emerald-400/10 dark:text-emerald-400 dark:ring-emerald-500/20">Eligible</span>
                                    @elseif($standing === 'warning')
                                        <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-medium text-amber-700 ring-1 ring-inset ring-amber-600/10 dark:bg-amber-400/10 dark:text-amber-400 dark:ring-amber-500/20">Warning</span>
                                    @elseif($standing === 'probation')
                                        <span class="inline-flex items-center rounded-md bg-orange-50 px-2 py-1 text-xs font-medium text-orange-700 ring-1 ring-inset ring-orange-600/10 dark:bg-orange-400/10 dark:text-orange-400 dark:ring-orange-500/20">Probation</span>
                                    @elseif($standing === 'ineligible')
                                        <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 text-xs font-medium text-rose-700 ring-1 ring-inset ring-rose-600/10 dark:bg-rose-400/10 dark:text-rose-400 dark:ring-rose-500/20">Ineligible</span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-700 ring-1 ring-inset ring-gray-600/10 dark:bg-gray-400/10 dark:text-gray-400 dark:ring-gray-500/20">Pending</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($recentRecord)
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">GPA: {{ number_format($recentRecord->gpa, 2) }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $recentRecord->semester }} · {{ $recentRecord->credits_earned }} credits</div>
                                    @else
                                        <span class="text-gray-400">No records</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($recentReview)
                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ ucfirst($recentReview->status) }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 italic break-words">"{{ Str::limit($recentReview->comments, 60) }}"</div>
                                    @else
                                        <span class="text-gray-400">Never reviewed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-10 text-center text-gray-400">
                                    No student athletes assigned to your team rosters yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
