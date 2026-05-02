<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Faculty management</h2>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage faculty accounts and roles (admin, coach, instructor).</div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4" x-data="{ addOpen: false }">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex items-center justify-end">
            <button type="button" @click="addOpen = true" class="inline-flex items-center rounded-2xl bg-gradient-to-r from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-95">
                Add faculty
            </button>
        </div>

        <!-- Add faculty modal -->
        <div x-show="addOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data="{ photoUrl: null, birthdate: '', role: '{{ old('role','coach') }}', calcAge() { if(!this.birthdate) return ''; const d = new Date(this.birthdate); if(String(d) === 'Invalid Date') return ''; const now = new Date(); let age = now.getFullYear() - d.getFullYear(); const m = now.getMonth() - d.getMonth(); if (m < 0 || (m === 0 && now.getDate() < d.getDate())) age--; return age < 0 ? '' : age; } }">
            <div class="absolute inset-0 bg-black/60" @click="addOpen = false"></div>
            <div class="relative w-full max-w-5xl rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex items-center justify-between shrink-0">
                    <div>
                        <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Add faculty</div>
                        <div class="text-sm text-gray-600 dark:text-gray-400">Personal details + role + sports/teams assignment.</div>
                    </div>
                    <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="addOpen = false">Close</button>
                </div>

                <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="p-6 space-y-6 overflow-y-auto">
                    @csrf

                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                        <div class="lg:col-span-4">
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
                                        <div class="text-xs text-gray-500 dark:text-gray-400">PNG/JPG up to 5MB.</div>
                                    </div>
                                </div>
                                <input
                                    name="photo"
                                    type="file"
                                    accept="image/*"
                                    @change="const f=$event.target.files?.[0]; photoUrl = f ? URL.createObjectURL(f) : null"
                                    class="block w-full text-sm text-gray-700 dark:text-gray-200 file:mr-3 file:rounded-xl file:border-0 file:bg-gray-900 file:px-3 file:py-2 file:text-xs file:font-semibold file:text-white hover:file:opacity-90 dark:file:bg-white/10 dark:file:text-gray-100"
                                />
                                <x-input-error :messages="$errors->get('photo')" class="mt-1" />

                                <div class="pt-2 border-t border-gray-200/60 dark:border-white/10 space-y-3">
                                    <div>
                                        <x-input-label for="birthdate" value="Birthdate (optional)" />
                                        <x-text-input id="birthdate" name="birthdate" type="date" class="mt-1 block w-full" x-model="birthdate" />
                                        <x-input-error :messages="$errors->get('birthdate')" class="mt-2" />
                                    </div>
                                    <div>
                                        <x-input-label value="Age (auto)" />
                                        <div class="mt-1 w-full rounded-md border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-gray-900/60 px-3 py-2 text-sm text-gray-700 dark:text-gray-200" x-text="calcAge() || '—'"></div>
                                    </div>
                                    <div>
                                        <x-input-label for="gender" value="Gender (optional)" />
                                        <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition">
                                            <option value="">—</option>
                                            <option value="male" @selected(old('gender') === 'male')>Male</option>
                                            <option value="female" @selected(old('gender') === 'female')>Female</option>
                                            <option value="other" @selected(old('gender') === 'other')>Other</option>
                                            <option value="prefer_not_to_say" @selected(old('gender') === 'prefer_not_to_say')>Prefer not to say</option>
                                        </select>
                                        <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-8 space-y-4">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Important details</div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <x-input-label for="name" value="Name" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="email" value="Email" />
                                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email')" required />
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="role" value="Role" />
                                    <select id="role" name="role" x-model="role" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition" required>
                                        @foreach(['coach','instructor','admin'] as $r)
                                            <option value="{{ $r }}" @selected(old('role', 'coach') === $r)>{{ ucfirst($r) }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="password" value="Temporary password (optional)" />
                                    <x-text-input id="password" name="password" type="text" class="mt-1 block w-full" :value="old('password')" />
                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to use: <span class="font-semibold">password</span></div>
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="address" value="Address (optional)" />
                                    <x-text-input id="address" name="address" type="text" class="mt-1 block w-full" :value="old('address')" />
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="profession" value="Profession (optional)" />
                                    <x-text-input id="profession" name="profession" type="text" class="mt-1 block w-full" :value="old('profession')" />
                                    <x-input-error :messages="$errors->get('profession')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="field_expertise" value="Field expertise (optional)" />
                                    <x-text-input id="field_expertise" name="field_expertise" type="text" class="mt-1 block w-full" :value="old('field_expertise')" />
                                    <x-input-error :messages="$errors->get('field_expertise')" class="mt-2" />
                                </div>
                                <div class="sm:col-span-2">
                                    <x-input-label for="achievements" value="Achievements (optional)" />
                                    <textarea id="achievements" name="achievements" rows="3" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition">{{ old('achievements') }}</textarea>
                                    <x-input-error :messages="$errors->get('achievements')" class="mt-2" />
                                </div>
                                <div>
                                    <x-input-label for="coaching_experience_years" value="Experience years (optional)" />
                                    <x-text-input id="coaching_experience_years" name="coaching_experience_years" type="number" class="mt-1 block w-full" :value="old('coaching_experience_years')" />
                                    <x-input-error :messages="$errors->get('coaching_experience_years')" class="mt-2" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Sports assignment</div>
                        <div class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-text="role === 'instructor' ? 'Select sports to teach (1 instructor per sport).' : 'Select sports to coach (instructor info is optional).'"></div>

                        <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                            @foreach($sports as $sport)
                                @php
                                    $assignedInstructorName = $sport->instructor?->name;
                                    $assignedInstructorEmail = $sport->instructor?->email;
                                    $facultyAssigned = $sportFacultyAssignments[$sport->id] ?? null;
                                    $isTaken = $facultyAssigned !== null;
                                @endphp
                                <label class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/60 dark:bg-gray-900/40 p-4 flex items-start gap-3 {{ $isTaken ? 'opacity-60' : '' }}">
                                    <input type="checkbox" name="sport_ids[]" value="{{ $sport->id }}" class="mt-1 rounded border-gray-300 dark:border-gray-700" @disabled($isTaken) />
                                    <span class="min-w-0">
                                        <span class="block text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $sport->name }}</span>
                                        @if($isTaken)
                                            <span class="block text-xs font-semibold text-amber-600 dark:text-amber-400 mt-1">
                                                Assigned to {{ $facultyAssigned->name }} ({{ $facultyAssigned->role }})
                                            </span>
                                        @endif
                                        <span class="block text-xs text-gray-500 dark:text-gray-400" x-show="role === 'instructor'">
                                            @if($assignedInstructorName)
                                                Instructor: {{ $assignedInstructorName }} ({{ $assignedInstructorEmail }})
                                            @else
                                                Available (no instructor yet)
                                            @endif
                                        </span>
                                        <span class="block text-xs text-gray-500 dark:text-gray-400" x-show="role !== 'instructor'">
                                            @if($assignedInstructorName)
                                                Instructor: {{ $assignedInstructorName }}
                                            @else
                                                Active sport
                                            @endif
                                        </span>
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex items-center justify-between gap-4 pt-2 border-t border-gray-200/60 dark:border-white/10 pt-4">
                        <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="addOpen = false">Cancel</button>
                        <x-primary-button class="px-6">Create</x-primary-button>
                    </div>
                </form>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                    <thead class="bg-gray-50/70 dark:bg-white/5">
                        <tr>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Faculty</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Role</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Assigned sports</th>
                            <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @foreach($users as $u)
                            @php
                                $assignedSports = collect($u->sports ?? [])
                                    ->filter()
                                    ->unique('id')
                                    ->values();

                                $assignedSportIds = $assignedSports
                                    ->pluck('id')
                                    ->map(fn ($v) => (int) $v)
                                    ->values()
                                    ->all();

                                // Use relative URL so it works on 127.0.0.1:8000 (APP_URL may omit the port).
                                $photoUrl = $u->profile?->photo_path ? '/storage/'.$u->profile->photo_path : null;
                                $birthdate = $u->profile?->birthdate?->format('Y-m-d');
                            @endphp
                            <tr x-data="{ open: false, delOpen: false }">
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="h-10 w-10 rounded-2xl bg-gray-100 dark:bg-white/5 overflow-hidden flex items-center justify-center ring-1 ring-black/5 dark:ring-white/10">
                                            @if($photoUrl)
                                                <div class="h-full w-full bg-center bg-cover" style="background-image: url('{{ $photoUrl }}')"></div>
                                            @else
                                                <span class="text-xs font-bold text-gray-500 dark:text-gray-400">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</span>
                                            @endif
                                        </div>
                                        <div class="min-w-0">
                                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $u->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $u->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex rounded-full border border-gray-200 dark:border-white/10 px-3 py-1 text-xs font-semibold uppercase text-gray-700 dark:text-gray-200">{{ $u->role }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    @if($assignedSports->isEmpty())
                                        <span class="text-sm text-gray-500 dark:text-gray-400">None</span>
                                    @else
                                        <div class="flex flex-wrap gap-2">
                                            @foreach($assignedSports as $sn)
                                                <span class="inline-flex rounded-full bg-black/5 dark:bg-white/10 px-3 py-1 text-xs font-semibold text-gray-700 dark:text-gray-200">{{ is_string($sn) ? $sn : ($sn->name ?? '') }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="inline-flex items-center gap-3">
                                        <button type="button" @click="open = true" class="text-sm font-semibold text-[#FF7A1A] hover:underline">Manage</button>

                                        <button type="button" class="rounded-xl p-2 text-gray-500 hover:text-red-500 hover:bg-black/5 dark:hover:bg-white/5" title="Delete" @click="delOpen = true">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                <path d="M3 6h18"></path>
                                                <path d="M8 6V4h8v2"></path>
                                                <path d="M19 6l-1 14H6L5 6"></path>
                                                <path d="M10 11v6"></path>
                                                <path d="M14 11v6"></path>
                                            </svg>
                                        </button>
                                    </div>
                                </td>

                                <!-- Delete confirmation modal -->
                                <td x-show="delOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                                    <div class="absolute inset-0 bg-black/60" @click="delOpen = false"></div>
                                    <div class="relative mx-auto w-full max-w-lg rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl overflow-hidden">
                                        <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex items-center justify-between">
                                            <div>
                                                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Delete faculty</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ $u->name }} · {{ $u->email }}</div>
                                            </div>
                                            <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="delOpen = false">Close</button>
                                        </div>

                                        <div class="p-6 space-y-4">
                                            <div class="rounded-2xl border border-red-200/60 bg-red-50 px-4 py-3 text-sm text-red-900 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-100">
                                                This will permanently delete the faculty account and related profile data. This action cannot be undone.
                                            </div>

                                            <form method="POST" action="{{ route('admin.users.destroy', $u) }}" class="flex items-center justify-end gap-3">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="rounded-2xl px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5" @click="delOpen = false">Cancel</button>
                                                <button type="submit" class="inline-flex items-center rounded-2xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                                                    Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>

                                <!-- Edit modal -->
                                <td x-show="open" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data="{ photoUrl: @js($photoUrl), birthdate: @js($birthdate), calcAge() { if(!this.birthdate) return ''; const d = new Date(this.birthdate); if(String(d) === 'Invalid Date') return ''; const now = new Date(); let age = now.getFullYear() - d.getFullYear(); const m = now.getMonth() - d.getMonth(); if (m < 0 || (m === 0 && now.getDate() < d.getDate())) age--; return age < 0 ? '' : age; } }">
                                    <div class="absolute inset-0 bg-black/60" @click="open = false"></div>
                                    <div class="relative mx-auto w-full max-w-6xl rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl overflow-hidden max-h-[90vh] flex flex-col">
                                        <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex items-center justify-between shrink-0">
                                            <div>
                                                <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Manage faculty</div>
                                                <div class="text-sm text-gray-600 dark:text-gray-400">{{ $u->name }} · {{ $u->email }}</div>
                                            </div>
                                            <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="open = false">Close</button>
                                        </div>

                                        <form method="POST" action="{{ route('admin.users.update', $u) }}" enctype="multipart/form-data" class="p-6 space-y-6 overflow-y-auto">
                                            @csrf
                                            @method('PATCH')

                                            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                                                <div class="lg:col-span-4">
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
                                                                <div class="text-xs text-gray-500 dark:text-gray-400">Uploading a new photo replaces the old one.</div>
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
                                                                <x-input-label value="Birthdate (optional)" />
                                                                <x-text-input name="birthdate" type="date" class="mt-1 block w-full" x-model="birthdate" />
                                                            </div>
                                                            <div>
                                                                <x-input-label value="Age (auto)" />
                                                                <div class="mt-1 w-full rounded-md border border-gray-200 dark:border-white/10 bg-white/70 dark:bg-gray-900/60 px-3 py-2 text-sm text-gray-700 dark:text-gray-200" x-text="calcAge() || '—'"></div>
                                                            </div>
                                                            <div>
                                                                <x-input-label value="Gender (optional)" />
                                                                <select name="gender" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition">
                                                                    <option value="">—</option>
                                                                    <option value="male" @selected(old('gender', $u->profile?->gender) === 'male')>Male</option>
                                                                    <option value="female" @selected(old('gender', $u->profile?->gender) === 'female')>Female</option>
                                                                    <option value="other" @selected(old('gender', $u->profile?->gender) === 'other')>Other</option>
                                                                    <option value="prefer_not_to_say" @selected(old('gender', $u->profile?->gender) === 'prefer_not_to_say')>Prefer not to say</option>
                                                                </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="lg:col-span-8 space-y-4">
                                                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Important details</div>
                                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                                        <div>
                                                            <x-input-label value="Name" />
                                                            <x-text-input name="name" type="text" class="mt-1 block w-full" :value="old('name', $u->name)" required />
                                                        </div>
                                                        <div>
                                                            <x-input-label value="Email" />
                                                            <x-text-input name="email" type="email" class="mt-1 block w-full" :value="old('email', $u->email)" required />
                                                        </div>
                                                        <div>
                                                            <x-input-label value="Role" />
                                                            <select name="role" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition" required>
                                                                @foreach(['admin','coach','instructor'] as $r)
                                                                    <option value="{{ $r }}" @selected(old('role', $u->role) === $r)>{{ ucfirst($r) }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="sm:col-span-2">
                                                            <x-input-label value="Address (optional)" />
                                                            <x-text-input name="address" type="text" class="mt-1 block w-full" :value="old('address', $u->profile?->address)" />
                                                        </div>
                                                        <div>
                                                            <x-input-label value="Profession (optional)" />
                                                            <x-text-input name="profession" type="text" class="mt-1 block w-full" :value="old('profession', $u->profile?->profession)" />
                                                        </div>
                                                        <div>
                                                            <x-input-label value="Field expertise (optional)" />
                                                            <x-text-input name="field_expertise" type="text" class="mt-1 block w-full" :value="old('field_expertise', $u->profile?->field_expertise)" />
                                                        </div>
                                                        <div class="sm:col-span-2">
                                                            <x-input-label value="Achievements (optional)" />
                                                            <textarea name="achievements" rows="3" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition">{{ old('achievements', $u->profile?->achievements) }}</textarea>
                                                        </div>
                                                        <div>
                                                            <x-input-label value="Experience years (optional)" />
                                                            <x-text-input name="coaching_experience_years" type="number" class="mt-1 block w-full" :value="old('coaching_experience_years', $u->profile?->coaching_experience_years)" />
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div>
                                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Sports assignment</div>
                                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                    Select which sports this faculty member will coach/teach.
                                                    Sports already assigned to another faculty member are disabled.
                                                </div>

                                                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                                                    @foreach($sports as $sport)
                                                        @php
                                                            $assignedInstructorName = $sport->instructor?->name;
                                                            $assignedInstructorEmail = $sport->instructor?->email;
                                                            $facultyAssigned = $sportFacultyAssignments[$sport->id] ?? null;
                                                            $isTakenByOtherFaculty = $facultyAssigned && (int) $facultyAssigned->user_id !== (int) $u->id;

                                                            $isAssignedToOtherInstructor = $sport->instructor_user_id && (int) $sport->instructor_user_id !== (int) $u->id;
                                                            $checked = in_array((int) $sport->id, old('sport_ids', $assignedSportIds), true);
                                                        @endphp
                                                        <label class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/60 dark:bg-gray-900/40 p-4 flex items-start gap-3 {{ $isTakenByOtherFaculty ? 'opacity-60' : '' }}">
                                                            <input
                                                                type="checkbox"
                                                                name="sport_ids[]"
                                                                value="{{ $sport->id }}"
                                                                class="mt-1 rounded border-gray-300 dark:border-gray-700"
                                                                @checked($checked)
                                                                @disabled($isTakenByOtherFaculty || ($u->role === 'instructor' && $isAssignedToOtherInstructor))
                                                            />
                                                            <span class="min-w-0">
                                                                <span class="block text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $sport->name }}</span>
                                                                @if($isTakenByOtherFaculty)
                                                                    <span class="block text-xs font-semibold text-amber-600 dark:text-amber-400 mt-1">
                                                                        Assigned to {{ $facultyAssigned->name }} ({{ $facultyAssigned->role }})
                                                                    </span>
                                                                @endif

                                                                @if($assignedInstructorName)
                                                                    <span class="block text-xs text-gray-500 dark:text-gray-400">
                                                                        Instructor: {{ $assignedInstructorName }} ({{ $assignedInstructorEmail }})
                                                                    </span>
                                                                @else
                                                                    <span class="block text-xs text-gray-500 dark:text-gray-400">
                                                                        {{ $u->role === 'instructor' ? 'Available (no instructor yet)' : 'Active sport' }}
                                                                    </span>
                                                                @endif
                                                                @if($u->role === 'instructor' && $isAssignedToOtherInstructor)
                                                                    <span class="block text-xs font-semibold text-amber-600 dark:text-amber-400 mt-1">Unavailable for instructor assignment</span>
                                                                @endif
                                                            </span>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>

                                            <div class="flex items-center justify-between gap-4 pt-2 border-t border-gray-200/60 dark:border-white/10 pt-4">
                                                <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="open = false">Cancel</button>
                                                <x-primary-button class="px-6">Save</x-primary-button>
                                            </div>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
