<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Academic & Eligibility Management</h2>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Track student-athlete GPAs, class attendance, and manage official eligibility reviews.</div>
            </div>
            <div>
                <a href="{{ route('admin.academics.export') }}" class="inline-flex items-center gap-1.5 rounded-xl border border-gray-200 dark:border-white/15 bg-white dark:bg-gray-850 px-4 py-2 text-sm font-semibold text-gray-800 dark:text-gray-100 hover:bg-gray-50 dark:hover:bg-gray-800 transition">
                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Export Records
                </a>
            </div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{
        recordModalOpen: false,
        attendanceModalOpen: false,
        reviewModalOpen: false,
        selectedUserId: '',
        selectedUserName: '',
        openRecordModal(id, name) {
            this.selectedUserId = id;
            this.selectedUserName = name;
            this.recordModalOpen = true;
        },
        openAttendanceModal(id, name) {
            this.selectedUserId = id;
            this.selectedUserName = name;
            this.attendanceModalOpen = true;
        },
        openReviewModal(id, name) {
            this.selectedUserId = id;
            this.selectedUserName = name;
            this.reviewModalOpen = true;
        }
    }">
        @if (session('success'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('success') }}
            </div>
        @endif

        <!-- KPI Summary Cards -->
        <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 shadow-sm p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Average Roster GPA</div>
                <div class="mt-2 flex items-baseline gap-2">
                    <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($averageGpa, 2) }}</div>
                    <div class="text-sm text-gray-500">/ 4.00</div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 shadow-sm p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Academic Warnings</div>
                <div class="mt-2 flex items-baseline gap-2">
                    <div class="text-3xl font-semibold text-amber-600 dark:text-amber-400">{{ $warningsCount }}</div>
                    <div class="text-sm text-gray-500">athletes at risk</div>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 shadow-sm p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Ineligible Athletes</div>
                <div class="mt-2 flex items-baseline gap-2">
                    <div class="text-3xl font-semibold text-rose-600 dark:text-rose-400">{{ $ineligibleCount }}</div>
                    <div class="text-sm text-gray-500">currently benched</div>
                </div>
            </div>
        </div>

        <!-- Student Rosters -->
        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white dark:bg-gray-900 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex justify-between items-center">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Student Athlete Standings</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-500 dark:text-gray-400">
                    <thead class="bg-gray-50 dark:bg-gray-800/40 text-xs text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                        <tr>
                            <th class="px-6 py-3">Athlete</th>
                            <th class="px-6 py-3">GPA (Avg)</th>
                            <th class="px-6 py-3">Attendance Rate</th>
                            <th class="px-6 py-3">Standing</th>
                            <th class="px-6 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200/60 dark:divide-white/5">
                        @foreach($students as $student)
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
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" @click="openRecordModal('{{ $student->id }}', '{{ $student->name }}')" class="inline-flex items-center rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-2.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/10 transition">
                                            Log GPA
                                        </button>
                                        <button type="button" @click="openAttendanceModal('{{ $student->id }}', '{{ $student->name }}')" class="inline-flex items-center rounded-lg border border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 px-2.5 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/10 transition">
                                            Log Attendance
                                        </button>
                                        <button type="button" @click="openReviewModal('{{ $student->id }}', '{{ $student->name }}')" class="inline-flex items-center rounded-lg bg-indigo-600 px-2.5 py-1.5 text-xs font-semibold text-white hover:bg-indigo-500 transition">
                                            Review Eligibility
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add GPA Record Modal -->
        <div x-show="recordModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" @click="recordModalOpen = false"></div>
            <div class="relative w-full max-w-md rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl p-6">
                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Log GPA for <span x-text="selectedUserName"></span></div>
                <form method="POST" action="{{ route('admin.academics.records.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="user_id" :value="selectedUserId">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Semester</label>
                        <input type="text" name="semester" placeholder="e.g. Fall 2026" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-transparent dark:text-white" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">GPA (0.00 - 4.00)</label>
                        <input type="number" step="0.01" name="gpa" min="0" max="4.00" placeholder="e.g. 3.45" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-transparent dark:text-white" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Credits Earned</label>
                        <input type="number" name="credits_earned" placeholder="e.g. 15" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-transparent dark:text-white" required>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="recordModalOpen = false" class="rounded-lg border border-gray-300 dark:border-white/10 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/10">Cancel</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Attendance Modal -->
        <div x-show="attendanceModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" @click="attendanceModalOpen = false"></div>
            <div class="relative w-full max-w-md rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl p-6">
                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Log Attendance for <span x-text="selectedUserName"></span></div>
                <form method="POST" action="{{ route('admin.academics.attendance.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="user_id" :value="selectedUserId">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Course Name</label>
                        <input type="text" name="course_name" placeholder="e.g. Calculus I" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-transparent dark:text-white" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Date</label>
                        <input type="date" name="date" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-transparent dark:text-white" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" required>
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="excused">Excused</option>
                            <option value="tardy">Tardy</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Notes (Optional)</label>
                        <textarea name="notes" placeholder="e.g. Excused for game travel" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-transparent dark:text-white"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="attendanceModalOpen = false" class="rounded-lg border border-gray-300 dark:border-white/10 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/10">Cancel</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Save</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Review Modal -->
        <div x-show="reviewModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" @click="reviewModalOpen = false"></div>
            <div class="relative w-full max-w-md rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl p-6">
                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Review Eligibility for <span x-text="selectedUserName"></span></div>
                <form method="POST" action="{{ route('admin.academics.reviews.store') }}" class="mt-4 space-y-4">
                    @csrf
                    <input type="hidden" name="user_id" :value="selectedUserId">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Review Date</label>
                        <input type="date" name="review_date" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-transparent dark:text-white" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
                        <select name="status" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-white dark:bg-gray-800 text-gray-900 dark:text-white" required>
                            <option value="eligible">Eligible</option>
                            <option value="ineligible">Ineligible</option>
                            <option value="probation">Probation</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Comments</label>
                        <textarea name="comments" placeholder="Include official decision reasoning" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-white/10 bg-transparent dark:text-white" required></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="reviewModalOpen = false" class="rounded-lg border border-gray-300 dark:border-white/10 px-4 py-2 text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/10">Cancel</button>
                        <button type="submit" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Submit Review</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
