<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">Reports</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">High-level counts and links to deeper analytics.</div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-5">
            <div class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Students</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $summary['students'] }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-5">
            <div class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Coaches</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $summary['coaches'] }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-5">
            <div class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Instructors</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $summary['instructors'] }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-5">
            <div class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Sports</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $summary['sports'] }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-5">
            <div class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Teams</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $summary['teams'] }}</div>
        </div>
        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 p-5">
            <div class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">Scores logged (30d)</div>
            <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-white">{{ $summary['scores_30d'] }}</div>
        </div>
    </div>

    <div class="mt-8 flex flex-wrap gap-3">
        <a href="{{ route('analytics.index') }}" class="inline-flex rounded-xl bg-gradient-to-br from-[#FF7A1A] to-[#FFB24D] px-4 py-2 text-sm font-semibold text-white shadow-sm">Open analytics</a>
        <a href="{{ route('admin.reports.performance_scores_csv') }}" class="inline-flex rounded-xl border border-gray-200 dark:border-white/15 px-4 py-2 text-sm font-semibold text-gray-800 dark:text-gray-100">Download scores CSV (90d)</a>
        <a href="{{ route('admin.dashboard') }}" class="inline-flex rounded-xl border border-gray-200 dark:border-white/15 px-4 py-2 text-sm font-semibold text-gray-800 dark:text-gray-100">Admin dashboard</a>
        <a href="{{ route('sports.index') }}" class="inline-flex rounded-xl border border-gray-200 dark:border-white/15 px-4 py-2 text-sm font-semibold text-gray-800 dark:text-gray-100">Sports &amp; teams</a>
    </div>
</x-app-layout>
