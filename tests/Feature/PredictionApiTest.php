<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_can_generate_predictions_and_team_endpoints(): void
    {
        $orgId = Organization::defaultId();

        $coach = User::factory()->create(['role' => 'coach', 'organization_id' => $orgId]);
        $studentA = User::factory()->create(['role' => 'student', 'organization_id' => $orgId]);
        $studentB = User::factory()->create(['role' => 'student', 'organization_id' => $orgId]);

        $sport = Sport::query()->create([
            'organization_id' => $orgId,
            'name' => 'Basketball',
            'slug' => 'basketball',
            'description' => null,
        ]);

        $team = Team::query()->create([
            'organization_id' => $orgId,
            'name' => 'Varsity',
            'sport_id' => $sport->id,
            'primary_coach_id' => $coach->id,
        ]);

        $team->students()->attach([
            $studentA->id => ['rank' => 1],
            $studentB->id => ['rank' => 2],
        ]);

        // athlete prediction (coach can view coached athletes in their org)
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

    public function test_coach_cannot_view_predictions_for_unassigned_student(): void
    {
        $orgId = Organization::defaultId();

        $coach = User::factory()->create(['role' => 'coach', 'organization_id' => $orgId]);
        $student = User::factory()->create(['role' => 'student', 'organization_id' => $orgId]);

        Sport::query()->create([
            'organization_id' => $orgId,
            'name' => 'Soccer',
            'slug' => 'soccer',
            'description' => null,
        ]);

        $this->actingAs($coach)
            ->getJson('/api/predictions/athletes/'.$student->id)
            ->assertForbidden();
    }
}
