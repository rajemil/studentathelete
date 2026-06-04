<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Sport;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffAcademicAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_coach_can_access_staff_academics_index_for_rostered_students(): void
    {
        $org = Organization::query()->firstOrFail();

        $coach = User::factory()->create(['role' => 'coach', 'organization_id' => $org->id]);
        $student = User::factory()->create(['role' => 'student', 'organization_id' => $org->id]);

        $sport = Sport::query()->create([
            'name' => 'Test Sport',
            'slug' => 'test-sport-'.uniqid(),
            'organization_id' => $org->id,
        ]);

        $team = Team::query()->create([
            'name' => 'Team A',
            'sport_id' => $sport->id,
            'organization_id' => $org->id,
            'primary_coach_id' => $coach->id,
        ]);

        $student->teams()->attach($team->id, [
            'rank' => 1,
            'joined_on' => now()->toDateString(),
        ]);

        $response = $this->actingAs($coach)->get(route('staff.academics.index'));

        $response->assertOk();
        $response->assertSee($student->name, false);
    }
}
