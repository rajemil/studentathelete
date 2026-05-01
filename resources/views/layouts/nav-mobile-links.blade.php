@php
    $role = Auth::user()->role ?? 'student';
    $isAdmin = $role === 'admin';
    $isCoach = $role === 'coach';
    $isInstructor = $role === 'instructor';
    $isStudent = $role === 'student';
    $staffSports = $isAdmin || $isCoach || $isInstructor;
    $coachLike = $isCoach || $isInstructor;
@endphp

<a href="{{ route('dashboard') }}" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5">
    @if($isAdmin) Admin dashboard @elseif($isCoach) Coach dashboard @elseif($isInstructor) Instructor dashboard @else Dashboard @endif
</a>

@if($isAdmin)
    <a href="{{ route('admin.users.index') }}" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5">Users & roles</a>
@endif

@if($staffSports)
    <a href="{{ route('sports.index') }}" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5">Sports & teams</a>
@endif

@if($coachLike)
    <a href="{{ route('staff.performance_scores.hub') }}" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5">Performance scores</a>
    <a href="{{ route('staff.injury_logs.index') }}" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5">Injury & health logs</a>
    <a href="{{ route('staff.ai_recommendations.hub') }}" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5">AI recommendations</a>
@endif

@if($staffSports)
    <a href="{{ route('analytics.index') }}" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5">Analytics</a>
@endif

@if($isAdmin)
    <a href="{{ route('admin.reports.index') }}" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5">Reports</a>
    <a href="{{ route('notifications.index') }}" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5">Notifications</a>
    <a href="{{ route('admin.system.index') }}" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5">System config</a>
@endif

@if($isStudent)
    <a href="{{ route('student.sports.index') }}" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5">Sports & registration</a>
    <a href="{{ route('student.dashboard') }}#ai-recommendations" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5">AI recommendations</a>
@endif

<a href="{{ route('profile.edit') }}" class="block rounded-2xl px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-black/5 dark:text-gray-200 dark:hover:bg-white/5">Profile</a>
