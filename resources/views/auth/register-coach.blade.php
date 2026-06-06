<x-guest-layout wide>
    <form
        method="POST"
        action="{{ route('register.coach') }}"
        enctype="multipart/form-data"
        x-data="{ photoUrl: null }"
        class="rounded-2xl border border-white/10 bg-gray-900/90 backdrop-blur-xl shadow-2xl overflow-hidden"
    >
        @csrf

        <div class="px-6 py-5 border-b border-white/10">
            <h1 class="text-xl font-semibold text-white">Coach registration</h1>
            <p class="mt-1 text-sm text-gray-400">Create a coach account with your background and expertise.</p>
        </div>

        <div class="p-6 space-y-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                <div class="lg:col-span-4">
                    @include('partials.profile-photo-upload')
                </div>

                <div class="lg:col-span-8 space-y-6">
                    <section class="space-y-4">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Account</h2>
                        @include('partials.person-name-fields')

                        <div>
                            <x-input-label for="email" value="Email" />
                            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="address" value="Address" />
                            <x-text-input id="address" class="block mt-1 w-full" type="text" name="address" :value="old('address')" required style="text-transform: uppercase;" />
                            <x-input-error :messages="$errors->get('address')" class="mt-2" />
                        </div>
                    </section>

                    <section class="space-y-4">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Profile details</h2>
                        @include('partials.faculty-profile-fields')
                    </section>

                    <section class="space-y-4">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Background</h2>
                        <div>
                            <x-input-label for="achievements" value="Achievements / qualifications" />
                            <textarea
                                id="achievements"
                                name="achievements"
                                rows="4"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 placeholder:text-gray-400 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 dark:placeholder:text-white/35 transition"
                                placeholder="Certifications, teams coached, notable results…"
                            >{{ old('achievements') }}</textarea>
                            <x-input-error :messages="$errors->get('achievements')" class="mt-2" />
                        </div>
                    </section>

                    <section class="space-y-4">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-400">Security</h2>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="password" value="Password" />
                                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Min 6 characters, 1 uppercase, 1 number.</p>
                                <x-input-error :messages="$errors->get('password')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="password_confirmation" value="Confirm password" />
                                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-white/10 flex items-center justify-between gap-4 bg-black/20">
            <a class="text-sm text-gray-400 hover:text-white transition" href="{{ route('register') }}">← Back</a>
            <x-primary-button class="!bg-gradient-to-br !from-[#FF7A1A] !to-[#FFB24D] !border-0">Register</x-primary-button>
        </div>
    </form>
</x-guest-layout>
