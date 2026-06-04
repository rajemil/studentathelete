<?php

namespace App\Providers;

use App\Models\InjuryRecord;
use App\Models\ParticipationLog;
use App\Models\PerformanceScore;
use App\Observers\AnalyticsCacheObserver;
use App\Services\AI\AiManager;
use App\Services\AI\Contracts\AiClient;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(AiClient::class, fn () => AiManager::make());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

<<<<<<< Updated upstream
        view()->composer(['layouts.sidebar', 'layouts.topbar', 'layouts.nav-mobile-links'], function ($view) {
            if (auth()->check() && in_array(auth()->user()->role, ['admin', 'coach', 'instructor'])) {
                $user = auth()->user();
                $count = \App\Models\SportApplication::where('status', 'pending')
                    ->whereHas('sport', function ($q) use ($user) {
                        $q->where('organization_id', $user->organization_id);
                        if ($user->role !== 'admin') {
                            // Filter sports managed by coach
                            $assignedSportIds = $user->sports()->pluck('sports.id');
                            $teamSportIds = \App\Models\Team::whereIn('id', \App\Support\CoachedTeams::teamIds($user))->pluck('sport_id');
                            $q->whereIn('id', $assignedSportIds->merge($teamSportIds)->unique());
                        }
                    })
                    ->count();
                $view->with('pendingApplicationsCount', $count);
            }
        });
=======
        $observer = AnalyticsCacheObserver::class;
        PerformanceScore::observe($observer);
        InjuryRecord::observe($observer);
        ParticipationLog::observe($observer);
>>>>>>> Stashed changes
    }
}
