<?php

namespace App\Providers;

use App\Models\InjuryRecord;
use App\Models\User;
use App\Models\ParticipationLog;
use App\Models\PerformanceScore;
use App\Models\SportApplication;
use App\Observers\AnalyticsCacheObserver;
use App\Observers\SportApplicationCacheObserver;
use App\Services\AI\AiManager;
use App\Services\AI\Contracts\AiClient;
use App\Services\Staff\PendingSportApplicationsCount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
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

        // Once per page (layouts.app), not per sidebar/topbar/mobile include — avoids 3× heavy queries.
        View::composer('layouts.app', function (): void {
            $user = Auth::user();
            if (! $user instanceof User) {
                return;
            }

            View::share(
                'pendingApplicationsCount',
                PendingSportApplicationsCount::forUser($user),
            );
        });

        $observer = AnalyticsCacheObserver::class;
        PerformanceScore::observe($observer);
        InjuryRecord::observe($observer);
        ParticipationLog::observe($observer);
        SportApplication::observe(SportApplicationCacheObserver::class);
    }
}
