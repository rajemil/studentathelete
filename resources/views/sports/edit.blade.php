<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Edit Sport</h2>
                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ $sport->name }}</div>
            </div>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="dash-card rounded-3xl p-8">
                <form method="POST" action="{{ route('sports.update', $sport) }}{{ request()->boolean('modal') ? '?modal=1' : '' }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <div>
                        <x-input-label for="name" value="Sport name" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required :value="old('name', $sport->name)" />
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                    <div>
                        <x-input-label for="slug" value="Slug" />
                        <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug', $sport->slug)" />
                        <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                    </div>

                    <div>
                        <x-input-label for="description" value="Description (optional)" />
                        <textarea id="description" name="description" rows="5" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-gray-100 focus:border-[#FF7A1A] focus:ring-[#FF7A1A] transition">{{ old('description', $sport->description) }}</textarea>
                        <x-input-error class="mt-2" :messages="$errors->get('description')" />
                    </div>

                    <div class="flex items-center justify-end gap-4 pt-2">
                        @unless(request()->boolean('modal'))
                            <a href="{{ route('sports.show', $sport) }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 transition">Back</a>
                        @endunless
                        <button type="submit" class="inline-flex items-center rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-5 py-2.5 text-sm font-semibold text-white shadow-sm glow-border-orange hover:shadow-md transition">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
