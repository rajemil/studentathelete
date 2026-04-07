<x-app-layout>
    <x-slot name="header">
        <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">New Sport</h2>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dash-card rounded-3xl p-8">
                <form method="POST" action="{{ route('sports.store') }}" class="space-y-6">
                    @csrf

                    <div>
                        <x-input-label for="name" value="Sport name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required autofocus :value="old('name')" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="slug" value="Slug (optional)" />
                        <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug')" />
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to auto-generate from name.</div>
                        <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Description (optional)" />
                        <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-gray-100 focus:border-[#FF7A1A] focus:ring-[#FF7A1A] transition">{{ old('description') }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-2">
                        <a href="{{ route('sports.index') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition">Cancel</a>
                        <button type="submit" class="inline-flex items-center rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-5 py-2.5 text-sm font-semibold text-white shadow-sm glow-border-orange hover:shadow-md transition">
                            Create Sport
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
