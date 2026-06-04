<?php

namespace App\Actions\Injury;

use App\Models\InjuryRecord;
use App\Models\Sport;
use App\Models\User;
use Carbon\CarbonImmutable;

class RecordInjuryAction
{
    /**
     * Record a new injury entry for an athlete.
     *
     * @param  User  $athlete
     * @param  Sport|null  $sport
     * @param  array  $data
     * @param  User  $actor
     * @param  int  $orgId
     * @return InjuryRecord
     */
    public function execute(User $athlete, ?Sport $sport, array $data, User $actor, int $orgId): InjuryRecord
    {
        $record = InjuryRecord::query()->create([
            'organization_id' => $orgId,
            'athlete_user_id' => $athlete->id,
            'reported_by_user_id' => $actor->id,
            'sport_id' => $sport?->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'status' => $data['status'],
            'occurred_on' => CarbonImmutable::parse($data['occurred_on'])->toDateString(),
        ]);

        activity()
            ->performedOn($record)
            ->causedBy($actor)
            ->withProperties(['athlete_user_id' => $athlete->id, 'sport_id' => $sport?->id])
            ->log('injury_record_created');

        return $record;
    }
}
