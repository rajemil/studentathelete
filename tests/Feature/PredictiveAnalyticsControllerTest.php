<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PredictiveAnalyticsControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_thesis_aligned_prediction_routes_respond(): void
    {
        $orgId = Organization::defaultId();
        $coach = User::factory()->create(['role' => 'coach', 'organization_id' => $orgId]);
        $studentA = User::factory()->create(['role' => 'student', 'organization_id' => $orgId]);
        $studentB = User::factory()->create(['role' => 'student', 'organization_id' => $orgId]);

        $sport = Sport::query()->create([
            'organization_id' => $orgId,
            'name' => 'Volleyball',
            'slug' => 'volleyball-predict',
        ]);

        $team = Team::query()->create([
            'organization_id' => $orgId,
            'sport_id' => $sport->id,
            'name' => 'Varsity VB',
            'primary_coach_id' => $coach->id,
        ]);

        $team->students()->attach([
            $studentA->id => ['rank' => 1],
            $studentB->id => ['rank' => 2],
        ]);

        $athlete = $this->actingAs($coach)->getJson('/api/predictions/athlete?user_id='.$studentA->id.'&sport_id='.$sport->id);
        $athlete->assertOk()->assertJsonStructure([
            'athlete' => ['id', 'name'],
            'prediction' => ['predicted_score', 'confidence', 'trend'],
        ]);

        $teamPred = $this->actingAs($coach)->getJson('/api/predictions/team?'.http_build_query([
            'sport_id' => $sport->id,
            'team_a_user_ids' => [$studentA->id],
            'team_b_user_ids' => [$studentB->id],
        ]));

        $teamPred->assertOk()->assertJsonStructure([
            'team_a' => ['strength', 'win_probability', 'members'],
            'team_b' => ['strength', 'win_probability', 'members'],
        ]);
    }
}
