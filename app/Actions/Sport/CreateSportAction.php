<?php

namespace App\Actions\Sport;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Support\Str;

class CreateSportAction
{
    /**
     * Execute the sport creation action.
     *
     * @param  array  $data
     * @param  int  $orgId
     * @param  User  $actor
     * @return Sport
     */
    public function execute(array $data, int $orgId, User $actor): Sport
    {
        $slug = $data['slug'] ?? Str::slug($data['name']);

        $sport = Sport::create([
            'organization_id' => $orgId,
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'] ?? null,
        ]);

        activity()
            ->performedOn($sport)
            ->causedBy($actor)
            ->log('sport_created');

        return $sport;
    }
}
