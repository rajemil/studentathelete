<?php

use App\Http\Controllers\Api\PredictionController;
use App\Http\Controllers\Api\PredictiveAnalyticsController;
use App\Http\Controllers\Api\LandingDataController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'org'])->group(function () {
    Route::prefix('predictions')->group(function () {
        Route::get('/athlete', [PredictiveAnalyticsController::class, 'athletePrediction']);
        Route::get('/team', [PredictiveAnalyticsController::class, 'teamPrediction'])
            ->middleware('role:admin,coach,instructor');
    });

    Route::get('predictions/athletes/{user}', [PredictionController::class, 'athlete']);
    Route::get('predictions/athletes/{user}/recommendations', [PredictionController::class, 'recommendations']);

    Route::middleware(['role:admin,coach,instructor'])->group(function () {
        Route::post('predictions/teams/win-probability', [PredictionController::class, 'teamWinProbability']);
        Route::post('predictions/teams/strongest-lineup', [PredictionController::class, 'strongestLineup']);
    });
});

Route::get('/dashboard/landing-data', [LandingDataController::class, 'index'])->name('api.landing.data');