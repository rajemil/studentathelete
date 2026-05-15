<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Student management</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create and manage student accounts and academic classifications.</div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{
        addOpen: false,
        manageOpen: false,
        delOpen: false,
        delName: '',
        delAction: '',
        manageId: null,
        manageUpdateUrl: '',
        manageDeleteUrl: '',
        manageName: '',
        manageEmail: '',
        manageBirthdate: '',
        manageGender: '',
        manageAddress: '',
        manageHeight: '',
        manageWeight: '',
        managePassword: '',
        manageCourseId: '',
        manageYearLevelId: '',
        manageSectionId: '',
        photoUrl: null,
        addPhotoPreview: null,
        birthdate: '',
        calcAge() {
            if (!this.birthdate) return '';
            const d = new Date(this.birthdate);
            if (String(d) === 'Invalid Date') return '';
            const now = new Date();
            let age = now.getFullYear() - d.getFullYear();
            const m = now.getMonth() - d.getMonth();
            if (m < 0 || (m === 0 && now.getDate() < d.getDate())) age--;
            return age < 0 ? '' : age;
        },
        openManage(s) {
            this.manageId = s.id;
            this.manageUpdateUrl = @js(url('/admin/students')).replace(/\/$/, '') + '/' + s.id;
            this.manageDeleteUrl = this.manageUpdateUrl;
            this.manageName = s.name;
            this.manageEmail = s.email;
            this.manageBirthdate = s.birthdate || '';
            this.manageGender = s.gender || '';
            this.manageAddress = s.address || '';
            this.manageHeight = s.height_cm ?? '';
            this.manageWeight = s.weight_kg ?? '';
            this.managePassword = '';
            this.manageCourseId = s.course_id || '';
            this.manageYearLevelId = s.year_level_id || '';
            this.manageSectionId = s.section_id || '';
            this.photoUrl = s.photo_url || null;
            this.manageOpen = true;
        },
        openDelete(name, url) {
            this.delName = name;
            this.delAction = url;
            this.delOpen = true;
        },
        onAddPhotoChange(e) {
            if (this.addPhotoPreview) URL.revokeObjectURL(this.addPhotoPreview);
            const f = e.target.files && e.target.files[0];
            this.addPhotoPreview = f ? URL.createObjectURL(f) : null;
        }
    }">
        <div class="flex items-center justify-end">
            <button type="button" @click="addOpen = true" class="inline-flex items-center rounded-2xl bg-gradient-to-r from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-95">
                Add student
            </button>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif
        @if (session('new_student_code'))
            <div class="rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-950 dark:border-amber-800/50 dark:bg-amber-950/40 dark:text-amber-100">
                <span class="font-semibold">Access code (copy now; email failed):</span>
                <span class="ml-2 font-mono tracking-widest">{{ session('new_student_code') }}</span>
            </div>
        @endif


        {{-- Add student modal --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" @click="addOpen = false"></div>
            <div class="relative w-full max-w-5xl rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white flex items-center justify-center shadow-lg shadow-[#FF7A1A]/20">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-900 dark:text-gray-100">Add New Student</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400">Register a new student with academic and physical profile.</div>
                        </div>
                    </div>
                    <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="addOpen = false">Close</button>
                </div>

                <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data" class="p-6 space-y-6 overflow-y-auto">
                    @csrf
                    
                    <div class="rounded-xl border border-sky-200/80 bg-sky-50/80 dark:border-sky-900/40 dark:bg-sky-950/30 px-4 py-3 text-xs text-sky-900 dark:text-sky-100">
                        A <strong>6-character access code</strong> will be generated automatically and sent to the student email.
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <!-- LEFT COLUMN: Profile & Metrics -->
                        <div class="lg:col-span-4 space-y-4">
                            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-4 space-y-4">
                                <div class="flex items-center gap-4">
                                    <div class="h-20 w-20 rounded-2xl bg-gray-100 dark:bg-white/5 overflow-hidden flex items-center justify-center">
                                        <template x-if="addPhotoPreview">
                                            <img :src="addPhotoPreview" alt="" class="h-full w-full object-cover" />
                                        </template>
                                        <template x-if="!addPhotoPreview">
                                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">PHOTO</span>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Photo</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Optional profile picture.</div>
                                    </div>
                                </div>
                                <input
                                    name="photo"
                                    type="file"
                                    accept="image/*"
                                    @change="onAddPhotoChange($event)"
                                    class="block w-full text-sm text-gray-700 dark:text-gray-200 file:mr-3 file:rounded-xl file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:opacity-90 dark:file:bg-white/10 dark:file:text-gray-100"
                                />

                                <div class="pt-2 border-t border-gray-200/60 dark:border-white/10 space-y-3">
                                    <div>
                                        <x-input-label for="add_birthdate" value="Birthdate (optional)" />
                                        <x-text-input id="add_birthdate" name="birthdate" type="date" class="mt-1 block w-full" x-model="birthdate" />
                                    </div>
                                    <div>
                                        <x-input-label value="Age (auto)" />
                                        <div class="mt-1 w-full rounded-md border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-gray-900/60 px-3 py-2 text-sm text-gray-700 dark:text-gray-200" x-text="calcAge() || '—'"></div>
                                    </div>
                                    <div>
                                        <x-input-label for="add_gender" value="Gender (optional)" />
                                        <select id="add_gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition">
                                            <option value="">—</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                            <option value="prefer_not_to_say">Prefer not to say</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN: Account & Academic & Physical -->
                        <div class="lg:col-span-8 space-y-6">
                            <!-- Account -->
                            <div class="space-y-4">
                                <div class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Account Details</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="add_name" value="Full Name" />
                                        <x-text-input id="add_name" name="name" type="text" class="mt-1 block w-full" required />
                                    </div>
                                    <div>
                                        <x-input-label for="add_email" value="Email Address" />
                                        <x-text-input id="add_email" name="email" type="email" class="mt-1 block w-full" required />
                                    </div>
                                </div>
                            </div>

                            <!-- Academic -->
                            <div class="space-y-4">
                                <div class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Academic Information</div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <x-input-label for="add_course" value="Course" />
                                        <select id="add_course" name="course_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition">
                                            <option value="">Select Course</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}">{{ $course->name }} ({{ $course->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="add_year_level" value="Year Level" />
                                        <select id="add_year_level" name="year_level_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition">
                                            <option value="">Select Year</option>
                                            @foreach($yearLevels as $yl)
                                                <option value="{{ $yl->id }}">{{ $yl->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="add_section" value="Section" />
                                        <select id="add_section" name="section_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition">
                                            <option value="">Select Section</option>
                                            @foreach($sections as $sec)
                                                <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Physical -->
                            <div class="space-y-4">
                                <div class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Physical Metrics</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="add_height" value="Height (cm)" />
                                        <x-text-input id="add_height" name="height_cm" type="number" step="0.1" class="mt-1 block w-full" />
                                    </div>
                                    <div>
                                        <x-input-label for="add_weight" value="Weight (kg)" />
                                        <x-text-input id="add_weight" name="weight_kg" type="number" step="0.1" class="mt-1 block w-full" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <x-input-label for="add_address" value="Home Address" />
                                        <x-text-input id="add_address" name="address" type="text" class="mt-1 block w-full" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 pt-4 border-t border-gray-200/60 dark:border-white/10">
                        <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="addOpen = false">Cancel</button>
                        <x-primary-button class="px-8">Create Student</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Manage student modal --}}
        <div x-show="manageOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" @click="manageOpen = false"></div>
            <div class="relative w-full max-w-5xl rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="h-12 w-12 rounded-2xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] text-white flex items-center justify-center shadow-lg shadow-[#FF7A1A]/20">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                        <div>
                            <div class="text-xl font-bold text-gray-900 dark:text-gray-100">Manage Student</div>
                            <div class="text-sm text-gray-500 dark:text-gray-400" x-text="manageName + ' · ' + manageEmail"></div>
                        </div>
                    </div>
                    <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="manageOpen = false">Close</button>
                </div>

                <form method="POST" :action="manageUpdateUrl" enctype="multipart/form-data" class="p-6 space-y-6 overflow-y-auto">
                    @csrf
                    @method('PATCH')
                    

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <!-- LEFT COLUMN -->
                        <div class="lg:col-span-4 space-y-4">
                            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-gray-50 dark:bg-white/5 p-4 space-y-4">
                                <div class="flex items-center gap-4">
                                    <div class="h-20 w-20 rounded-2xl bg-gray-100 dark:bg-white/5 overflow-hidden flex items-center justify-center">
                                        <template x-if="photoUrl">
                                            <img :src="photoUrl" alt="" class="h-full w-full object-cover" />
                                        </template>
                                        <template x-if="!photoUrl">
                                            <span class="text-xs font-bold text-gray-500 dark:text-gray-400">PHOTO</span>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Photo</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">Upload to replace.</div>
                                    </div>
                                </div>
                                <input
                                    name="photo"
                                    type="file"
                                    accept="image/*"
                                    @change="const f=$event.target.files?.[0]; photoUrl = f ? URL.createObjectURL(f) : photoUrl"
                                    class="block w-full text-sm text-gray-700 dark:text-gray-200 file:mr-3 file:rounded-xl file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:opacity-90 dark:file:bg-white/10 dark:file:text-gray-100"
                                />

                                <div class="pt-2 border-t border-gray-200/60 dark:border-white/10 space-y-3">
                                    <div>
                                        <x-input-label value="Birthdate" />
                                        <x-text-input name="birthdate" type="date" class="mt-1 block w-full" x-model="manageBirthdate" />
                                    </div>
                                    <div>
                                        <x-input-label value="Age (auto)" />
                                        <div class="mt-1 w-full rounded-md border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-gray-900/60 px-3 py-2 text-sm text-gray-700 dark:text-gray-200" x-text="calcAge() || '—'"></div>
                                    </div>
                                    <div>
                                        <x-input-label value="Gender" />
                                        <select name="gender" x-model="manageGender" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition">
                                            <option value="">—</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                            <option value="prefer_not_to_say">Prefer not to say</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="lg:col-span-8 space-y-6">
                            <!-- Account -->
                            <div class="space-y-4">
                                <div class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Account</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label value="Full Name" />
                                        <x-text-input name="name" type="text" class="mt-1 block w-full" x-model="manageName" required />
                                    </div>
                                    <div>
                                        <x-input-label value="Email Address" />
                                        <x-text-input name="email" type="email" class="mt-1 block w-full" x-model="manageEmail" required />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <x-input-label value="New Password (leave blank to keep current)" />
                                        <x-text-input name="password" type="text" class="mt-1 block w-full" x-model="managePassword" placeholder="Enter new password..." />
                                    </div>
                                </div>
                            </div>

                            <!-- Academic -->
                            <div class="space-y-4">
                                <div class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Academic Information</div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div>
                                        <x-input-label value="Course" />
                                        <select name="course_id" x-model="manageCourseId" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition">
                                            <option value="">Select Course</option>
                                            @foreach($courses as $course)
                                                <option value="{{ $course->id }}">{{ $course->name }} ({{ $course->code }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label value="Year Level" />
                                        <select name="year_level_id" x-model="manageYearLevelId" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition">
                                            <option value="">Select Year</option>
                                            @foreach($yearLevels as $yl)
                                                <option value="{{ $yl->id }}">{{ $yl->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label value="Section" />
                                        <select name="section_id" x-model="manageSectionId" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition">
                                            <option value="">Select Section</option>
                                            @foreach($sections as $sec)
                                                <option value="{{ $sec->id }}">{{ $sec->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Physical -->
                            <div class="space-y-4">
                                <div class="text-xs font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500">Physical Metrics</div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label value="Height (cm)" />
                                        <x-text-input name="height_cm" type="number" step="0.1" class="mt-1 block w-full" x-model="manageHeight" />
                                    </div>
                                    <div>
                                        <x-input-label value="Weight (kg)" />
                                        <x-text-input name="weight_kg" type="number" step="0.1" class="mt-1 block w-full" x-model="manageWeight" />
                                    </div>
                                    <div class="sm:col-span-2">
                                        <x-input-label value="Home Address" />
                                        <x-text-input name="address" type="text" class="mt-1 block w-full" x-model="manageAddress" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 pt-4 border-t border-gray-200/60 dark:border-white/10">
                        <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="manageOpen = false">Cancel</button>
                        <x-primary-button class="px-8">Update Student</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Delete modal --}}
        <div x-show="delOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" @click="delOpen = false"></div>
            <div class="relative w-full max-w-md rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl p-6">
                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Delete student?</div>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Remove <span class="font-semibold" x-text="delName"></span> from the organization. This cannot be undone.</p>
                <form :action="delAction" method="POST" class="mt-6 flex justify-end gap-3">
                    @csrf
                    @method('DELETE')
                    <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="delOpen = false">Cancel</button>
                    <button type="submit" class="inline-flex items-center rounded-xl bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Delete</button>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                    <thead class="bg-gray-50/70 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 w-1/3">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Course / Year / Section</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400 w-24">Actions</th>
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
                                    'birthdate' => optional($p?->birthdate)->format('Y-m-d'),
                                    'gender' => $p?->gender,
                                    'address' => $p?->address,
                                    'height_cm' => $p?->height_cm,
                                    'weight_kg' => $p?->weight_kg,
                                    'course_id' => $p?->course_id,
                                    'year_level_id' => $p?->year_level_id,
                                    'section_id' => $p?->section_id,
                                    'photo_url' => $photo,
                                ];
                            @endphp
                            <tr class="group hover:bg-gray-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-11 w-11 shrink-0 rounded-2xl bg-gray-100 dark:bg-white/10 bg-cover bg-center ring-2 ring-white dark:ring-white/5 shadow-sm" @if($photo) style="background-image: url('{{ $photo }}')" @endif></div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $stu->name }}</div>
                                            <div class="text-[11px] text-gray-500 dark:text-gray-400 uppercase tracking-wider font-bold mt-0.5">Student Athlete</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-sm text-gray-600 dark:text-gray-300">{{ $stu->email }}</td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-bold bg-[#FF7A1A]/10 text-[#FF7A1A] border border-[#FF7A1A]/20">
                                            {{ $p?->course?->code ?: ($p?->course?->name ?: 'No Course') }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-bold bg-sky-500/10 text-sky-500 border border-sky-500/20 uppercase">
                                            {{ $p?->yearLevel?->name ?: 'N/A' }}
                                        </span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-bold bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                            {{ $p?->section?->name ?: 'N/A' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" @click="openManage(@js($payload))" class="h-9 w-9 flex items-center justify-center rounded-xl bg-white dark:bg-white/5 border border-gray-200/60 dark:border-white/10 text-gray-600 dark:text-gray-400 hover:text-[#FF7A1A] hover:border-[#FF7A1A]/50 transition-all shadow-sm">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button type="button" @click="openDelete(@js($stu->name), @js(route('admin.students.destroy', $stu)))" class="h-9 w-9 flex items-center justify-center rounded-xl bg-white dark:bg-white/5 border border-gray-200/60 dark:border-white/10 text-gray-600 dark:text-gray-400 hover:text-red-600 hover:border-red-600/50 transition-all shadow-sm">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-20">
                                    <div class="flex flex-col items-center justify-center text-center">
                                        <div class="h-16 w-16 rounded-2xl bg-gray-50 dark:bg-white/5 flex items-center justify-center text-gray-400 dark:text-gray-500 mb-4">
                                            <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2m12-9a4 4 0 11-8 0 4 4 0 018 0zm6 3l-3 3m0 0l-3-3m3 3V10"></path>
                                            </svg>
                                        </div>
                                        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">No students found</h3>
                                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 max-w-xs">Start by adding your first student athlete to manage their profile and academic details.</p>
                                        <button type="button" @click="addOpen = true" class="mt-6 inline-flex items-center rounded-xl bg-gray-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-gray-800 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-200 transition">
                                            Add First Student
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200/60 dark:border-white/10">
                {{ $students->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
