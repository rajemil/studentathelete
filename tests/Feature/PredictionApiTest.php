<?php

namespace Tests\Feature;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_can_generate_predictions_and_team_endpoints(): void
    {
        $coach = User::factory()->create(['role' => 'coach']);
        $studentA = User::factory()->create(['role' => 'student']);
        $studentB = User::factory()->create(['role' => 'student']);

        $sport = Sport::query()->create([
            'name' => 'Basketball',
            'slug' => 'basketball',
            'description' => null,
        ]);

        // athlete prediction (coach can view any athlete)
        $pred = $this->actingAs($coach)->getJson('/api/predictions/athletes/'.$studentA->id.'?sport_id='.$sport->id.'&horizon_days=14');
        $pred->assertOk()->assertJsonStructure([
            'athlete' => ['id', 'name'],
            'sport' => ['id', 'name'],
            'prediction' => ['predicted_score', 'confidence', 'trend', 'inputs'],
        ]);

        // recommendations
        $rec = $this->actingAs($coach)->getJson('/api/predictions/athletes/'.$studentA->id.'/recommendations?sport_id='.$sport->id);
        $rec->assertOk()->assertJsonStructure([
            'recommendations' => [
                'training',
                'strategy',
                'best_preparation_date',
                'rationale',
                'prediction',
            ],
        ]);

        // win probability
        $wp = $this->actingAs($coach)->postJson('/api/predictions/teams/win-probability', [
            'sport_id' => $sport->id,
            'team_a_user_ids' => [$studentA->id],
            'team_b_user_ids' => [$studentB->id],
        ]);
        $wp->assertOk()->assertJsonStructure([
            'team_a' => ['strength', 'win_probability', 'members'],
            'team_b' => ['strength', 'win_probability', 'members'],
        ]);

        // strongest lineup
        $lu = $this->actingAs($coach)->postJson('/api/predictions/teams/strongest-lineup', [
            'sport_id' => $sport->id,
            'candidate_user_ids' => [$studentA->id, $studentB->id],
            'lineup_size' => 1,
        ]);
        $lu->assertOk()->assertJsonStructure([
            'lineup_size',
            'lineup_strength',
            'lineup',
        ]);

    }
}
