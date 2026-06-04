<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Predictive recommendations</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Data-driven team suggestions and performance analytics for your sports.</div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($sports as $sport)
            <a href="{{ route('sports.team_suggestions.index', $sport) }}" class="block rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-5 shadow-sm hover:shadow-md transition">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $sport->name }}</div>
                <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">Team recommendations, compatibility analysis, and Elo-based win probability</div>
            </a>
        @empty
            <div class="md:col-span-3 rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-6 text-sm text-gray-600 dark:text-gray-400">
                No sports assigned to your teams yet. Once you coach a team, predictive recommendations will appear here.
            </div>
        @endforelse
    </div>
</x-app-layout>
