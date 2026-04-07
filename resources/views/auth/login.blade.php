<x-guest-layout>
    <div class="relative">
        <!-- extra premium orbs + tiny floating UI shapes (subtle) -->
        <div class="pointer-events-none absolute -top-16 -left-10 h-56 w-56 rounded-full blur-3xl opacity-25"
             style="background: radial-gradient(circle at 30% 30%, rgba(255,122,26,0.55), transparent 62%);"></div>
        <div class="pointer-events-none absolute -bottom-20 -right-12 h-64 w-64 rounded-full blur-3xl opacity-20"
             style="background: radial-gradient(circle at 40% 40%, rgba(59,130,246,0.45), transparent 65%);"></div>
        <div class="pointer-events-none absolute left-6 top-10 h-12 w-12 rounded-2xl bg-white/5 blur-[0.5px] opacity-60 rotate-6"></div>
        <div class="pointer-events-none absolute right-10 bottom-14 h-10 w-10 rounded-full bg-white/5 blur-[0.5px] opacity-50 -rotate-6"></div>

        <x-ui.card class="p-7 sm:p-8 login-float login-glow-pulse" hoverGlow="true">
        <div class="text-center">
            <div class="text-2xl font-extrabold tracking-tight text-white">Welcome Back</div>
            <div class="mt-2 text-sm text-white/70">Login to your athlete dashboard</div>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mt-6" :status="session('status')" />

        <form
            method="POST"
            action="{{ route('login') }}"
            class="mt-6 space-y-4"
            x-data="{ loading: false, showPassword: false }"
            x-on:submit="loading = true"
        >
            @csrf

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('Email')" class="text-white/80" />
                <x-text-input
                    id="email"
                    class="mt-2 block w-full bg-white/5 text-white placeholder:text-white/35 shadow-sm
                           border {{ $errors->has('email') ? 'border-red-500/60 focus:border-red-400 focus:ring-red-500/20' : 'border-white/10 focus:border-[#FF7A1A] focus:ring-[#FF7A1A]/30' }}"
                    type="email"
                    name="email"
                    :value="old('email')"
                    required
                    autofocus
                    autocomplete="username"
                    aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
                />
                <x-input-error :messages="$errors->get('email')" class="mt-2 !text-red-300" />
            </div>

            <!-- Password -->
            <div>
                <x-input-label for="password" :value="__('Password')" class="text-white/80" />
                <div class="relative mt-2">
                    <x-text-input
                        id="password"
                        class="block w-full bg-white/5 text-white placeholder:text-white/35 shadow-sm pr-11
                               border {{ $errors->has('password') ? 'border-red-500/60 focus:border-red-400 focus:ring-red-500/20' : 'border-white/10 focus:border-[#FF7A1A] focus:ring-[#FF7A1A]/30' }}"
                        x-bind:type="showPassword ? 'text' : 'password'"
                        name="password"
                        required
                        autocomplete="current-password"
                        aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}"
                    />
                    <button
                        type="button"
                        class="absolute inset-y-0 right-0 px-3 inline-flex items-center text-white/55 hover:text-white transition-colors"
                        x-on:click="showPassword = !showPassword"
                        x-bind:aria-label="showPassword ? 'Hide password' : 'Show password'"
                    >
                        <svg x-show="!showPassword" x-cloak viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z" stroke="currentColor" stroke-width="1.8" stroke-linejoin="round"/>
                            <path d="M12 15a3 3 0 1 0-3-3 3 3 0 0 0 3 3Z" stroke="currentColor" stroke-width="1.8"/>
                        </svg>
                        <svg x-show="showPassword" x-cloak viewBox="0 0 24 24" fill="none" class="h-5 w-5">
                            <path d="M3 3l18 18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M10.6 10.6a3 3 0 0 0 4.2 4.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/>
                            <path d="M7.1 7.2C4.2 9.3 2 12 2 12s3.5 7 10 7c2 0 3.7-.5 5.1-1.2" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M14.2 5.2A10.6 10.6 0 0 0 12 5c-6.5 0-10 7-10 7a20.4 20.4 0 0 0 3.6 4.7" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <x-input-error :messages="$errors->get('password')" class="mt-2 !text-red-300" />
            </div>

            <div class="flex items-center justify-between pt-1">
                <!-- Remember Me -->
                <label for="remember_me" class="inline-flex items-center gap-2 text-sm text-white/70">
                    <input
                        id="remember_me"
                        type="checkbox"
                        class="rounded border-white/10 bg-white/5 text-[#FF7A1A] focus:ring-[#FF7A1A]/30"
                        name="remember"
                    >
                    <span>{{ __('Remember me') }}</span>
                </label>

                @if (Route::has('password.request'))
                    <a
                        href="{{ route('password.request') }}"
                        class="text-sm text-white/70 hover:text-white underline-offset-4 hover:underline transition-colors"
                    >
                        {{ __('Forgot password?') }}
                    </a>
                @endif
            </div>

            <button
                type="submit"
                data-motion-btn
                x-bind:disabled="loading"
                x-bind:aria-busy="loading ? 'true' : 'false'"
                class="mt-2 inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold text-black transition-all
                       bg-gradient-to-r from-[#FF7A1A] via-[#FF8A3A] to-[#FFB24D]
                       shadow-[0_12px_38px_rgba(255,122,26,0.20)]
                       hover:shadow-[0_18px_56px_rgba(255,122,26,0.34)]
                       hover:-translate-y-0.5 active:translate-y-0
                       disabled:opacity-70 disabled:cursor-not-allowed disabled:hover:translate-y-0"
            >
                <svg x-show="loading" x-cloak class="-ml-1 mr-2 h-4 w-4 animate-spin text-black/70" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2a10 10 0 1 0 10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
                <span x-text="loading ? '{{ __('Logging in...') }}' : '{{ __('Login') }}'"></span>
            </button>
        </form>
        </x-ui.card>
    </div>
</x-guest-layout>
