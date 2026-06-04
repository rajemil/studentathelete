<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Student management</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create and manage student accounts and sport enrollment.</div>
        </div>
    </x-slot>

    <div class="space-y-4" x-data="{
        addOpen: false,
        manageOpen: false,
        delOpen: false,
        delName: '',
        delAction: '',
        manageId: null,
        manageUpdateUrl: '',
        manageDeleteUrl: '',
        manageFirstName: '',
        manageLastName: '',
        manageEmail: '',
        manageCourse: '',
        manageBirthdate: '',
        manageGender: '',
        manageAddress: '',
        manageHeight: '',
        manageWeight: '',
        managePassword: '',
        manageSportIds: [],
        photoUrl: null,
        addPhotoPreview: null,
        birthdate: '',
        addH: '',
        addW: '',
        addBmi: '',
        manageH: '',
        manageW: '',
        manageBmi: '',
        calcBmi(h, w) {
            const hc = parseFloat(h); const wk = parseFloat(w);
            if (!hc || !wk) return '';
            const m = hc / 100.0;
            return (wk / (m * m)).toFixed(2);
        },
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
            this.manageFirstName = s.first_name || '';
            this.manageLastName = s.last_name || '';
            this.manageEmail = s.email;
            this.manageCourse = s.course || '';
            this.manageBirthdate = s.birthdate || '';
            this.manageGender = s.gender || '';
            this.manageAddress = s.address || '';
            this.manageHeight = s.height_cm ?? '';
            this.manageWeight = s.weight_kg ?? '';
            this.manageH = this.manageHeight;
            this.manageW = this.manageWeight;
            this.manageBmi = this.calcBmi(this.manageH, this.manageW);
            this.managePassword = '';
            this.manageSportIds = (s.sport_ids || []).map(Number);
            this.photoUrl = null;
            this.manageOpen = true;
        },
        sportChecked(id) {
            return this.manageSportIds.map(Number).includes(Number(id));
        },
        syncSportFromCheckbox(id, event) {
            id = Number(id);
            const on = Boolean(event.target.checked);
            const ids = this.manageSportIds.map(Number);
            if (on && ! ids.includes(id)) {
                this.manageSportIds.push(id);
            }
            if (! on) {
                this.manageSportIds = ids.filter((x) => x !== id);
            }
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
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex items-center justify-end">
            <button type="button" @click="addOpen = true" class="inline-flex items-center rounded-2xl bg-gradient-to-r from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-95">
                Add student
            </button>
        </div>

        {{-- Add student modal --}}
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" @click="addOpen = false"></div>
            <div class="relative w-full max-w-3xl rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex justify-between shrink-0">
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add student</div>
                    <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="addOpen = false">Close</button>
                </div>
                <form method="POST" action="{{ route('admin.students.store') }}" enctype="multipart/form-data" class="p-6 space-y-5 overflow-y-auto">
                    @csrf
                    <div class="rounded-xl border border-sky-200/80 bg-sky-50/80 dark:border-sky-900/40 dark:bg-sky-950/30 px-4 py-3 text-xs text-sky-900 dark:text-sky-100">
                        Set a strong password for the student. They will receive an email verification link before they can sign in.
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 space-y-4">
                            @include('partials.person-name-fields')
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="add_email" value="Email" />
                                    <x-text-input id="add_email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="add_password" value="Password" />
                                    <x-text-input id="add_password" name="password" type="password" class="mt-1 block w-full" required autocomplete="new-password" />
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="add_password_confirmation" value="Confirm Password" />
                                    <x-text-input id="add_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" required />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="add_birthdate" value="Birthday" />
                                    <x-text-input id="add_birthdate" name="birthdate" type="date" class="mt-1 block w-full" x-model="birthdate" :value="old('birthdate')" required />
                                    <x-input-error :messages="$errors->get('birthdate')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label value="Age (auto)" />
                                    <div class="mt-1 rounded-md border border-gray-200 dark:border-white/10 px-3 py-2 text-sm" x-text="calcAge() || '—'"></div>
                                </div>
                                <div>
                                    <x-input-label for="add_gender" value="Gender" />
                                    <select id="add_gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                                        <option value="">Select...</option>
                                        @foreach(['male','female','other','prefer_not_to_say'] as $g)
                                            <option value="{{ $g }}" @selected(old('gender') === $g)>{{ ucfirst(str_replace('_',' ', $g)) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="add_course" value="Course / Program" />
                                    <x-text-input id="add_course" name="course" type="text" class="mt-1 block w-full" :value="old('course')" required />
                                    <x-input-error :messages="$errors->get('course')" class="mt-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="add_address" value="Address" />
                                    <x-text-input id="add_address" name="address" type="text" class="mt-1 block w-full" :value="old('address')" required />
                                </div>
                                <div>
                                    <x-input-label for="add_height" value="Height (cm)" />
                                    <x-text-input id="add_height" name="height_cm" type="number" step="0.01" class="mt-1 block w-full" x-model="addH" required />
                                </div>
                                <div>
                                    <x-input-label for="add_weight" value="Weight (kg)" />
                                    <x-text-input id="add_weight" name="weight_kg" type="number" step="0.01" class="mt-1 block w-full" x-model="addW" required />
                                </div>
                                <div>
                                    <x-input-label value="BMI (auto)" />
                                    <div class="mt-1 rounded-md border border-gray-200 dark:border-white/10 px-3 py-2 text-sm" x-text="calcBmi(addH, addW) || '—'"></div>
                                </div>
                            </div>
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sports</div>
                                <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
                                    @foreach($sports as $sport)
                                        <label class="flex items-center gap-2 rounded-xl border border-gray-200/60 dark:border-white/10 p-3">
                                            <input type="checkbox" name="sport_ids[]" value="{{ $sport->id }}" class="rounded border-gray-300 dark:border-gray-700" />
                                            <span class="text-sm text-gray-800 dark:text-gray-200">{{ $sport->name }}</span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="space-y-3">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Photo</div>
                            <x-input-label for="add_photo" value="Upload (optional)" />
                            <input id="add_photo" name="photo" type="file" accept="image/*" class="mt-1 block w-full text-sm" @change="onAddPhotoChange($event)" />
                            <div class="relative aspect-square max-h-48 rounded-2xl border border-dashed border-gray-300 dark:border-white/15 bg-gray-50 dark:bg-white/5 overflow-hidden">
                                <span x-show="!addPhotoPreview" class="absolute inset-0 flex items-center justify-center text-xs text-gray-500 dark:text-gray-400">No preview</span>
                                <img x-show="addPhotoPreview" x-bind:src="addPhotoPreview" alt="" class="h-full w-full object-cover" />
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-200/60 dark:border-white/10">
                        <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="addOpen = false">Cancel</button>
                        <x-primary-button>Create</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Manage student modal --}}
        <div x-show="manageOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/60" @click="manageOpen = false"></div>
            <div class="relative w-full max-w-3xl rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex justify-between shrink-0">
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Manage student</div>
                    <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="manageOpen = false">Close</button>
                </div>
                <form method="POST" :action="manageUpdateUrl" enctype="multipart/form-data" class="p-6 space-y-4 overflow-y-auto">
                    @csrf
                    @method('PATCH')
                    <template x-for="sid in manageSportIds" :key="sid">
                        <input type="hidden" name="sport_ids[]" :value="sid" />
                    </template>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">First Name</label>
                            <input name="first_name" type="text" x-model="manageFirstName" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Last Name</label>
                            <input name="last_name" type="text" x-model="manageLastName" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input name="email" type="email" x-model="manageEmail" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Course / Program</label>
                            <input name="course" type="text" x-model="manageCourse" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">New password</label>
                            <input name="password" type="password" x-model="managePassword" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" placeholder="Leave blank to keep current" />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Birthday</label>
                            <input name="birthdate" type="date" x-model="manageBirthdate" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Gender</label>
                            <select name="gender" x-model="manageGender" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                                <option value="prefer_not_to_say">Prefer not to say</option>
                            </select>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Address</label>
                            <input name="address" type="text" x-model="manageAddress" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Height (cm)</label>
                            <input name="height_cm" type="number" step="0.01" x-model="manageH" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Weight (kg)</label>
                            <input name="weight_kg" type="number" step="0.01" x-model="manageW" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100" required />
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">BMI (auto)</label>
                            <div class="mt-1 rounded-md border border-gray-200 dark:border-white/10 px-3 py-2 text-sm" x-text="calcBmi(manageH, manageW) || '—'"></div>
                        </div>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Sports</div>
                        <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-2">
                            @foreach($sports as $sport)
                                <label class="flex items-center gap-2 rounded-xl border border-gray-200/60 dark:border-white/10 p-3 cursor-pointer">
                                    <input type="checkbox" :checked="sportChecked({{ $sport->id }})" @change="syncSportFromCheckbox({{ $sport->id }}, $event)" class="rounded border-gray-300 dark:border-gray-700" />
                                    <span class="text-sm text-gray-800 dark:text-gray-200">{{ $sport->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 dark:text-gray-300">Photo (optional)</label>
                        <input name="photo" type="file" accept="image/*" class="mt-1 block w-full text-sm" />
                    </div>
                    <div class="flex justify-end gap-3 pt-2 border-t border-gray-200/60 dark:border-white/10">
                        <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="manageOpen = false">Cancel</button>
                        <x-primary-button>Save</x-primary-button>
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
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Student</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Email</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Sports</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($students as $stu)
                            @php
                                $p = $stu->profile;
                                $photo = $p?->photo_path ? '/storage/'.$p->photo_path : null;
                                $sportIds = $stu->sports->pluck('id')->values()->all();
                                $nameParts = \App\Support\PersonName::split($stu->name);
                                $payload = [
                                    'id' => $stu->id,
                                    'first_name' => $nameParts['first_name'],
                                    'last_name' => $nameParts['last_name'],
                                    'email' => $stu->email,
                                    'course' => $p?->course,
                                    'birthdate' => optional($p?->birthdate)->format('Y-m-d'),
                                    'gender' => $p?->gender,
                                    'address' => $p?->address,
                                    'height_cm' => $p?->height_cm,
                                    'weight_kg' => $p?->weight_kg,
                                    'sport_ids' => $sportIds,
                                ];
                            @endphp
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 shrink-0 rounded-2xl bg-gray-100 dark:bg-white/10 bg-cover bg-center ring-1 ring-black/5 dark:ring-white/10" @if($photo) style="background-image: url('{{ $photo }}')" @endif></div>
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $stu->name }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">{{ $stu->email }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $stu->sports->pluck('name')->join(', ') ?: '—' }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <button type="button" class="text-sm font-semibold text-[#FF7A1A] hover:underline" @click="openManage(@js($payload))">Manage</button>
                                    <button type="button" class="ml-3 text-red-600 dark:text-red-400 hover:underline" title="Delete" @click="openDelete(@js($stu->name), @js(route('admin.students.destroy', $stu)))">
                                        <svg class="inline h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m3 0v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6h14zM10 11v6M14 11v6"/></svg>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-600 dark:text-gray-400">No students yet. Add a student or enable public registration.</td>
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
