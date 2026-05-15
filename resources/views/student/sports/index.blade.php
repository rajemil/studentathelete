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

                    {{-- Sport Rules / Requirements Display --}}
                    @if($sport->qual_min_age || $sport->qual_max_age || $sport->qual_min_height_cm || !empty($sport->qual_allowed_genders))
                        <div class="mt-4 flex flex-wrap gap-2">
                            @if($sport->qual_min_age || $sport->qual_max_age)
                                <span class="inline-flex items-center gap-1 rounded-md bg-gray-50 dark:bg-white/5 px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 ring-1 ring-inset ring-gray-500/10 dark:ring-white/10">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    @if($sport->qual_min_age && $sport->qual_max_age)
                                        Age: {{ $sport->qual_min_age }} - {{ $sport->qual_max_age }}
                                    @elseif($sport->qual_min_age)
                                        Age: {{ $sport->qual_min_age }}+
                                    @else
                                        Age: Up to {{ $sport->qual_max_age }}
                                    @endif
                                </span>
                            @endif
                            @if($sport->qual_min_height_cm)
                                <span class="inline-flex items-center gap-1 rounded-md bg-gray-50 dark:bg-white/5 px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 ring-1 ring-inset ring-gray-500/10 dark:ring-white/10">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4h13M3 8h9m-9 4h6m4 0l4-4m0 0l4 4m-4-4v12"></path></svg>
                                    Min height: {{ $sport->qual_min_height_cm }}cm
                                </span>
                            @endif
                            @if(!empty($sport->qual_allowed_genders))
                                <span class="inline-flex items-center gap-1 rounded-md bg-gray-50 dark:bg-white/5 px-2 py-1 text-xs font-medium text-gray-600 dark:text-gray-300 ring-1 ring-inset ring-gray-500/10 dark:ring-white/10">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    {{ implode(', ', array_map('ucfirst', $sport->qual_allowed_genders)) }}
                                </span>
                            @endif
                        </div>
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
                                <form method="POST" action="{{ route('student.sports.apply', $sport) }}" enctype="multipart/form-data" class="mt-3 space-y-2 rounded-xl border border-gray-200/60 dark:border-white/10 p-3">
                                    @csrf

                                    @if($sport->require_report_card)
                                        <div x-data="{ fileName: '', previewUrl: '' }" class="space-y-1">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Report card / Grades (Photo/PDF)</label>
                                            <div class="relative flex items-center justify-center w-full">
                                                <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 dark:hover:bg-white/5 dark:bg-gray-800/50 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 overflow-hidden transition relative">
                                                    <div class="flex flex-col items-center justify-center pt-5 pb-6 z-10" x-show="!previewUrl && !fileName">
                                                        <svg class="w-6 h-6 mb-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                                        <p class="mb-1 text-xs text-gray-500 dark:text-gray-400"><span class="font-semibold text-[#FF7A1A]">Click to upload</span></p>
                                                    </div>
                                                    <template x-if="previewUrl">
                                                        <img :src="previewUrl" class="absolute inset-0 w-full h-full object-cover opacity-50 dark:opacity-70 mix-blend-overlay" />
                                                    </template>
                                                    <template x-if="fileName">
                                                        <div class="absolute inset-0 flex items-center justify-center p-2 z-20">
                                                            <div class="flex items-center gap-2 text-xs font-semibold text-gray-900 dark:text-gray-100 bg-white/90 dark:bg-gray-900/90 shadow-sm border border-gray-200 dark:border-white/10 px-3 py-1.5 rounded-lg truncate max-w-full">
                                                                <svg class="w-4 h-4 text-[#FF7A1A] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                <span x-text="fileName" class="truncate"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <input type="file" name="report_card" accept="image/*,.pdf" class="hidden" required @change="
                                                        const file = $event.target.files[0];
                                                        if (file) {
                                                            fileName = file.name;
                                                            if (file.type.startsWith('image/')) {
                                                                previewUrl = URL.createObjectURL(file);
                                                            } else {
                                                                previewUrl = '';
                                                            }
                                                        } else {
                                                            fileName = '';
                                                            previewUrl = '';
                                                        }
                                                    " />
                                                </label>
                                            </div>
                                        </div>
                                    @endif

                                    @if($sport->require_medical_form)
                                        <div x-data="{ fileName: '', previewUrl: '' }" class="space-y-1">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Medical certificate / Other forms</label>
                                            <div class="relative flex items-center justify-center w-full">
                                                <label class="flex flex-col items-center justify-center w-full h-24 border-2 border-gray-300 border-dashed rounded-xl cursor-pointer bg-gray-50 dark:hover:bg-white/5 dark:bg-gray-800/50 hover:bg-gray-100 dark:border-gray-600 dark:hover:border-gray-500 overflow-hidden transition relative">
                                                    <div class="flex flex-col items-center justify-center pt-5 pb-6 z-10" x-show="!previewUrl && !fileName">
                                                        <svg class="w-6 h-6 mb-2 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                                        <p class="mb-1 text-xs text-gray-500 dark:text-gray-400"><span class="font-semibold text-[#FF7A1A]">Click to upload</span></p>
                                                    </div>
                                                    <template x-if="previewUrl">
                                                        <img :src="previewUrl" class="absolute inset-0 w-full h-full object-cover opacity-50 dark:opacity-70 mix-blend-overlay" />
                                                    </template>
                                                    <template x-if="fileName">
                                                        <div class="absolute inset-0 flex items-center justify-center p-2 z-20">
                                                            <div class="flex items-center gap-2 text-xs font-semibold text-gray-900 dark:text-gray-100 bg-white/90 dark:bg-gray-900/90 shadow-sm border border-gray-200 dark:border-white/10 px-3 py-1.5 rounded-lg truncate max-w-full">
                                                                <svg class="w-4 h-4 text-[#FF7A1A] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                                                <span x-text="fileName" class="truncate"></span>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <input type="file" name="medical_form" accept="image/*,.pdf" class="hidden" required @change="
                                                        const file = $event.target.files[0];
                                                        if (file) {
                                                            fileName = file.name;
                                                            if (file.type.startsWith('image/')) {
                                                                previewUrl = URL.createObjectURL(file);
                                                            } else {
                                                                previewUrl = '';
                                                            }
                                                        } else {
                                                            fileName = '';
                                                            previewUrl = '';
                                                        }
                                                    " />
                                                </label>
                                            </div>
                                        </div>
                                    @endif

                                    @if($sport->require_bp || $sport->require_heart_rate)
                                        <div class="grid grid-cols-2 gap-3 pt-2">
                                            @if($sport->require_bp)
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Blood Pressure</label>
                                                    <input type="text" name="medical_bp" placeholder="e.g. 120/80" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-gray-100 text-sm focus:border-[#FF7A1A] focus:ring-[#FF7A1A] transition" required>
                                                </div>
                                            @endif
                                            @if($sport->require_heart_rate)
                                                <div>
                                                    <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Heart Rate (bpm)</label>
                                                    <input type="number" name="medical_heart_rate" placeholder="e.g. 72" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-gray-100 text-sm focus:border-[#FF7A1A] focus:ring-[#FF7A1A] transition" required>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    @if($sport->require_allergies)
                                        <div class="pt-2">
                                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Allergies (if any)</label>
                                            <textarea name="medical_allergies" rows="2" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-gray-100 text-sm focus:border-[#FF7A1A] focus:ring-[#FF7A1A] transition" placeholder="List any allergies…"></textarea>
                                        </div>
                                    @endif

                                    <div class="pt-2">
                                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Message to staff (optional)</label>
                                        <textarea name="student_message" rows="2" maxlength="1000" class="mt-1 w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-gray-100 text-sm focus:border-[#FF7A1A] focus:ring-[#FF7A1A] transition" placeholder="Why you want to join…"></textarea>
                                    </div>
                                    <div class="pt-2">
                                        <button type="submit" class="w-full rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-3 py-2.5 text-sm font-semibold text-white shadow-sm glow-border-orange hover:shadow-md hover:scale-[1.01] transition-all">Submit application</button>
                                    </div>
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
