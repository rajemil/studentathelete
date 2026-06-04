<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\Organization;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffEventManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_can_view_and_create_event_for_coached_team(): void
    {
        $org = Organization::query()->firstOrFail();
        $coach = User::factory()->create(['role' => 'coach', 'organization_id' => $org->id]);
        $student = User::factory()->create(['role' => 'student', 'organization_id' => $org->id]);

        $sport = Sport::create([
            'organization_id' => $org->id,
            'name' => 'Soccer',
            'slug' => 'soccer',
        ]);

        $team = Team::create([
            'organization_id' => $org->id,
            'sport_id' => $sport->id,
            'name' => 'Varsity Soccer',
            'primary_coach_id' => $coach->id,
        ]);

        // Enroll student in team
        $team->students()->attach($student->id, ['joined_on' => now()]);

        // Check index page
        $response = $this->actingAs($coach)->get(route('staff.events.index'));
        $response->assertOk();

        // Check create page
        $response = $this->actingAs($coach)->get(route('staff.events.create'));
        $response->assertOk();

        // Post store request
        $response = $this->actingAs($coach)->post(route('staff.events.store'), [
            'title' => 'Morning Conditioning',
            'description' => 'Bring running shoes.',
            'event_type' => 'training',
            'team_id' => $team->id,
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
            'location' => 'Field A',
        ]);

        $response->assertRedirect(route('staff.events.index'));

        // Assert database has event
        $this->assertDatabaseHas('events', [
            'title' => 'Morning Conditioning',
            'team_id' => $team->id,
            'sport_id' => $sport->id,
            'location' => 'Field A',
            'created_by' => $coach->id,
        ]);

        $event = Event::where('title', 'Morning Conditioning')->firstOrFail();

        // Assert student and coach are participants
        $this->assertDatabaseHas('event_participants', [
            'event_id' => $event->id,
            'user_id' => $student->id,
            'participant_role' => 'student',
        ]);

        $this->assertDatabaseHas('event_participants', [
            'event_id' => $event->id,
            'user_id' => $coach->id,
            'participant_role' => 'coach',
        ]);
    }

    public function test_coach_cannot_create_event_for_uncoached_team(): void
    {
        $org = Organization::query()->firstOrFail();
        $coach = User::factory()->create(['role' => 'coach', 'organization_id' => $org->id]);
        $otherCoach = User::factory()->create(['role' => 'coach', 'organization_id' => $org->id]);

        $sport = Sport::create([
            'organization_id' => $org->id,
            'name' => 'Soccer',
            'slug' => 'soccer',
        ]);

        $team = Team::create([
            'organization_id' => $org->id,
            'sport_id' => $sport->id,
            'name' => 'Varsity Soccer',
            'primary_coach_id' => $otherCoach->id,
        ]);

        // Post store request using unassigned coach
        $response = $this->actingAs($coach)->post(route('staff.events.store'), [
            'title' => 'Morning Conditioning',
            'event_type' => 'training',
            'team_id' => $team->id,
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ]);

        // Expect 404/403 since they are not assigned to this team
        $response->assertStatus(404); // findOrFail on restricted query fails with 404 model not found
    }

    public function test_coach_cannot_interact_with_cross_tenant_team(): void
    {
        $orgA = Organization::query()->firstOrFail();
        $orgB = Organization::create(['name' => 'Org B', 'slug' => 'org-b']);

        $coachA = User::factory()->create(['role' => 'coach', 'organization_id' => $orgA->id]);

        $sportB = Sport::create([
            'organization_id' => $orgB->id,
            'name' => 'Soccer B',
            'slug' => 'soccer-b',
        ]);

        $teamB = Team::create([
            'organization_id' => $orgB->id,
            'sport_id' => $sportB->id,
            'name' => 'Varsity Soccer B',
            'primary_coach_id' => $coachA->id, // theoretically assigned, but cross-tenant
        ]);

        $response = $this->actingAs($coachA)->post(route('staff.events.store'), [
            'title' => 'Cross Tenant Event',
            'event_type' => 'training',
            'team_id' => $teamB->id,
            'starts_at' => now()->addDay()->format('Y-m-d H:i:s'),
            'ends_at' => now()->addDay()->addHour()->format('Y-m-d H:i:s'),
        ]);

        $response->assertStatus(404);
    }
}
