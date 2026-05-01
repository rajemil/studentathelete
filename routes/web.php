<?php

use App\Http\Controllers\Admin\AdminReportsController;
use App\Http\Controllers\Admin\AdminSystemConfigController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\CoachDashboardController;
use App\Http\Controllers\Dashboard\DashboardRedirectController;
use App\Http\Controllers\Dashboard\InstructorDashboardController;
use App\Http\Controllers\Dashboard\StudentDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PerformanceScoreController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\SportRankingController;
use App\Http\Controllers\SportStudentController;
use App\Http\Controllers\SportTeamSuggestionController;
use App\Http\Controllers\Staff\StaffAiRecommendationsHubController;
use App\Http\Controllers\Staff\StaffInjuryLogsController;
use App\Http\Controllers\Staff\StaffInjuryRecordsController;
use App\Http\Controllers\Staff\StaffPerformanceScoresHubController;
use App\Http\Controllers\Student\StudentParticipationLogsController;
use App\Http\Controllers\Student\StudentSportBrowseController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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
        ->middleware('role:student')
        ->name('student.dashboard');

    Route::middleware('role:admin')->group(function () {
        Route::get('/admin/users', [AdminUserController::class, 'index'])->name('admin.users.index');
        Route::get('/admin/users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
        Route::patch('/admin/users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::get('/admin/reports', AdminReportsController::class)->name('admin.reports.index');
        Route::get('/admin/reports/performance-scores.csv', [AdminReportsController::class, 'export'])
            ->name('admin.reports.performance_scores_csv');
        Route::get('/admin/system', AdminSystemConfigController::class)->name('admin.system.index');
    });

    Route::middleware('role:coach,instructor')->group(function () {
        Route::get('/staff/injury-logs', StaffInjuryLogsController::class)->name('staff.injury_logs.index');
        Route::get('/staff/injury-records', [StaffInjuryRecordsController::class, 'index'])->name('staff.injury_records.index');
        Route::post('/staff/injury-records', [StaffInjuryRecordsController::class, 'store'])->name('staff.injury_records.store');
        Route::get('/staff/performance-scores', StaffPerformanceScoresHubController::class)->name('staff.performance_scores.hub');
        Route::get('/staff/ai-recommendations', StaffAiRecommendationsHubController::class)->name('staff.ai_recommendations.hub');
    });

    Route::middleware('role:student')->group(function () {
        Route::get('/student/sports', [StudentSportBrowseController::class, 'index'])->name('student.sports.index');
        Route::post('/student/sports/{sport}/join', [StudentSportBrowseController::class, 'join'])->name('student.sports.join');
        Route::delete('/student/sports/{sport}/leave', [StudentSportBrowseController::class, 'leave'])->name('student.sports.leave');

        Route::get('/student/participation-logs', [StudentParticipationLogsController::class, 'index'])->name('student.participation_logs.index');
        Route::post('/student/participation-logs', [StudentParticipationLogsController::class, 'store'])->name('student.participation_logs.store');
    });

    Route::middleware('role:admin,coach,instructor')->group(function () {
        Route::get('/analytics', [AnalyticsController::class, 'index'])->name('analytics.index');

        Route::resource('sports', SportController::class);

        Route::post('sports/{sport}/students', [SportStudentController::class, 'store'])
            ->name('sports.students.store');
        Route::delete('sports/{sport}/students/{user}', [SportStudentController::class, 'destroy'])
            ->name('sports.students.destroy');

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
