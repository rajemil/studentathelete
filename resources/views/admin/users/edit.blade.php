<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Faculty details</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ $user->name }} · {{ $user->email }}</div>
        </div>
    </x-slot>

    <div class="max-w-3xl space-y-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-6 shadow-sm space-y-4">
            @csrf
            @method('PATCH')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" value="Name" />
                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>
                <div>
                    <x-input-label for="email" value="Email" />
                    <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>
            </div>

            <div>
                <x-input-label for="role" value="Role" />
                <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 focus:border-indigo-500 focus:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 transition" required>
                    @foreach(['admin','coach','instructor'] as $r)
                        <option value="{{ $r }}" @selected(old('role', $user->role) === $r)>{{ ucfirst($r) }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            <div class="pt-2">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Assigned teams</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Select which teams this faculty member can access as a coach (sports come from team assignments).</div>

                <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-3">
                    @foreach($teams->groupBy(fn($t) => $t->sport?->name ?? 'Other') as $sportName => $sportTeams)
                        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/60 dark:bg-gray-900/40 p-4">
                            <div class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $sportName }}</div>
                            <div class="mt-2 space-y-2">
                                @foreach($sportTeams as $t)
                                    <label class="flex items-start gap-3 text-sm text-gray-800 dark:text-gray-200">
                                        <input
                                            type="checkbox"
                                            name="team_ids[]"
                                            value="{{ $t->id }}"
                                            class="mt-1 rounded border-gray-300 dark:border-gray-700"
                                            @checked(in_array($t->id, old('team_ids', $assignedTeamIds ?? []), true))
                                        />
                                        <span class="leading-tight">
                                            <span class="font-medium">{{ $t->name }}</span>
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">Team ID: {{ $t->id }}</span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('team_ids')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between gap-4 pt-2">
                <a href="{{ route('admin.users.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancel</a>
                <x-primary-button>Save</x-primary-button>
            </div>
        </form>
    </div>
</x-app-layout>
