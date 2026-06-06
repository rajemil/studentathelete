<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\Section;
use App\Models\User;
use App\Models\YearLevel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('TEST USER', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_student_can_update_profile_with_athlete_fields(): void
    {
        $user = User::factory()->create(['role' => 'student']);
        $orgId = $user->organization_id;
        
        $c1 = Course::create(['organization_id' => $orgId, 'name' => 'Old Course']);
        $c2 = Course::create(['organization_id' => $orgId, 'name' => 'BS Athletics']);
        
        $y1 = YearLevel::create(['organization_id' => $orgId, 'name' => 'Year 1']);
        $y2 = YearLevel::create(['organization_id' => $orgId, 'name' => 'Year 2']);
        
        $s1 = Section::create(['organization_id' => $orgId, 'name' => 'A']);
        $s2 = Section::create(['organization_id' => $orgId, 'name' => 'B']);

        Profile::query()->create([
            'user_id' => $user->id,
            'birthdate' => '2000-01-01',
            'gender' => 'male',
            'approval_status' => 'approved',
            'address' => 'Old Address',
            'course_id' => $c1->id,
            'year_level_id' => $y1->id,
            'section_id' => $s1->id,
            'height_cm' => 170,
            'weight_kg' => 65,
        ]);

        $response = $this->actingAs($user)->patch('/profile', [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => $user->email,
            'birthdate' => '2001-02-02',
            'gender' => 'female',
            'address' => 'New Address',
            'course_id' => $c2->id,
            'year_level_id' => $y2->id,
            'section_id' => $s2->id,
            'height_cm' => 172,
            'weight_kg' => 68,
        ]);

        $response->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('JANE DOE', $user->name);
        $this->assertSame($c2->id, $user->profile->course_id);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
