<?php

namespace Tests\Feature;

use App\Mail\StudentWelcomeMail;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminStudentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_open_students_index(): void
    {
        $org = Organization::query()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'organization_id' => $org->id]);

        $this->actingAs($admin)->get(route('admin.students.index'))->assertOk();
    }

    public function test_non_admin_cannot_access_students_index(): void
    {
        $org = Organization::query()->firstOrFail();
        $coach = User::factory()->create(['role' => 'coach', 'organization_id' => $org->id]);

        $this->actingAs($coach)->get(route('admin.students.index'))->assertForbidden();
    }

    public function test_admin_can_create_student(): void
    {
        Mail::fake();

        $org = Organization::query()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'organization_id' => $org->id]);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'name' => 'Test Athlete',
            'email' => 'athlete@test.example',
        ]);

        $response->assertRedirect(route('admin.students.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'athlete@test.example',
            'role' => 'student',
            'organization_id' => $org->id,
        ]);

        Mail::assertQueued(StudentWelcomeMail::class, function (StudentWelcomeMail $mail): bool {
            return strlen($mail->plainAccessCode) === 6
                && preg_match('/^[A-Z0-9]{6}$/', $mail->plainAccessCode) === 1;
        });
    }
}
