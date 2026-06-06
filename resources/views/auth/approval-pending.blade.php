<x-guest-layout>
    <div class="space-y-6 text-center">
        <div>
            <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Registration Pending</h1>
            <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                Your student account has been created and is currently awaiting approval from a coach.
                You will be able to access the dashboard and register for sports once your account is approved.
            </p>
        </div>

        <div class="pt-4 flex justify-center">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm font-semibold text-[#FF7A1A] hover:underline">
                    Log Out
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
