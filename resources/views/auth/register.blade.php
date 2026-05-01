<x-guest-layout>
    <div class="space-y-5">
        <div>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Create your account</h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Choose your role to continue.</p>
        </div>

        <div class="grid grid-cols-1 gap-3">
            <a href="{{ route('register.student') }}" class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 hover:shadow-md transition">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Register as Student</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Track performance, rankings, and recommendations.</div>
            </a>
            <a href="{{ route('register.coach') }}" class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 hover:shadow-md transition">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Register as Coach</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Manage teams, enter scores, and build lineups.</div>
            </a>
            <a href="{{ route('register.instructor') }}" class="rounded-2xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 hover:shadow-md transition">
                <div class="text-sm font-semibold text-gray-900 dark:text-gray-100">Register as Instructor</div>
                <div class="mt-1 text-sm text-gray-600 dark:text-gray-400">Classes, student performance, health logs, and AI insights.</div>
            </a>
        </div>

        <div class="text-sm text-gray-600 dark:text-gray-400">
            Already registered?
            <a class="underline hover:text-gray-900 dark:hover:text-gray-100" href="{{ route('login') }}">Log in</a>
        </div>
    </div>
</x-guest-layout>
