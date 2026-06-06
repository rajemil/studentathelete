<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\ParticipationLog;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for StudentParticipationLogsController::store()
 *
 * Covers three sport_id scenarios:
 *   1. Valid sport belonging to the authenticated user's organization.
 *   2. Completely invalid (non-existent) sport_id.
 *   3. Valid sport but belonging to a different organization (cross-tenant).
 */
class StudentParticipationLogsTest extends TestCase
{
    use RefreshDatabase;

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function makeOrg(string $slug): Organization
    {
        return Organization::factory()->create(['slug' => $slug]);
    }

    private function makeStudent(Organization $org): User
    {
        return User::factory()->create([
            'role' => 'student',
            'organization_id' => $org->id,
            'approval_status' => 'approved',
        ]);
    }

    private function makeSport(Organization $org, string $name = 'Basketball'): Sport
    {
        return Sport::query()->create([
            'organization_id' => $org->id,
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name . '-' . $org->id),
        ]);
    }

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'sport_id' => null,
            'activity_type' => 'training',
            'duration_minutes' => 60,
            'notes' => 'Morning session',
            'logged_on' => now()->toDateString(),
        ], $overrides);
    }

    // -----------------------------------------------------------------------
    // 1. Valid sport — same organization
    // -----------------------------------------------------------------------

    public function test_student_can_log_activity_with_valid_sport(): void
    {
        $org = $this->makeOrg('log-org-valid');
        $student = $this->makeStudent($org);
        $sport = $this->makeSport($org, 'Swimming');

        $response = $this->actingAs($student)
            ->post(route('student.participation_logs.store'), $this->payload([
                'sport_id' => $sport->id,
            ]));

        $response->assertRedirect(route('student.participation_logs.index'));
        $response->assertSessionHas('status', 'Activity logged.');

        $this->assertDatabaseHas('participation_logs', [
            'user_id' => $student->id,
            'organization_id' => $org->id,
            'sport_id' => $sport->id,
            'activity_type' => 'training',
        ]);
    }

    public function test_student_can_log_activity_without_sport(): void
    {
        $org = $this->makeOrg('log-org-no-sport');
        $student = $this->makeStudent($org);

        $response = $this->actingAs($student)
            ->post(route('student.participation_logs.store'), $this->payload([
                'sport_id' => null,
            ]));

        $response->assertRedirect(route('student.participation_logs.index'));

        $this->assertDatabaseHas('participation_logs', [
            'user_id' => $student->id,
            'sport_id' => null,
        ]);
    }

    // -----------------------------------------------------------------------
    // 2. Invalid sport_id — does not exist at all
    // -----------------------------------------------------------------------

    public function test_invalid_sport_id_is_rejected_with_validation_error(): void
    {
        $org = $this->makeOrg('log-org-invalid');
        $student = $this->makeStudent($org);

        $response = $this->actingAs($student)
            ->post(route('student.participation_logs.store'), $this->payload([
                'sport_id' => 999999, // definitely non-existent
            ]));

        $response->assertSessionHasErrors(['sport_id']);

        // No FK violation, no DB record.
        $this->assertDatabaseEmpty('participation_logs');
    }

    public function test_non_integer_sport_id_is_rejected(): void
    {
        $org = $this->makeOrg('log-org-bad-type');
        $student = $this->makeStudent($org);

        $response = $this->actingAs($student)
            ->post(route('student.participation_logs.store'), $this->payload([
                'sport_id' => 'not-an-integer',
            ]));

        // The integer validation rule rejects this before the tenant check.
        $response->assertSessionHasErrors(['sport_id']);
        $this->assertDatabaseEmpty('participation_logs');
    }

    // -----------------------------------------------------------------------
    // 3. Cross-tenant sport — exists but belongs to a different organization
    // -----------------------------------------------------------------------

    public function test_cross_tenant_sport_is_rejected_with_validation_error(): void
    {
        $orgA = $this->makeOrg('log-org-a');
        $orgB = $this->makeOrg('log-org-b');

        $student = $this->makeStudent($orgA);
        $otherOrgSport = $this->makeSport($orgB, 'Rugby'); // belongs to org-B

        $response = $this->actingAs($student)
            ->post(route('student.participation_logs.store'), $this->payload([
                'sport_id' => $otherOrgSport->id,
            ]));

        // Must get a validation error, not a FK crash or silent 0.
        $response->assertSessionHasErrors(['sport_id']);

        // The cross-tenant sport must not be recorded.
        $this->assertDatabaseEmpty('participation_logs');
    }

    public function test_cross_tenant_sport_never_stores_zero_as_sport_id(): void
    {
        $orgA = $this->makeOrg('log-nozero-a');
        $orgB = $this->makeOrg('log-nozero-b');

        $student = $this->makeStudent($orgA);
        $otherOrgSport = $this->makeSport($orgB, 'Tennis');

        $this->actingAs($student)
            ->post(route('student.participation_logs.store'), $this->payload([
                'sport_id' => $otherOrgSport->id,
            ]));

        // Ensure sport_id=0 was never written (old bug).
        $this->assertDatabaseMissing('participation_logs', [
            'user_id' => $student->id,
            'sport_id' => 0,
        ]);
    }

    // -----------------------------------------------------------------------
    // 4. Edge: sport_id = 0 (explicit zero submitted)
    // -----------------------------------------------------------------------

    public function test_zero_sport_id_is_treated_as_null_empty_and_skipped(): void
    {
        $org = $this->makeOrg('log-org-zero');
        $student = $this->makeStudent($org);

        // sport_id = 0 is falsy → empty() catches it → treated as null.
        $response = $this->actingAs($student)
            ->post(route('student.participation_logs.store'), $this->payload([
                'sport_id' => 0,
            ]));

        // Should succeed with sport_id null.
        $response->assertRedirect(route('student.participation_logs.index'));

        $this->assertDatabaseHas('participation_logs', [
            'user_id' => $student->id,
            'sport_id' => null,
        ]);

        // Must NOT have stored 0.
        $this->assertDatabaseMissing('participation_logs', [
            'user_id' => $student->id,
            'sport_id' => 0,
        ]);
    }
}
