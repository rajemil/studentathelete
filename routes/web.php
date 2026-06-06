<?php

use App\Http\Controllers\Admin\AdminReportsController;
use App\Http\Controllers\Admin\AdminSystemConfigController;
use App\Http\Controllers\Admin\AdminStudentController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminAcademicController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\CoachDashboardController;
use App\Http\Controllers\Dashboard\DashboardRedirectController;
use App\Http\Controllers\Dashboard\InstructorDashboardController;
use App\Http\Controllers\Dashboard\StudentDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PerformanceScoreController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SportApplicationController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\SportRankingController;
use App\Http\Controllers\SportStudentController;
use App\Http\Controllers\SportTeamSuggestionController;
use App\Http\Controllers\Staff\StaffAiRecommendationsHubController;
use App\Http\Controllers\Staff\StaffInjuryLogsController;
use App\Http\Controllers\Staff\StaffInjuryRecordsController;
use App\Http\Controllers\Staff\StaffPerformanceScoresHubController;
use App\Http\Controllers\Staff\StaffAcademicController;
use App\Http\Controllers\Staff\StaffEventController;
use App\Http\Controllers\Student\StudentParticipationLogsController;
use App\Http\Controllers\Student\StudentSportBrowseController;
use App\Http\Controllers\Student\StudentAcademicController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\LandingPageController;

Route::get('/', [LandingPageController::class, 'index'])->name('home');

Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware(['auth', 'org'])
    ->name('dashboard');

Route::middleware(['auth', 'org'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
});

Route::middleware(['auth', 'org'])->group(function () {
    Route::get('/admin/dashboard', AdminDashboardController::class)
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::get('/coach/dashboard', CoachDashboardController::class)
        ->middleware('role:coach')
        ->name('coach.dashboard');

    Route::get('/instructor/dashboard', InstructorDashboardController::class)
        ->middleware('role:instructor')
        ->name('instructor.dashboard');

    Route::get('/student/dashboard', StudentDashboardController::class)
        ->middleware(['role:student', 'approved'])
        ->name('student.dashboard');

    Route::get('/approval-pending', function () {
        return view('auth.approval-pending');
    })->name('approval.pending');

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/create', [AdminUserController::class, 'create'])->name('admin.users.create');
        Route::post('/admin/users', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::patch('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/admin/users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');

        Route::get('/admin/students', [AdminStudentController::class, 'index'])->name('admin.students.index');
        Route::post('/admin/students', [AdminStudentController::class, 'store'])->name('admin.students.store');
        Route::patch('/admin/students/{user}', [AdminStudentController::class, 'update'])->name('admin.students.update');
        Route::delete('/admin/students/{user}', [AdminStudentController::class, 'destroy'])->name('admin.students.destroy');

        Route::get('/admin/reports', AdminReportsController::class)->name('admin.reports.index');
        Route::get('/admin/reports/performance-scores.csv', [AdminReportsController::class, 'export'])
            ->name('admin.reports.performance_scores_csv');
        Route::get('/admin/system', [AdminSystemConfigController::class, 'index'])->name('admin.system.index');

        Route::post('/admin/system/courses', [AdminSystemConfigController::class, 'storeCourse'])->name('admin.system.courses.store');
        Route::delete('/admin/system/courses/{course}', [AdminSystemConfigController::class, 'destroyCourse'])->name('admin.system.courses.destroy');
        Route::post('/admin/system/year-levels', [AdminSystemConfigController::class, 'storeYearLevel'])->name('admin.system.years.store');
        Route::delete('/admin/system/year-levels/{yearLevel}', [AdminSystemConfigController::class, 'destroyYearLevel'])->name('admin.system.years.destroy');
        Route::post('/admin/system/sections', [AdminSystemConfigController::class, 'storeSection'])->name('admin.system.sections.store');
        Route::delete('/admin/system/sections/{section}', [AdminSystemConfigController::class, 'destroySection'])->name('admin.system.sections.destroy');

        Route::resource('admin/team-members', \App\Http\Controllers\Admin\AdminTeamMemberController::class)
            ->except(['create', 'show', 'edit'])
            ->names('admin.team_members');

        Route::get('/admin/academics', [AdminAcademicController::class, 'index'])->name('admin.academics.index');
        Route::post('/admin/academics/record', [AdminAcademicController::class, 'storeRecord'])->name('admin.academics.records.store');
        Route::post('/admin/academics/attendance', [AdminAcademicController::class, 'storeAttendance'])->name('admin.academics.attendance.store');
        Route::post('/admin/academics/review', [AdminAcademicController::class, 'storeReview'])->name('admin.academics.reviews.store');
        Route::get('/admin/academics/export', [AdminAcademicController::class, 'export'])->name('admin.academics.export');
    });

    Route::middleware('role:coach,instructor')->group(function () {
        Route::get('/staff/injury-logs', StaffInjuryLogsController::class)->name('staff.injury_logs.index');
        Route::get('/staff/injury-records', [StaffInjuryRecordsController::class, 'index'])->name('staff.injury_records.index');
        Route::post('/staff/injury-records', [StaffInjuryRecordsController::class, 'store'])->name('staff.injury_records.store');
        Route::get('/staff/performance-scores', StaffPerformanceScoresHubController::class)->name('staff.performance_scores.hub');
        Route::get('/staff/ai-recommendations', StaffAiRecommendationsHubController::class)->name('staff.ai_recommendations.hub');

        Route::get('/staff/academics', [StaffAcademicController::class, 'index'])->name('staff.academics.index');

        Route::get('/staff/events', [StaffEventController::class, 'index'])->name('staff.events.index');
        Route::get('/staff/events/create', [StaffEventController::class, 'create'])->name('staff.events.create');
        Route::post('/staff/events', [StaffEventController::class, 'store'])->name('staff.events.store');
        Route::get('/staff/events/{event}/edit', [StaffEventController::class, 'edit'])->name('staff.events.edit');
        Route::patch('/staff/events/{event}', [StaffEventController::class, 'update'])->name('staff.events.update');
        Route::get('/staff/students', [\App\Http\Controllers\Staff\CoachStudentController::class, 'index'])->name('staff.students.index');
        Route::post('/staff/students/{user}/approve', [\App\Http\Controllers\Staff\CoachStudentController::class, 'approve'])->name('staff.students.approve');
        Route::post('/staff/students/{user}/reject', [\App\Http\Controllers\Staff\CoachStudentController::class, 'reject'])->name('staff.students.reject');
    });

    Route::middleware(['role:student', 'approved'])->group(function () {
        Route::get('/student/sports', [StudentSportBrowseController::class, 'index'])->name('student.sports.index');
        Route::post('/student/sports/{sport}/apply', [StudentSportBrowseController::class, 'apply'])->name('student.sports.apply');
        Route::post('/student/sports/{sport}/withdraw', [StudentSportBrowseController::class, 'withdraw'])->name('student.sports.withdraw');
        Route::delete('/student/sports/{sport}/leave', [StudentSportBrowseController::class, 'leave'])->name('student.sports.leave');

        Route::get('/student/participation-logs', [StudentParticipationLogsController::class, 'index'])->name('student.participation_logs.index');
        Route::post('/student/participation-logs', [StudentParticipationLogsController::class, 'store'])->name('student.participation_logs.store');
        
        Route::get('/student/academics', [StudentAcademicController::class, 'index'])->name('student.academics.index');
    });

    Route::middleware('role:admin,coach,instructor')->group(function () {
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

        Route::resource('sports', SportController::class);

        Route::post('sports/{sport}/students', [SportStudentController::class, 'store'])
            ->name('sports.students.store');
        Route::delete('sports/{sport}/students/{user}', [SportStudentController::class, 'destroy'])
            ->name('sports.students.destroy');

        Route::post('sports/{sport}/applications/{application}/approve', [SportApplicationController::class, 'approve'])
            ->name('sports.applications.approve');
        Route::post('sports/{sport}/applications/{application}/reject', [SportApplicationController::class, 'reject'])
            ->name('sports.applications.reject');

        Route::get('sports/{sport}/rankings', [SportRankingController::class, 'index'])
            ->name('sports.rankings.index');

        Route::get('sports/{sport}/team-suggestions', [SportTeamSuggestionController::class, 'index'])
            ->name('sports.team_suggestions.index');
        Route::post('sports/{sport}/team-suggestions', [SportTeamSuggestionController::class, 'generate'])
            ->name('sports.team_suggestions.generate');

        Route::get('sports/{sport}/scores', [PerformanceScoreController::class, 'index'])
            ->name('sports.scores.index');
        Route::post('sports/{sport}/scores', [PerformanceScoreController::class, 'store'])
            ->name('sports.scores.store');
    });
});

require __DIR__.'/auth.php';
