<?php

namespace App\Actions\Sport;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Support\Str;

class UpdateSportAction
{
    /**
     * Execute the sport update action.
     *
     * @param  Sport  $sport
     * @param  array  $data
     * @param  User  $actor
     * @return Sport
     */
    public function execute(Sport $sport, array $data, User $actor): Sport
    {
        $sport->update([
            'name' => $data['name'],
            'slug' => $data['slug'] ?? Str::slug($data['name']),
            'description' => $data['description'] ?? null,
            'qual_min_age' => $data['qual_min_age'] ?? null,
            'qual_max_age' => $data['qual_max_age'] ?? null,
            'qual_min_height_cm' => $data['qual_min_height_cm'] ?? null,
            'qual_allowed_genders' => isset($data['qual_genders']) && count($data['qual_genders']) > 0
                ? array_values(array_unique($data['qual_genders']))
                : null,
        ]);

        activity()
            ->performedOn($sport)
            ->causedBy($actor)
            ->log('sport_updated');

        return $sport;
    }
}
