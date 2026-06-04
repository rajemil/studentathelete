<?php

namespace Tests\Feature\Auth;

use App\Models\Organization;
use App\Models\Profile;
use App\Models\Sport;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $org = Organization::query()->firstOrFail();

        $sport = Sport::query()->create([
            'name' => 'Basketball',
            'slug' => 'basketball',
            'description' => null,
            'organization_id' => $org->id,
        ]);

        $response = $this->post('/register/student', [
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@example.com',
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

        $this->assertTrue(
            Profile::query()
                ->where('course', 'BS Sports Science')
                ->where('height_cm', 170)
                ->where('weight_kg', 65)
                ->whereDate('birthdate', '2005-06-15')
                ->exists()
        );
    }
}
