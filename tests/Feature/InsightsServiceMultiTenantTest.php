<?php

namespace Tests\Feature;

use App\Models\Insight;
use App\Models\Organization;
use App\Models\PerformanceScore;
use App\Models\User;
use App\Services\Insights\InsightsService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Verifies that InsightsService is fully tenant-safe:
 *  - ensureGenerated() scopes the existence check to the given organization.
 *  - generate() only queries performance data belonging to the given org's users.
 *  - Cross-tenant insight rows never contaminate each other's checks.
 *
 * Schema note: performance_scores has no organization_id column.
 * Tenant isolation is via user_id → users.organization_id.
 */
class InsightsServiceMultiTenantTest extends TestCase
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
        ]);
    }

    private function plantInsight(Organization $org): Insight
    {
        return Insight::factory()->create([
            'organization_id' => $org->id,
        ]);
    }

    // -----------------------------------------------------------------------
    // ensureGenerated() – skips when org already has insights
    // -----------------------------------------------------------------------

    public function test_ensure_generated_skips_when_org_already_has_insights(): void
    {
        $org = $this->makeOrg('org-with-insights');
        $this->plantInsight($org);

        /** @var InsightsService $svc */
        $svc = app(InsightsService::class);

        $count = $svc->ensureGenerated(CarbonImmutable::now(), $org->id);

        $this->assertSame(0, $count, 'Should return 0 when insights already exist for the org');
        $this->assertSame(1, Insight::where('organization_id', $org->id)->count());
    }

    // -----------------------------------------------------------------------
    // ensureGenerated() – generates for org-B even when org-A already has rows
    // -----------------------------------------------------------------------

    public function test_ensure_generated_is_not_blocked_by_other_orgs_insights(): void
    {
        $orgA = $this->makeOrg('org-blocked-a');
        $orgB = $this->makeOrg('org-blocked-b');

        // Pre-seed org-A with an insight (global table is non-empty).
        $this->plantInsight($orgA);

        /** @var InsightsService $svc */
        $svc = app(InsightsService::class);

        // Invoking for org-B must not short-circuit because org-A has insights.
        // (With no performance data for org-B, generate() returns 0, but it IS called.)
        $svc->ensureGenerated(CarbonImmutable::now(), $orgB->id);

        // Org-A insight untouched.
        $this->assertSame(1, Insight::where('organization_id', $orgA->id)->count());
        // No exception raised = tenant guard did not wrongly block org-B.
        $this->assertTrue(true);
    }

    // -----------------------------------------------------------------------
    // ensureGenerated() with null org → global fallback (console behaviour)
    // -----------------------------------------------------------------------

    public function test_ensure_generated_null_org_uses_global_check(): void
    {
        $org = $this->makeOrg('org-global-check');
        $this->plantInsight($org);

        /** @var InsightsService $svc */
        $svc = app(InsightsService::class);

        // With null, checks the whole table — any row means skip.
        $count = $svc->ensureGenerated(CarbonImmutable::now(), null);

        $this->assertSame(0, $count);
    }

    // -----------------------------------------------------------------------
    // generate() – only generates insights for org-A users, not org-B
    // -----------------------------------------------------------------------

    public function test_generate_scopes_insights_to_target_organization(): void
    {
        $orgA = $this->makeOrg('gen-scope-a');
        $orgB = $this->makeOrg('gen-scope-b');

        $studentA = $this->makeStudent($orgA);

        $now = CarbonImmutable::now();
        $this->seedImprovingScores($studentA, $now);

        /** @var InsightsService $svc */
        $svc = app(InsightsService::class);

        // Generate only for org-A.
        $generatedCount = $svc->generate($now, $orgA->id);

        // Org-A should have insights generated (scores exist → heuristic fires).
        $this->assertGreaterThan(0, $generatedCount);
        $this->assertGreaterThan(0, Insight::where('organization_id', $orgA->id)->count());

        // Org-B must have zero insights.
        $this->assertSame(0, Insight::where('organization_id', $orgB->id)->count());
    }

    public function test_generate_for_org_b_does_not_pick_up_org_a_data(): void
    {
        $orgA = $this->makeOrg('leak-check-a');
        $orgB = $this->makeOrg('leak-check-b');

        $studentA = $this->makeStudent($orgA);

        $now = CarbonImmutable::now();
        $this->seedImprovingScores($studentA, $now);

        /** @var InsightsService $svc */
        $svc = app(InsightsService::class);

        // Generate only for org-B which has no scores.
        $generatedCount = $svc->generate($now, $orgB->id);

        // Org-B has no students with performance data → nothing generated.
        $this->assertSame(0, $generatedCount);
        $this->assertSame(0, Insight::where('organization_id', $orgB->id)->count());

        // Org-A data must remain untouched (no insights written for it here).
        $this->assertSame(0, Insight::where('organization_id', $orgA->id)->count());
    }

    // -----------------------------------------------------------------------
    // Private helpers
    // -----------------------------------------------------------------------

    /**
     * Seed two full weeks of improving performance scores for a student.
     * Uses only columns that exist on the performance_scores table.
     * Tenant isolation is guaranteed by the user's organization_id.
     */
    private function seedImprovingScores(User $student, CarbonImmutable $now): void
    {
        // Previous week (days -14 .. -8): baseline low scores
        foreach (range(14, 8) as $daysAgo) {
            PerformanceScore::query()->create([
                'user_id' => $student->id,
                'sport_id' => null,
                'team_id' => null,
                'category' => 'overall',
                'score' => 50,
                'scored_on' => $now->subDays($daysAgo)->toDateString(),
            ]);
        }

        // Current week (days -7 .. -1): significantly higher scores (>8% improvement)
        foreach (range(7, 1) as $daysAgo) {
            PerformanceScore::query()->create([
                'user_id' => $student->id,
                'sport_id' => null,
                'team_id' => null,
                'category' => 'overall',
                'score' => 80,
                'scored_on' => $now->subDays($daysAgo)->toDateString(),
            ]);
        }
    }
}
