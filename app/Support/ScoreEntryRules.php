<?php

namespace App\Support;

use App\Models\Sport;
use App\Models\User;

final class ScoreEntryRules
{
    public static function requesterMayEnterScoreFor(User $actor, User $student, Sport $sport): bool
    {
        return RosterAccess::actorMayEnterScoreFor($actor, $student, $sport);
    }
}
