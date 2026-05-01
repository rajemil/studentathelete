<x-guest-layout>
    <form method="POST" action="{{ route('register.instructor') }}">
        @csrf

        <div class="mb-5">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Instructor registration</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create an instructor account to manage classes, performance, and student health signals.</p>
        </div>

        <div class="space-y-4">
            <div>
                <x-input-label for="name" value="Full Name" />
                <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="age" value="Age" />
                    <x-text-input id="age" class="block mt-1 w-full" type="number" name="age" min="18" max="90" :value="old('age')" required />
                    <x-input-error :messages="$errors->get('age')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="gender" value="Gender" />
                    <select id="gender" name="gender" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition" required>
                        <option value="">Select...</option>
                        @foreach(['Male','Female','Non-binary','Prefer not to say'] as $g)
                            <option value="{{ $g }}" @selected(old('gender')===$g)>{{ $g }}</option>
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="field_expertise" value="Subject / sport focus" />
                    <x-text-input id="field_expertise" class="block mt-1 w-full" type="text" name="field_expertise" :value="old('field_expertise')" required />
                    <x-input-error :messages="$errors->get('field_expertise')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="profession" value="Role / specialization" />
                    <x-text-input id="profession" class="block mt-1 w-full" type="text" name="profession" :value="old('profession')" required />
                    <x-input-error :messages="$errors->get('profession')" class="mt-2" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="coaching_experience_years" value="Instruction experience (years)" />
                    <x-text-input id="coaching_experience_years" class="block mt-1 w-full" type="number" name="coaching_experience_years" min="0" max="70" :value="old('coaching_experience_years')" required />
                    <x-input-error :messages="$errors->get('coaching_experience_years')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="achievements" value="Qualifications (optional)" />
                    <textarea id="achievements" name="achievements" rows="3" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-white/35 transition">{{ old('achievements') }}</textarea>
                    <x-input-error :messages="$errors->get('achievements')" class="mt-2" />
                </div>
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
