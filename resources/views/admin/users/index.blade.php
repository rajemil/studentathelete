<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div>
                <h2 class="text-lg sm:text-xl font-semibold tracking-tight text-gray-900 dark:text-gray-100">User management</h2>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">View accounts and manage roles (admin, coach, instructor, student).</div>
            </div>
        </div>
    </x-slot>

    <div class="space-y-4">
        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 dark:border-emerald-900/40 dark:bg-emerald-900/20 dark:text-emerald-100">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-2xl border border-gray-200/60 dark:border-white/10 bg-white/80 dark:bg-gray-900/50 overflow-hidden shadow-sm">
            <div class="divide-y divide-gray-200 dark:divide-white/10">
                @foreach($users as $u)
                    <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $u->name }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ $u->email }}</div>
                        </div>
                        <div class="flex items-center gap-3 shrink-0">
                            <span class="inline-flex rounded-full border border-gray-200 dark:border-white/10 px-3 py-1 text-xs font-semibold uppercase text-gray-700 dark:text-gray-200">{{ $u->role }}</span>
                            <a href="{{ route('admin.users.edit', $u) }}" class="text-sm font-semibold text-[#FF7A1A] hover:underline">Manage role</a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div>
            {{ $users->links() }}
        </div>
    </div>
</x-app-layout>
