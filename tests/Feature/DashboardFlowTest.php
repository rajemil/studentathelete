<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirects_admin_to_admin_dashboard(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/dashboard');

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_dashboard_redirects_coach_to_coach_dashboard(): void
    {
        $coach = User::factory()->create(['role' => 'coach']);

        $response = $this->actingAs($coach)->get('/dashboard');

        $response->assertRedirect(route('coach.dashboard'));
    }

    public function test_dashboard_redirects_student_to_student_dashboard(): void
    {
        $student = User::factory()->create(['role' => 'student']);

        $response = $this->actingAs($student)->get('/dashboard');

        $response->assertRedirect(route('student.dashboard'));
    }
}
