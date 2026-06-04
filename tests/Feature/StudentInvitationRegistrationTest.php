<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Sport;
use App\Models\User;
use App\Notifications\StudentInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StudentInvitationRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_student_create_stores_invitation_token(): void
    {
        Notification::fake();

        $org = Organization::query()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'organization_id' => $org->id]);
        $sport = Sport::query()->create([
            'organization_id' => $org->id,
            'name' => 'Soccer',
            'slug' => 'soccer-invite',
        ]);

        $this->actingAs($admin)->post(route('admin.students.store'), [
            'first_name' => 'Invited',
            'last_name' => 'Student',
            'email' => 'invited.student@example.com',
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
            'birthdate' => '2005-01-01',
            'gender' => 'male',
            'address' => 'Campus',
            'course' => 'BS PE',
            'height_cm' => 175,
            'weight_kg' => 70,
            'sport_ids' => [$sport->id],
        ])->assertRedirect(route('admin.students.index'));

        $user = User::withoutGlobalScopes()->where('email', 'invited.student@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->invitation_token);
        $this->assertNotNull($user->invited_at);

        Notification::assertSentTo($user, StudentInvitationNotification::class);
    }

    public function test_invitation_registration_completes_and_clears_token(): void
    {
        $org = Organization::query()->firstOrFail();
        $sport = Sport::query()->create([
            'organization_id' => $org->id,
            'name' => 'Tennis',
            'slug' => 'tennis-invite',
        ]);

        $token = str_repeat('a', 64);
        $user = User::factory()->unverified()->create([
            'organization_id' => $org->id,
            'role' => 'student',
            'email' => 'token.student@example.com',
            'name' => 'Token Student',
            'invitation_token' => $token,
            'invited_at' => now(),
        ]);

        $response = $this->post('/register/student', [
            'invitation_token' => $token,
            'first_name' => 'Token',
            'last_name' => 'Student',
            'email' => 'token.student@example.com',
            'password' => 'NewSecure1!',
            'password_confirmation' => 'NewSecure1!',
            'birthdate' => '2004-05-05',
            'gender' => 'female',
            'address' => 'Dorm',
            'course' => 'BS Athletics',
            'height_cm' => 168,
            'weight_kg' => 60,
            'sports_interested' => [$sport->id],
        ]);

        $response->assertRedirect(route('verification.notice'));

        $user->refresh();
        $this->assertNull($user->invitation_token);
    }

    public function test_standard_registration_without_token_still_works(): void
    {
        $org = Organization::query()->firstOrFail();
        $sport = Sport::query()->create([
            'organization_id' => $org->id,
            'name' => 'Swim',
            'slug' => 'swim-open',
        ]);

        $response = $this->post('/register/student', [
            'first_name' => 'Open',
            'last_name' => 'Register',
            'email' => 'open.register@example.com',
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
            'birthdate' => '2005-06-15',
            'gender' => 'male',
            'address' => 'Test Address',
            'course' => 'BS Sports Science',
            'height_cm' => 170,
            'weight_kg' => 65,
            'sports_interested' => [$sport->id],
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertDatabaseHas('users', ['email' => 'open.register@example.com']);
    }
}
