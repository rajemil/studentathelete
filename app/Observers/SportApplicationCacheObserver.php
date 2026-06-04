<?php

namespace App\Observers;

use App\Models\Sport;
use App\Models\SportApplication;
use App\Services\Staff\PendingSportApplicationsCount;

class SportApplicationCacheObserver
{
    public function saved(SportApplication $application): void
    {
        $this->flush($application);
    }

    public function deleted(SportApplication $application): void
    {
        $this->flush($application);
    }

    private function flush(SportApplication $application): void
    {
        $orgId = Sport::query()->whereKey($application->sport_id)->value('organization_id');
        if ($orgId !== null) {
            PendingSportApplicationsCount::forgetForOrganization((int) $orgId);
        }
    }
}
