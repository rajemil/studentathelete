<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BelongsToOrganizationScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_only_sees_own_organization_sports(): void
    {
        $orgA = Organization::factory()->create(['slug' => 'org-scope-a', 'name' => 'Org A']);
        $orgB = Organization::factory()->create(['slug' => 'org-scope-b', 'name' => 'Org B']);

        $adminA = User::factory()->create(['role' => 'admin', 'organization_id' => $orgA->id]);

        Sport::query()->create([
            'organization_id' => $orgA->id,
            'name' => 'Visible Sport',
            'slug' => 'visible-sport',
        ]);

        Sport::query()->create([
            'organization_id' => $orgB->id,
            'name' => 'Hidden Sport',
            'slug' => 'hidden-sport',
        ]);

        $this->actingAs($adminA);

        $names = Sport::query()->orderBy('name')->pluck('name')->all();

        $this->assertSame(['Visible Sport'], $names);
        $this->assertFalse(Sport::query()->where('name', 'Hidden Sport')->exists());
    }

    public function test_unauthenticated_queries_are_not_scoped(): void
    {
        $org = Organization::query()->firstOrFail();

        Sport::query()->create([
            'organization_id' => $org->id,
            'name' => 'Guest Visible',
            'slug' => 'guest-visible',
        ]);

        $this->assertTrue(Sport::query()->where('name', 'Guest Visible')->exists());
    }
}
