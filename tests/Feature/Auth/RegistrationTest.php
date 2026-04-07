<?php

namespace Tests\Feature\Auth;

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
        $sport = Sport::query()->create([
            'name' => 'Basketball',
            'slug' => 'basketball',
            'description' => null,
        ]);

        $response = $this->post('/register/student', [
            'name' => 'Test Student',
            'email' => 'student@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'age' => 18,
            'gender' => 'Male',
            'address' => 'Test Address',
            'height_cm' => 170,
            'weight_kg' => 65,
            'sports_interested' => [$sport->id],
        ]);

        $response->assertRedirect(route('dashboard'));
    }
}
