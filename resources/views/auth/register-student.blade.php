<x-guest-layout>
    <form method="POST" action="{{ route('register.student') }}" x-data="{ birthdate: '{{ old('birthdate', '') }}', h: '{{ old('height_cm', '') }}', w: '{{ old('weight_kg', '') }}', bmi: '', calcAge() { if (!this.birthdate) return ''; const d = new Date(this.birthdate); if (String(d) === 'Invalid Date') return ''; const now = new Date(); let age = now.getFullYear() - d.getFullYear(); const m = now.getMonth() - d.getMonth(); if (m < 0 || (m === 0 && now.getDate() < d.getDate())) age--; return age < 0 ? '' : age; } }" x-init="
        const calc = () => {
            const hc = parseFloat(h); const wk = parseFloat(w);
            if (!hc || !wk) { bmi = ''; return; }
            const m = hc / 100.0;
            bmi = (wk / (m*m)).toFixed(2);
        };
        $watch('h', calc); $watch('w', calc); calc();
    ">
        @csrf

        @if(!empty($invitationToken))
            <input type="hidden" name="invitation_token" value="{{ $invitationToken }}" />
        @endif

        <div class="mb-5">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Student Athlete registration</h1>
            @if($invitedUser ?? null)
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Complete your invited registration. Your email is pre-filled from your invitation.</p>
            @else
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create a Student Athlete account with profile details.</p>
            @endif
        </div>

        <div class="space-y-4">
            @include('partials.person-name-fields', ['user' => $invitedUser ?? null])

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $invitedUser->email ?? '')" @if($invitedUser ?? null) readonly @endif required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="course" value="Course / Program" />
                <x-text-input id="course" class="block mt-1 w-full" type="text" name="course" :value="old('course')" required />
                <x-input-error :messages="$errors->get('course')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="birthdate" value="Birthday" />
                    <x-text-input id="birthdate" class="block mt-1 w-full" type="date" name="birthdate" x-model="birthdate" :value="old('birthdate')" required />
                    <x-input-error :messages="$errors->get('birthdate')" class="mt-2" />
                </div>
                <div>
                    <x-input-label value="Age (auto)" />
                    <div class="mt-1 h-10 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 px-3 flex items-center text-sm text-gray-900 dark:text-gray-100">
                        <span x-text="calcAge() || '—'"></span>
                    </div>
                </div>
                <div>
                    <x-input-label for="gender" value="Gender" />
                    <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition" required>
                        <option value="">Select...</option>
                        @foreach(['male' => 'Male', 'female' => 'Female', 'other' => 'Other', 'prefer_not_to_say' => 'Prefer not to say'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('gender')===$val)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('gender')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="address" value="Address" />
                <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')" required />
                <x-input-error :messages="$errors->get('address')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <x-input-label for="height_cm" value="Height (cm)" />
                    <x-text-input id="height_cm" class="block mt-1 w-full" type="number" step="0.01" name="height_cm" x-model="h" required />
                    <x-input-error :messages="$errors->get('height_cm')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="weight_kg" value="Weight (kg)" />
                    <x-text-input id="weight_kg" class="block mt-1 w-full" type="number" step="0.01" name="weight_kg" x-model="w" required />
                    <x-input-error :messages="$errors->get('weight_kg')" class="mt-2" />
                </div>
                <div>
                    <x-input-label value="BMI (auto)" />
                    <div class="mt-1 h-10 rounded-md border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/40 px-3 flex items-center text-sm text-gray-900 dark:text-gray-100">
                        <span x-text="bmi || '—'"></span>
                    </div>
                </div>
            </div>

            <div>
                <x-input-label for="sports_interested" value="Sports Interested" />
                <select
                    id="sports_interested"
                    name="sports_interested[]"
                    multiple
                    size="6"
                    class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition
                           [&>option]:bg-white [&>option]:text-gray-900 dark:[&>option]:bg-gray-900 dark:[&>option]:text-gray-100"
                >
                    @foreach($sports as $sport)
                        <option value="{{ $sport->id }}" @selected(collect(old('sports_interested', []))->contains($sport->id))>{{ $sport->name }}</option>
                    @endforeach
                </select>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hold Ctrl/Command to select multiple.</div>
                <x-input-error :messages="$errors->get('sports_interested')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="password" value="Password" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="password_confirmation" value="Confirm Password" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-between mt-6">
            <a class="text-sm text-gray-600 dark:text-gray-400 hover:underline" href="{{ route('register') }}">Back</a>
            <x-primary-button>Register</x-primary-button>
        </div>
    </form>
</x-guest-layout>

