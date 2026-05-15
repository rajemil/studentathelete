<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Academic Settings</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage courses, year levels, and sections for student classification.</div>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ tab: 'courses' }">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
            <button @click="tab = 'courses'" :class="tab === 'courses' ? 'bg-[#FF7A1A] text-white' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400'" class="px-4 py-2 rounded-2xl text-sm font-semibold shadow-sm transition-all shrink-0">Courses</button>
            <button @click="tab = 'years'" :class="tab === 'years' ? 'bg-[#FF7A1A] text-white' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400'" class="px-4 py-2 rounded-2xl text-sm font-semibold shadow-sm transition-all shrink-0">Year Levels</button>
            <button @click="tab = 'sections'" :class="tab === 'sections' ? 'bg-[#FF7A1A] text-white' : 'bg-white dark:bg-gray-900 text-gray-600 dark:text-gray-400'" class="px-4 py-2 rounded-2xl text-sm font-semibold shadow-sm transition-all shrink-0">Sections</button>
        </div>

        <!-- COURSES TAB -->
        <div x-show="tab === 'courses'" class="space-y-4">
            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-6 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-4">Add New Course</h3>
                <form method="POST" action="{{ route('admin.settings.courses.store') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    @csrf
                    <div class="sm:col-span-2">
                        <x-input-label value="Course Name (e.g. BS Information Technology)" />
                        <x-text-input name="name" type="text" class="mt-1 block w-full" required />
                    </div>
                    <div>
                        <x-input-label value="Code (e.g. BSIT)" />
                        <div class="mt-1 flex gap-2">
                            <x-text-input name="code" type="text" class="block w-full" />
                            <x-primary-button>Add</x-primary-button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 overflow-hidden shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                    <thead class="bg-gray-50/70 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Course</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Code</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($courses as $c)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $c->name }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $c->code ?: '—' }}</td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <form method="POST" action="{{ route('admin.settings.courses.destroy', $c) }}" onsubmit="return confirm('Remove this course?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-gray-500">No courses defined yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- YEARS TAB -->
        <div x-show="tab === 'years'" class="space-y-4" x-cloak>
            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-6 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-4">Add Year Level</h3>
                <form method="POST" action="{{ route('admin.settings.years.store') }}" class="flex flex-col sm:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <x-input-label value="Year Level Name (e.g. 1st Year)" />
                        <x-text-input name="name" type="text" class="mt-1 block w-full" required />
                    </div>
                    @csrf
                    <x-primary-button>Add Year Level</x-primary-button>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 overflow-hidden shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                    <thead class="bg-gray-50/70 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Year Level</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($yearLevels as $y)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $y->name }}</td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <form method="POST" action="{{ route('admin.settings.years.destroy', $y) }}" onsubmit="return confirm('Remove this year level?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-4 py-8 text-center text-sm text-gray-500">No year levels defined yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTIONS TAB -->
        <div x-show="tab === 'sections'" class="space-y-4" x-cloak>
            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-6 shadow-sm">
                <h3 class="text-sm font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-4">Add Section</h3>
                <form method="POST" action="{{ route('admin.settings.sections.store') }}" class="flex flex-col sm:flex-row gap-4 items-end">
                    <div class="flex-1">
                        <x-input-label value="Section Name (e.g. Section A)" />
                        <x-text-input name="name" type="text" class="mt-1 block w-full" required />
                    </div>
                    @csrf
                    <x-primary-button>Add Section</x-primary-button>
                </form>
            </div>

            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 overflow-hidden shadow-sm">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
                    <thead class="bg-gray-50/70 dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 dark:text-gray-400">Section</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 dark:text-gray-400">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/10">
                        @forelse($sections as $s)
                            <tr>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-gray-100">{{ $s->name }}</td>
                                <td class="px-4 py-3 text-right text-sm">
                                    <form method="POST" action="{{ route('admin.settings.sections.destroy', $s) }}" onsubmit="return confirm('Remove this section?')">
                                        @csrf @method('DELETE')
                                        <button class="text-red-600 hover:underline">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-4 py-8 text-center text-sm text-gray-500">No sections defined yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
