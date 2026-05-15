<?php

namespace Tests\Feature;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SportsFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_can_manage_sport_assign_students_and_enter_scores(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $coach = User::factory()->create(['role' => 'coach', 'organization_id' => $admin->organization_id]);
        $student = User::factory()->create(['role' => 'student', 'organization_id' => $admin->organization_id]);

        // Admin creates sport
        $this->actingAs($admin)->post('/sports', [
            'name' => 'Volleyball',
            'slug' => '',
            'description' => 'Team sport',
        ])->assertStatus(302);

        $sport = Sport::query()->where('name', 'Volleyball')->firstOrFail();
        
        // Assign coach to sport
        $coach->sports()->attach($sport->id);

        // Coach manages sport (Assign student)
        $assign = $this->actingAs($coach)->post(route('sports.students.store', $sport), [
            'user_id' => $student->id,
        ]);
        $assign->assertStatus(302);

        // Scores page loads
        $scoresPage = $this->actingAs($coach)->get(route('sports.scores.index', $sport));
        $scoresPage->assertOk();

        // Enter score
        $storeScore = $this->actingAs($coach)->post(route('sports.scores.store', $sport), [
            'user_id' => $student->id,
            'category' => 'overall',
            'score' => 80,
            'scored_on' => Carbon::now()->toDateString(),
        ]);
        $storeScore->assertStatus(302);

        $this->assertDatabaseHas('performance_scores', [
            'user_id' => $student->id,
            'sport_id' => $sport->id,
            'category' => 'overall',
        ]);

        // Rankings and team suggestions should load
        $this->actingAs($coach)->get(route('sports.rankings.index', $sport))->assertOk();
        $this->actingAs($coach)->get(route('sports.team_suggestions.index', $sport))->assertOk();
    }
}
