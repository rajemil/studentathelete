<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Add faculty</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Create an admin, coach, or instructor account.</div>
        </div>
    </x-slot>

    <div class="max-w-xl space-y-6">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('admin.users.store') }}" class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-6 shadow-sm space-y-4">
            @csrf

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
                <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition" required>
                    @foreach(['coach','instructor','admin'] as $r)
                        <option value="{{ $r }}" @selected(old('role', 'coach') === $r)>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" value="Temporary password (optional)" />
                <x-text-input id="password" name="password" type="text" class="mt-1 block w-full" :value="old('password')" />
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to use the default password: <span class="font-semibold">password</span></div>
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-4 pt-2">
                <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                <x-primary-button>Create</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>

