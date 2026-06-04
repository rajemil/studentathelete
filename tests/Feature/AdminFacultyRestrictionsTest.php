<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminFacultyRestrictionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_create_faculty_with_admin_role(): void
    {
        $org = Organization::query()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'organization_id' => $org->id]);

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'first_name' => 'Extra',
            'last_name' => 'Admin',
            'email' => 'extra-admin@example.com',
            'role' => 'admin',
            'password' => 'SecurePass1!',
            'password_confirmation' => 'SecurePass1!',
            'birthdate' => '1990-01-01',
            'gender' => 'male',
            'address' => '123 Main St',
            'profession' => 'Administrator',
            'field_expertise' => 'Systems',
            'coaching_experience_years' => 5,
        ]);

        $response->assertSessionHasErrors('role');
    }

    public function test_faculty_index_does_not_list_admin_users(): void
    {
        $org = Organization::query()->firstOrFail();
        $actingAdmin = User::factory()->create([
            'role' => 'admin',
            'organization_id' => $org->id,
            'name' => 'Portal Admin',
        ]);

        User::factory()->create([
            'role' => 'admin',
            'organization_id' => $org->id,
            'name' => 'Hidden Faculty Admin',
        ]);

        User::factory()->create([
            'role' => 'coach',
            'organization_id' => $org->id,
            'name' => 'Visible Coach',
        ]);

        $response = $this->actingAs($actingAdmin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee('Visible Coach');
        $response->assertDontSee('Hidden Faculty Admin', false);
    }

    public function test_admin_faculty_edit_returns_404_for_admin_user(): void
    {
        $org = Organization::query()->firstOrFail();
        $actingAdmin = User::factory()->create(['role' => 'admin', 'organization_id' => $org->id]);
        $targetAdmin = User::factory()->create(['role' => 'admin', 'organization_id' => $org->id]);

        $this->actingAs($actingAdmin)
            ->get(route('admin.users.edit', $targetAdmin))
            ->assertNotFound();
    }
}
