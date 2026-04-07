<?php

use App\Http\Controllers\Dashboard\DashboardRedirectController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\CoachDashboardController;
use App\Http\Controllers\Dashboard\StudentDashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SportController;
use App\Http\Controllers\SportRankingController;
use App\Http\Controllers\SportStudentController;
use App\Http\Controllers\SportTeamSuggestionController;
use App\Http\Controllers\AnalyticsController;
use App\Http\Controllers\PerformanceScoreController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/{id}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/admin/dashboard', AdminDashboardController::class)
        ->middleware('role:admin')
        ->name('admin.dashboard');

    Route::get('/coach/dashboard', CoachDashboardController::class)
        ->middleware('role:coach')
        ->name('coach.dashboard');

    Route::get('/student/dashboard', StudentDashboardController::class)
        ->middleware('role:student')
        ->name('student.dashboard');

    Route::middleware('role:admin,coach')->group(function () {
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
