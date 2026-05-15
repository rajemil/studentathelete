<x-app-layout>
    <div class="p-6 space-y-6" x-data="{ previewUrl: null, previewType: null }">
        {{-- File Preview Modal --}}
        <div x-show="previewUrl" x-cloak class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/90 backdrop-blur-sm" @keydown.escape.window="previewUrl = null">
            <div class="absolute top-4 right-4 z-10 flex gap-2">
                <button @click="previewUrl = null" class="rounded-full bg-white/10 p-2 text-white hover:bg-white/20 transition" title="Close">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div class="relative w-full max-w-5xl h-full flex items-center justify-center">
                <template x-if="previewType === 'image'">
                    <img :src="previewUrl" class="max-w-full max-h-full object-contain shadow-2xl rounded-lg">
                </template>
                <template x-if="previewType === 'pdf'">
                    <iframe :src="previewUrl" class="w-full h-full rounded-lg border-0 bg-white"></iframe>
                </template>
            </div>
        </div>

        <div class="flex items-start justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Application Review</h3>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Reviewing application from {{ $application->user->name }}</div>
            </div>
            <div class="shrink-0">
                <span class="text-xs font-semibold rounded-full px-2 py-1 border {{ $application->qualification_passed ? 'border-emerald-500/40 bg-emerald-500/10 text-emerald-700 dark:text-emerald-300' : 'border-amber-500/40 bg-amber-500/10 text-amber-800 dark:text-amber-200' }}">
                    {{ $application->qualification_passed ? 'Meets Eligibility Rules' : 'Manual Check Required' }}
                </span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-6">
                {{-- Profile Info --}}
                <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 p-4">
                    <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Student Profile</h4>
                    <div class="mt-3 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Gender:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100 capitalize">{{ $application->user->profile->gender ?? 'Not set' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Age:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $application->user->profile->age ?? 'Not set' }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Height:</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ $application->user->profile->height_cm ?? '?' }} cm</span>
                        </div>
                    </div>
                </div>

                {{-- Eligibility Detail --}}
                @if(is_array($application->qualification_detail) && count($application->qualification_detail))
                    <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 p-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Eligibility Analysis</h4>
                        <ul class="mt-3 text-sm text-gray-600 dark:text-gray-300 list-disc pl-4 space-y-1">
                            @foreach($application->qualification_detail as $line)
                                <li>{{ $line }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Message --}}
                @if($application->student_message)
                    <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 p-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Student's Message</h4>
                        <p class="mt-2 text-sm text-gray-700 dark:text-gray-200 leading-relaxed italic">&ldquo;{{ $application->student_message }}&rdquo;</p>
                    </div>
                @endif
            </div>

            <div class="space-y-6">
                {{-- Medical Data --}}
                @if($application->medical_bp || $application->medical_heart_rate || $application->medical_allergies)
                    <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 p-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Medical Information</h4>
                        <div class="mt-3 space-y-3">
                            @if($application->medical_bp)
                                <div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase">Blood Pressure</div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $application->medical_bp }}</div>
                                </div>
                            @endif
                            @if($application->medical_heart_rate)
                                <div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase">Heart Rate</div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $application->medical_heart_rate }} bpm</div>
                                </div>
                            @endif
                            @if($application->medical_allergies)
                                <div>
                                    <div class="text-[10px] font-bold text-gray-400 uppercase">Allergies/Notes</div>
                                    <div class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $application->medical_allergies }}</div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif

                {{-- Documents --}}
                @if($application->report_card_path || $application->other_document_path)
                    <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-gray-50/50 dark:bg-white/5 p-4">
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Attachments</h4>
                        <div class="mt-3 space-y-2">
                            @if($application->report_card_path)
                                @php
                                    $ext = pathinfo($application->report_card_path, PATHINFO_EXTENSION);
                                    $isImg = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp']);
                                @endphp
                                <button 
                                    type="button"
                                    @click="previewUrl = '{{ Storage::url($application->report_card_path) }}'; previewType = '{{ $isImg ? 'image' : 'pdf' }}'"
                                    class="w-full flex items-center justify-between p-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 hover:border-[#FF7A1A] transition group text-left"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-lg bg-orange-50 dark:bg-orange-950/30 text-[#FF7A1A] flex items-center justify-center">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Report Card</span>
                                    </div>
                                    <svg class="h-4 w-4 text-gray-400 group-hover:text-[#FF7A1A]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                            @endif
                            @if($application->other_document_path)
                                @php
                                    $ext = pathinfo($application->other_document_path, PATHINFO_EXTENSION);
                                    $isImg = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp']);
                                @endphp
                                <button 
                                    type="button"
                                    @click="previewUrl = '{{ Storage::url($application->other_document_path) }}'; previewType = '{{ $isImg ? 'image' : 'pdf' }}'"
                                    class="w-full flex items-center justify-between p-3 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-gray-900 hover:border-[#FF7A1A] transition group text-left"
                                >
                                    <div class="flex items-center gap-3">
                                        <div class="h-8 w-8 rounded-lg bg-blue-50 dark:bg-blue-950/30 text-blue-500 flex items-center justify-center">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </div>
                                        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">Medical Form</span>
                                    </div>
                                    <svg class="h-4 w-4 text-gray-400 group-hover:text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0zM2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                </button>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="pt-6 border-t border-gray-200 dark:border-white/10 flex items-center justify-end">
            <button 
                type="button" 
                @click="window.parent.dispatchEvent(new CustomEvent('close-modal'))" 
                class="rounded-2xl bg-gray-900 dark:bg-white/10 px-8 py-2.5 text-sm font-bold text-white hover:opacity-90 transition"
            >
                Close Review
            </button>
        </div>
    </div>
</x-app-layout>
