<?php

namespace Database\Factories;

use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TeamMember>
 */
class TeamMemberFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organization_id' => Organization::first()?->id ?? Organization::factory(),
            'name'            => $this->faker->name(),
            'role'            => $this->faker->randomElement([
                'Programmer, Documentator',
                'Presenter, Documentator',
                'Analyst',
                'Coach',
                'Manager',
            ]),
            'image_path'      => null,
            'description'     => $this->faker->sentence(12),
        ];
    }
}
