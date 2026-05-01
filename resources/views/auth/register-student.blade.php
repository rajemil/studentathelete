<x-guest-layout>
    <form method="POST" action="{{ route('register.student') }}" x-data="{ h: '{{ old('height_cm', '') }}', w: '{{ old('weight_kg', '') }}', bmi: '' }" x-init="
        const calc = () => {
            const hc = parseFloat(h); const wk = parseFloat(w);
            if (!hc || !wk) { bmi = ''; return; }
            const m = hc / 100.0;
            bmi = (wk / (m*m)).toFixed(2);
        };
        $watch('h', calc); $watch('w', calc); calc();
    ">
        @csrf

        <div class="mb-5">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Student registration</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create a student account with athlete profile details.</p>
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
                    <x-text-input id="age" class="block mt-1 w-full" type="number" name="age" min="10" max="80" :value="old('age')" required />
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

