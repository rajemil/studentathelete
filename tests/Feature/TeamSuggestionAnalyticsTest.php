<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\PerformanceScore;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use App\Services\Team\TeamSuggestionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamSuggestionAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_suggestions_include_confidence_and_explanation(): void
    {
        $org = Organization::query()->firstOrFail();
        $coach = User::factory()->create(['role' => 'coach', 'organization_id' => $org->id]);

        $sport = Sport::create([
            'organization_id' => $org->id,
            'name' => 'Basketball',
            'slug' => 'basketball-suggest',
        ]);

        $team = Team::create([
            'organization_id' => $org->id,
            'sport_id' => $sport->id,
            'name' => 'Varsity BB',
            'primary_coach_id' => $coach->id,
        ]);

        $students = User::factory()->count(6)->create([
            'role' => 'student',
            'organization_id' => $org->id,
        ]);

        foreach ($students as $i => $student) {
            $sport->students()->attach($student->id);
            $team->students()->attach($student->id, ['joined_on' => now()->toDateString()]);
            PerformanceScore::query()->create([
                'user_id' => $student->id,
                'sport_id' => $sport->id,
                'score' => 60 + $i * 5,
                'scored_on' => now()->subDays($i + 1)->toDateString(),
            ]);
        }

        $service = app(TeamSuggestionService::class);
        $result = $service->generate(collect($students), $sport, 'strongest', 2, 3);

        $this->assertCount(2, $result['teams']);
        $this->assertArrayHasKey('confidence_score', $result['teams'][0]);
        $this->assertArrayHasKey('explanation', $result['teams'][0]);
        $this->assertNotEmpty($result['teams'][0]['members'][0]['explanation'] ?? '');
        $this->assertNotEmpty($result['win_probabilities']);

        $response = $this->actingAs($coach)->post(route('sports.team_suggestions.generate', $sport), [
            'mode' => 'balanced',
            'team_count' => 2,
            'team_size' => 3,
        ]);

        $response->assertOk();
        $response->assertSee('Balanced Team', false);
        $response->assertSee('predictive analytics', false);
    }

    public function test_staff_can_schedule_intramurals_event(): void
    {
        $org = Organization::query()->firstOrFail();
        $coach = User::factory()->create(['role' => 'coach', 'organization_id' => $org->id]);
        $sport = Sport::create(['organization_id' => $org->id, 'name' => 'Volleyball', 'slug' => 'vb']);
        $team = Team::create([
            'organization_id' => $org->id,
            'sport_id' => $sport->id,
            'name' => 'Varsity',
            'primary_coach_id' => $coach->id,
        ]);

        $response = $this->actingAs($coach)->post(route('staff.events.store'), [
            'title' => 'Local Intramurals Finals',
            'event_type' => 'intramurals',
            'team_id' => $team->id,
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->addHours(2)->format('Y-m-d H:i:s'),
        ]);

        $response->assertRedirect(route('staff.events.index'));
        $this->assertDatabaseHas('events', [
            'title' => 'Local Intramurals Finals',
            'event_type' => 'intramurals',
        ]);
    }
}
