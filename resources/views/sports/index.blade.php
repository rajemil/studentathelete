<x-app-layout>
    <x-slot name="header">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Sports</h2>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage individual and team sports.</div>
            </div>
            <a href="{{ route('sports.create') }}" class="inline-flex items-center rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm glow-border-orange hover:shadow-md transition">
                New sport
            </a>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">
            @if (session('status'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($sports as $sport)
                    <a href="{{ route('sports.show', $sport) }}" class="dash-card dash-card-glow rounded-3xl p-6 group block transition">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $sport->name }}</div>
                                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-mono">{{ $sport->slug }}</div>
                            </div>
                            <div class="text-xs font-semibold rounded-full bg-[#FF7A1A]/10 text-[#FF7A1A] border border-[#FF7A1A]/20 px-3 py-1">
                                {{ $sport->students_count }} students
                            </div>
                        </div>
                        @if($sport->description)
                            <div class="mt-4 text-sm text-gray-600 dark:text-gray-300 line-clamp-2">
                                {{ $sport->description }}
                            </div>
                        @endif
                    </a>
                @empty
                    <div class="dash-card rounded-3xl p-8 text-center sm:col-span-2 lg:col-span-3">
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            No sports yet. Create your first sport to begin.
                        </div>
                    </div>
                @endforelse
            </div>

            <div>
                {{ $sports->links() }}
            </div>
        </div>
    </div>
</x-app-layout>

