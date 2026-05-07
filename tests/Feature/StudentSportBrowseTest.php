<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Sport;
use App\Models\User;
use App\Notifications\SportApplicationSubmitted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StudentSportBrowseTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_apply_withdraw_and_leave(): void
    {
        $orgId = Organization::defaultId();

        $sport = Sport::query()->create([
            'organization_id' => $orgId,
            'name' => 'Soccer',
            'slug' => 'soccer',
            'description' => null,
        ]);

        $instructor = User::factory()->create(['role' => 'instructor', 'organization_id' => $orgId]);
        $sport->update(['instructor_user_id' => $instructor->id]);

        $student = User::factory()->create(['role' => 'student', 'organization_id' => $orgId]);

        Notification::fake();

        $this->actingAs($student)
            ->post(route('student.sports.apply', $sport), ['student_message' => 'I want to join.'])
            ->assertRedirect(route('student.sports.index'));

        $this->assertDatabaseHas('sport_applications', [
            'sport_id' => $sport->id,
            'user_id' => $student->id,
            'status' => 'pending',
        ]);

        Notification::assertSentTo($instructor, SportApplicationSubmitted::class);

        $this->actingAs($student)
            ->post(route('student.sports.withdraw', $sport))
            ->assertRedirect(route('student.sports.index'));

        $this->assertDatabaseHas('sport_applications', [
            'sport_id' => $sport->id,
            'user_id' => $student->id,
            'status' => 'withdrawn',
        ]);

        $student->sports()->syncWithoutDetaching([$sport->id]);

        $this->actingAs($student)
            ->delete(route('student.sports.leave', $sport))
            ->assertRedirect(route('student.sports.index'));

        $this->assertFalse($student->fresh()->sports->contains($sport));
    }
}
