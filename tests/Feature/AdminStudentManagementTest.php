<?php

namespace Tests\Feature;

use App\Mail\StudentWelcomeMail;
use App\Models\Organization;
use App\Models\Sport;
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

    public function test_admin_can_create_student_with_password(): void
    {
        Mail::fake();

        $org = Organization::query()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'organization_id' => $org->id]);

        $sport = Sport::query()->create([
            'name' => 'Soccer',
            'slug' => 'soccer-'.uniqid(),
            'organization_id' => $org->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Test',
            'last_name' => 'Athlete',
            'email' => 'athlete@test.example',
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
            'birthdate' => '2005-01-15',
            'gender' => 'male',
            'address' => '123 Campus Way',
            'course' => 'BS Computer Science',
            'height_cm' => 175,
            'weight_kg' => 70,
            'sport_ids' => [$sport->id],
        ]);

        $response->assertRedirect(route('admin.students.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'athlete@test.example',
            'role' => 'student',
            'name' => 'Test Athlete',
        ]);

        $user = User::query()->where('email', 'athlete@test.example')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->email_verified_at);

        $this->assertDatabaseHas('profiles', [
            'user_id' => $user->id,
            'course' => 'BS Computer Science',
        ]);

        Mail::assertSent(StudentWelcomeMail::class);
    }
}
