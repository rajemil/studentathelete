<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentSportBrowseTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_join_and_leave_a_sport(): void
    {
        $orgId = Organization::defaultId();

        $sport = Sport::query()->create([
            'organization_id' => $orgId,
            'name' => 'Soccer',
            'slug' => 'soccer',
            'description' => null,
        ]);

        $student = User::factory()->create(['role' => 'student', 'organization_id' => $orgId]);

        $this->actingAs($student)
            ->post(route('student.sports.join', $sport))
            ->assertRedirect(route('student.sports.index'));

        $this->assertTrue($student->fresh()->sports->contains($sport));

        $this->actingAs($student)
            ->delete(route('student.sports.leave', $sport))
            ->assertRedirect(route('student.sports.index'));

        $this->assertFalse($student->fresh()->sports->contains($sport));
    }
}
