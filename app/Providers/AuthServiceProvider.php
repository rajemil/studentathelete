<?php

namespace App\Providers;

use App\Models\InjuryRecord;
use App\Models\ParticipationLog;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use App\Policies\InjuryRecordPolicy;
use App\Policies\ParticipationLogPolicy;
use App\Policies\SportPolicy;
use App\Policies\TeamPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        InjuryRecord::class => InjuryRecordPolicy::class,
        ParticipationLog::class => ParticipationLogPolicy::class,
        Sport::class => SportPolicy::class,
        Team::class => TeamPolicy::class,
        User::class => UserPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
