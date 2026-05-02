<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Sports</h2>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage individual and team sports.</div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4" x-data="{ newOpen: false, delOpen: false, delName: '', delAction: '' }">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
                    {{ session('status') }}
                </div>
            @endif

            <div class="flex items-center justify-end">
                <button type="button" @click="newOpen = true" class="inline-flex items-center rounded-2xl bg-gradient-to-r from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm hover:opacity-95">
                    New sport
                </button>
            </div>

            <!-- New sport modal -->
            <div x-show="newOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60" @click="newOpen = false"></div>
                <div class="relative w-full max-w-2xl rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex items-center justify-between">
                        <div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">New sport</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400">Create a sport in your organization.</div>
                        </div>
                        <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="newOpen = false">Close</button>
                    </div>

                    <form method="POST" action="{{ route('sports.store') }}" class="p-6 space-y-5">
                        @csrf

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div class="sm:col-span-2">
                                <x-input-label for="name" value="Sport name" />
                                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" required :value="old('name')" />
                                <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            </div>

                            <div class="sm:col-span-2">
                                <x-input-label for="slug" value="Slug (optional)" />
                                <x-text-input id="slug" name="slug" type="text" class="mt-1 block w-full" :value="old('slug')" />
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Leave blank to auto-generate from name.</div>
                                <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                            </div>

                            <div class="sm:col-span-2">
                                <x-input-label for="description" value="Description (optional)" />
                                <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-xl border-gray-300 dark:border-white/10 dark:bg-white/5 dark:text-gray-100 focus:border-[#FF7A1A] focus:ring-[#FF7A1A] transition">{{ old('description') }}</textarea>
                                <x-input-error class="mt-2" :messages="$errors->get('description')" />
                            </div>
                        </div>

                        <div class="flex items-center justify-between gap-4 pt-2 border-t border-gray-200/60 dark:border-white/10 pt-4">
                            <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="newOpen = false">Cancel</button>
                            <x-primary-button class="px-6">Create</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                        <thead class="bg-gray-50/70 dark:bg-white/5">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Sport</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Slug</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Students</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                            @forelse($sports as $sport)
                                <tr>
                                    <td class="px-5 py-4">
                                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $sport->name }}</div>
                                        @if($sport->description)
                                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 line-clamp-1">{{ $sport->description }}</div>
                                        @endif
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="text-xs font-mono text-gray-600 dark:text-gray-300">{{ $sport->slug }}</span>
                                    </td>
                                    <td class="px-5 py-4">
                                        <span class="inline-flex rounded-full bg-black/5 dark:bg-white/10 px-3 py-1 text-xs font-semibold text-gray-700 dark:text-gray-200">
                                            {{ $sport->students_count }} students
                                        </span>
                                    </td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="inline-flex items-center gap-3">
                                            <a href="{{ route('sports.show', $sport) }}" class="text-sm font-semibold text-[#FF7A1A] hover:underline">Manage</a>

                                            <button
                                                type="button"
                                                class="rounded-xl p-2 text-gray-500 hover:text-red-500 hover:bg-black/5 dark:hover:bg-white/5"
                                                title="Delete"
                                                @click="delName = @js($sport->name); delAction = @js(route('sports.destroy', $sport)); delOpen = true"
                                            >
                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                    <path d="M3 6h18"></path>
                                                    <path d="M8 6V4h8v2"></path>
                                                    <path d="M19 6l-1 14H6L5 6"></path>
                                                    <path d="M10 11v6"></path>
                                                    <path d="M14 11v6"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-5 py-10 text-center text-sm text-gray-600 dark:text-gray-400">
                                        No sports yet. Create your first sport to begin.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Shared Delete confirmation modal (single instance for performance) -->
            <div x-show="delOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/60" @click="delOpen = false"></div>
                <div class="relative w-full max-w-lg rounded-2xl border border-white/10 bg-white dark:bg-gray-900 shadow-xl overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200/60 dark:border-white/10 flex items-center justify-between">
                        <div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">Delete sport</div>
                            <div class="text-sm text-gray-600 dark:text-gray-400" x-text="delName"></div>
                        </div>
                        <button type="button" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:underline" @click="delOpen = false">Close</button>
                    </div>

                    <div class="p-6 space-y-4">
                        <div class="rounded-2xl border border-red-200/60 bg-red-50 px-4 py-3 text-sm text-red-900 dark:border-red-900/40 dark:bg-red-900/20 dark:text-red-100">
                            This will permanently delete the sport and remove all related assignments. This action cannot be undone.
                        </div>

                        <form method="POST" :action="delAction" class="flex items-center justify-end gap-3">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="rounded-2xl px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5" @click="delOpen = false">Cancel</button>
                            <button type="submit" class="inline-flex items-center rounded-2xl bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700">
                                Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div>
                {{ $sports->links() }}
            </div>
    </div>
</x-app-layout>

