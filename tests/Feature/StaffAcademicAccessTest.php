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

        $sport = Sport::query()->create([
            'name' => 'Test Sport',
            'slug' => 'test-sport-'.uniqid(),
            'organization_id' => $org->id,
        ]);

        $coach = User::factory()->create(['role' => 'coach', 'organization_id' => $org->id, 'sport_id' => $sport->id]);
        $student = User::factory()->create(['role' => 'student', 'organization_id' => $org->id]);

        $student->sports()->attach($sport->id);

        $response = $this->actingAs($coach)->get(route('staff.academics.index'));

        $response->assertOk();
        $response->assertSee($student->name, false);
    }
}
