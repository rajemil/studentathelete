<x-guest-layout>
    <form method="POST" action="{{ route('register.instructor') }}" x-data="{ birthdate: '{{ old('birthdate', '') }}', calcAge() { if (!this.birthdate) return ''; const d = new Date(this.birthdate); if (String(d) === 'Invalid Date') return ''; const now = new Date(); let age = now.getFullYear() - d.getFullYear(); const m = now.getMonth() - d.getMonth(); if (m < 0 || (m === 0 && now.getDate() < d.getDate())) age--; return age < 0 ? '' : age; } }">
        @csrf

        <div class="mb-5">
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Instructor registration</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create an instructor account to manage classes, performance, and student health signals.</p>
        </div>

        <div class="space-y-4">
            @include('partials.person-name-fields')

            <div>
                <x-input-label for="email" value="Email" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            @include('partials.faculty-profile-fields')

            <div>
                <x-input-label for="achievements" value="Qualifications" />
                <textarea id="achievements" name="achievements" rows="3" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition">{{ old('achievements') }}</textarea>
                <x-input-error :messages="$errors->get('achievements')" class="mt-2" />
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
