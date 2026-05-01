<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">System configuration</h2>
            <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Read-only overview of core application settings (change via <code class="text-xs">.env</code> and config files).</div>
        </div>
    </x-slot>

    <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 divide-y divide-gray-200 dark:divide-white/10 overflow-hidden">
        @foreach($basics as $label => $value)
            <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1">
                <div class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $label }}</div>
                <div class="text-sm text-gray-600 dark:text-gray-400 font-mono break-all">{{ $value }}</div>
            </div>
        @endforeach
    </div>
</x-app-layout>
