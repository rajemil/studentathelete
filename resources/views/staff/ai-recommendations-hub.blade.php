<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">AI recommendations</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Lineup suggestions and win-probability tools per sport.</div>
        </div>
    </x-slot>

    <div class="space-y-6">
        <a href="{{ route('analytics.index') }}" class="block rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-5 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Analytics</div>
                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Trends and performance insights across the program.</div>
                </div>
                <span class="text-sm font-semibold text-[#FF7A1A]">Open →</span>
            </div>
        </a>

        @forelse($sports as $sport)
            <a href="{{ route('sports.team_suggestions.index', $sport) }}" class="block rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-5 shadow-sm hover:shadow-md transition">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $sport->name }}</div>
                        <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">AI team suggestions &amp; lineup ideas</div>
                    </div>
                    <span class="text-sm font-semibold text-[#FF7A1A]">Open →</span>
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-8 text-sm text-gray-600 dark:text-gray-400">
                No sports linked to your teams yet.
            </div>
        @endforelse
    </div>
</x-app-layout>
