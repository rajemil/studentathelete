<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\PerformanceScore;
use App\Models\Sport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class AdminReportsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_scores_csv_scoped_to_org(): void
    {
        $orgId = Organization::defaultId();

        $admin = User::factory()->create(['role' => 'admin', 'organization_id' => $orgId]);
        $student = User::factory()->create(['role' => 'student', 'organization_id' => $orgId]);

        $sport = Sport::query()->create([
            'organization_id' => $orgId,
            'name' => 'Tennis',
            'slug' => 'tennis',
            'description' => null,
        ]);

        $otherSport = Sport::query()->create([
            'organization_id' => $orgId,
            'name' => 'Soccer',
            'slug' => 'soccer',
            'description' => null,
        ]);

        PerformanceScore::query()->create([
            'user_id' => $student->id,
            'sport_id' => $sport->id,
            'team_id' => null,
            'category' => 'overall',
            'score' => 88,
            'scored_on' => Carbon::now()->toDateString(),
            'breakdown' => ['entered_by' => $admin->id],
        ]);

        PerformanceScore::query()->create([
            'user_id' => $student->id,
            'sport_id' => $otherSport->id,
            'team_id' => null,
            'category' => 'overall',
            'score' => 55,
            'scored_on' => Carbon::now()->toDateString(),
            'breakdown' => ['entered_by' => $admin->id],
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.performance_scores_csv', [
            'sport_id' => $sport->id,
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');

        $csv = $response->streamedContent();
        $this->assertStringContainsString('student_email', $csv);
        $this->assertStringContainsString($student->email, $csv);
        $this->assertStringContainsString('Tennis', $csv);
        $this->assertStringNotContainsString('Soccer', $csv);
    }
}
