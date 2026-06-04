<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailVerificationEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_unverified_user_cannot_access_dashboard(): void
    {
        $org = Organization::query()->firstOrFail();

        $user = User::factory()->unverified()->create([
            'role' => 'student',
            'organization_id' => $org->id,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_verified_user_can_access_dashboard(): void
    {
        $org = Organization::query()->firstOrFail();

        $user = User::factory()->create([
            'role' => 'student',
            'organization_id' => $org->id,
        ]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('student.dashboard'));
    }
}
