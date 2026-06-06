<?php

namespace Tests\Feature\Auth;

use App\Models\Course;
use App\Models\Organization;
use App\Models\Profile;
use App\Models\Section;
use App\Models\Sport;
use App\Models\YearLevel;
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

        $course = Course::create(['organization_id' => $org->id, 'name' => 'BS Sports Science']);
        $year = YearLevel::create(['organization_id' => $org->id, 'name' => 'First Year']);
        $sec = Section::create(['organization_id' => $org->id, 'name' => 'A']);

        $response = $this->post('/register/student', [
            'first_name' => 'Test',
            'last_name' => 'Student',
            'email' => 'student@example.com',
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
            'birthdate' => '2005-06-15',
            'gender' => 'male',
            'address' => 'Test Address',
            'course_id' => $course->id,
            'year_level_id' => $year->id,
            'section_id' => $sec->id,
            'height_cm' => 170,
            'weight_kg' => 65,
            'sports_interested' => [$sport->id],
        ]);

        $response->assertRedirect(route('verification.notice'));

        $this->assertTrue(
            Profile::query()
                ->where('course_id', $course->id)
                ->where('year_level_id', $year->id)
                ->where('section_id', $sec->id)
                ->where('height_cm', 170)
                ->where('weight_kg', 65)
                ->whereDate('birthdate', '2005-06-15')
                ->exists()
        );
    }
}
