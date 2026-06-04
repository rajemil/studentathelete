<?php

namespace App\Actions\Sport;

use App\Models\Sport;
use App\Models\User;

class DeleteSportAction
{
    /**
     * Execute the sport deletion action.
     *
     * @param  Sport  $sport
     * @param  User  $actor
     * @return void
     */
    public function execute(Sport $sport, User $actor): void
    {
        $sport->delete();

        activity()
            ->performedOn($sport)
            ->causedBy($actor)
            ->log('sport_deleted');
    }
}
