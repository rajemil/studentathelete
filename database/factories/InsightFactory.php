<?php

namespace Database\Factories;

use App\Models\Insight;
use App\Models\Organization;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Insight>
 */
class InsightFactory extends Factory
{
    protected $model = Insight::class;

    public function definition(): array
    {
        return [
            'hash_key' => sha1(uniqid('', true)),
            'organization_id' => fn () => Organization::factory(),
            'user_id' => null,
            'sport_id' => null,
            'team_id' => null,
            'type' => $this->faker->randomElement([
                'performance_improved',
                'stamina_decreasing',
                'top_performer_week',
                'at_risk_injury',
            ]),
            'severity' => $this->faker->randomElement(['info', 'success', 'warning', 'danger']),
            'title' => $this->faker->sentence(3),
            'message' => $this->faker->sentence(8),
            'payload' => null,
            'computed_at' => Carbon::now(),
        ];
    }
}
