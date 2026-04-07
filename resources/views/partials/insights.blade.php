@php
    $badge = function (string $severity): string {
        return match ($severity) {
            'success' => 'bg-emerald-50 text-emerald-900 border-emerald-200 dark:bg-emerald-900/20 dark:text-emerald-100 dark:border-emerald-900/40',
            'warning' => 'bg-amber-50 text-amber-900 border-amber-200 dark:bg-amber-900/20 dark:text-amber-100 dark:border-amber-900/40',
            'danger' => 'bg-red-50 text-red-900 border-red-200 dark:bg-red-900/20 dark:text-red-100 dark:border-red-900/40',
            default => 'bg-gray-50 text-gray-900 border-gray-200 dark:bg-white/5 dark:text-gray-100 dark:border-white/10',
        };
    };
@endphp

<div class="dash-card rounded-3xl overflow-hidden">
    <div class="border-b border-gray-200/60 dark:border-white/10 px-6 py-5 flex items-center justify-between">
        <div>
            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Smart insights</div>
            <div class="text-xs text-gray-500 dark:text-gray-400">Auto-generated from performance + stats trends</div>
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">
            Updated {{ optional($insights->first()?->computed_at)->diffForHumans() ?? '—' }}
        </div>
    </div>

    <div class="divide-y divide-gray-200 dark:divide-white/10">
        @forelse($insights as $insight)
            <div class="px-5 py-4 flex items-start gap-3">
                <span class="mt-0.5 inline-flex items-center rounded-full border px-2 py-0.5 text-[11px] font-semibold {{ $badge($insight->severity) }}">
                    {{ strtoupper($insight->severity) }}
                </span>
                <div class="min-w-0">
                    <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                        {{ $insight->title }}
                    </div>
                    <div class="mt-1 text-sm text-gray-700 dark:text-gray-200">
                        {{ $insight->message }}
                    </div>
                </div>
            </div>
        @empty
            <div class="px-5 py-8 text-sm text-gray-600 dark:text-gray-400">
                No insights yet. Add some scores and run `php artisan insights:generate`.
            </div>
        @endforelse
    </div>
</div>

