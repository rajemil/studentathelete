<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Student Athletes</h2>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">View and approve student athletes in your organization.</div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4" x-data="{
        viewOpen: false,
        viewStudent: {},
        calcAge(birthdate) {
            if (!birthdate) return '—';
            const d = new Date(birthdate);
            if (String(d) === 'Invalid Date') return '—';
            const now = new Date();
            let age = now.getFullYear() - d.getFullYear();
            const m = now.getMonth() - d.getMonth();
            if (m < 0 || (m === 0 && now.getDate() < d.getDate())) age--;
            return age < 0 ? '—' : age;
        },
        openView(student) {
            this.viewStudent = student;
            this.viewOpen = true;
        }
    }">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <a href="{{ route('staff.students.index', ['status' => 'all', 'search' => request('search')]) }}"
                   class="px-4 py-2 text-sm font-medium rounded-full {{ $status === 'all' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' }}">
                    All
                </a>
                <a href="{{ route('staff.students.index', ['status' => 'pending', 'search' => request('search')]) }}"
                   class="px-4 py-2 text-sm font-medium rounded-full {{ $status === 'pending' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' }}">
                    Pending
                </a>
                <a href="{{ route('staff.students.index', ['status' => 'approved', 'search' => request('search')]) }}"
                   class="px-4 py-2 text-sm font-medium rounded-full {{ $status === 'approved' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' }}">
                    Approved
                </a>
                <a href="{{ route('staff.students.index', ['status' => 'rejected', 'search' => request('search')]) }}"
                   class="px-4 py-2 text-sm font-medium rounded-full {{ $status === 'rejected' ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-white/10' }}">
                    Rejected
                </a>
            </div>
            
            <form method="GET" action="{{ route('staff.students.index') }}" class="w-full sm:w-64">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search students..." class="w-full rounded-xl border border-gray-300 pl-10 pr-4 py-2 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white" />
                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                </div>
            </form>
        </div>

        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                    <thead class="bg-gray-50/70 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Academic Details</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Sports</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($students as $stu)
                            @php
                                $p = $stu->profile;
                                $photo = $p?->photo_path ? '/storage/'.$p->photo_path : null;
                                $payload = [
                                    'id' => $stu->id,
                                    'name' => $stu->name,
                                    'email' => $stu->email,
                                    'course' => $p?->course?->name,
                                    'year_level' => $p?->yearLevel?->name,
                                    'section' => $p?->section?->name,
                                    'birthdate' => optional($p?->birthdate)->format('M d, Y') ?? '',
                                    'raw_birthdate' => optional($p?->birthdate)->format('Y-m-d') ?? '',
                                    'gender' => $p ? ucfirst(str_replace('_', ' ', $p->gender)) : '',
                                    'address' => $p?->address,
                                    'height' => $p?->height_cm,
                                    'weight' => $p?->weight_kg,
                                    'sports' => $stu->sports->pluck('name')->all(),
                                    'photo' => $photo,
                                ];
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 shrink-0 rounded-2xl bg-gray-100 dark:bg-white/10 bg-cover bg-center ring-1 ring-black/5 dark:ring-white/10" @if($photo) style="background-image: url('{{ $photo }}')" @endif></div>
                                        <div>
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $stu->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $stu->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="text-sm text-gray-700 dark:text-gray-300">{{ $p?->course?->name ?? '—' }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $p?->yearLevel?->name ?? '—' }} &bull; {{ $p?->section?->name ?? '—' }}</div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $stu->sports->pluck('name')->join(', ') ?: '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if($stu->approval_status === 'approved')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/20">Approved</span>
                                    @elseif($stu->approval_status === 'pending')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-yellow-50 px-2 py-1 text-xs font-semibold text-yellow-700 ring-1 ring-inset ring-yellow-600/20 dark:bg-yellow-500/10 dark:text-yellow-400 dark:ring-yellow-500/20">Pending</span>
                                    @elseif($stu->approval_status === 'rejected')
                                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-1 text-xs font-semibold text-red-700 ring-1 ring-inset ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20">Rejected</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-right space-x-2">
                                    <button type="button" class="text-sm font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition" @click="openView(@js($payload))">View</button>
                                    @if($stu->approval_status === 'pending')
                                        <form method="POST" action="{{ route('staff.students.approve', $stu) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 transition">Approve</button>
                                        </form>
                                        <form method="POST" action="{{ route('staff.students.reject', $stu) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="text-sm font-semibold text-red-600 hover:text-red-700 dark:text-red-400 transition">Reject</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-600 dark:text-gray-400">No students found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200/60 dark:border-white/10">
                {{ $students->links() }}
            </div>
        </div>

        {{-- View Student Modal --}}
        <div x-show="viewOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" @click="viewOpen = false"></div>
            <div class="relative w-full max-w-2xl rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex justify-between shrink-0">
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Student Details</div>
                    <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="viewOpen = false">Close</button>
                </div>
                <div class="p-6 space-y-6 overflow-y-auto">
                    <div class="flex items-center gap-4 border-b border-gray-200/60 dark:border-white/10 pb-6">
                        <div class="h-20 w-20 shrink-0 rounded-2xl bg-gray-100 dark:bg-white/10 bg-cover bg-center ring-1 ring-black/5 dark:ring-white/10" x-bind:style="viewStudent.photo ? `background-image: url('${viewStudent.photo}')` : ''"></div>
                        <div>
                            <div class="text-xl font-semibold text-gray-900 dark:text-gray-100" x-text="viewStudent.name"></div>
                            <div class="text-sm text-gray-500 dark:text-gray-400" x-text="viewStudent.email"></div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Course / Program</div>
                            <div class="mt-1 font-medium text-gray-900 dark:text-gray-100" x-text="viewStudent.course || '—'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Year Level & Section</div>
                            <div class="mt-1 font-medium text-gray-900 dark:text-gray-100">
                                <span x-text="viewStudent.year_level || '—'"></span> &bull; <span x-text="viewStudent.section || '—'"></span>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Date of Birth & Age</div>
                            <div class="mt-1 font-medium text-gray-900 dark:text-gray-100">
                                <span x-text="viewStudent.birthdate || '—'"></span>
                                <span class="text-gray-500 dark:text-gray-400" x-text="viewStudent.raw_birthdate ? `(${calcAge(viewStudent.raw_birthdate)} yrs)` : ''"></span>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Gender</div>
                            <div class="mt-1 font-medium text-gray-900 dark:text-gray-100" x-text="viewStudent.gender || '—'"></div>
                        </div>
                        <div>
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Height / Weight</div>
                            <div class="mt-1 font-medium text-gray-900 dark:text-gray-100">
                                <span x-text="viewStudent.height ? viewStudent.height + ' cm' : '—'"></span> / 
                                <span x-text="viewStudent.weight ? viewStudent.weight + ' kg' : '—'"></span>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Address</div>
                            <div class="mt-1 font-medium text-gray-900 dark:text-gray-100" x-text="viewStudent.address || '—'"></div>
                        </div>
                        <div class="sm:col-span-2">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sports Interested</div>
                            <div class="mt-1 font-medium text-gray-900 dark:text-gray-100" x-text="viewStudent.sports ? viewStudent.sports.join(', ') : '—'"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
