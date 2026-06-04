<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use App\Models\AcademicRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAcademicEligibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_academics_index(): void
    {
        $org = Organization::query()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'organization_id' => $org->id]);
        User::factory()->create(['role' => 'student', 'organization_id' => $org->id]);

        $response = $this->actingAs($admin)->get(route('admin.academics.index'));
        $response->assertOk();
        $response->assertSee('Academic', false);
    }

    public function test_admin_cannot_store_academic_record_for_student_in_other_org(): void
    {
        $orgA = Organization::factory()->create(['slug' => 'org-a-'.uniqid()]);
        $orgB = Organization::factory()->create(['slug' => 'org-b-'.uniqid()]);

        $admin = User::factory()->create(['role' => 'admin', 'organization_id' => $orgA->id]);
        $otherStudent = User::factory()->create(['role' => 'student', 'organization_id' => $orgB->id]);

        $response = $this->actingAs($admin)->post(route('admin.academics.records.store'), [
            'user_id' => $otherStudent->id,
            'semester' => 'Fall 2026',
            'gpa' => '3.0',
            'credits_earned' => 12,
        ]);

        $response->assertSessionHasErrors('user_id');
        $this->assertDatabaseMissing('academic_records', [
            'user_id' => $otherStudent->id,
            'semester' => 'Fall 2026',
        ]);
    }

    public function test_logging_low_gpa_triggers_academic_warning_notification(): void
    {
        $org = Organization::query()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'organization_id' => $org->id]);
        $student = User::factory()->create(['role' => 'student', 'organization_id' => $org->id]);

        // Post a low GPA record to trigger warning
        $response = $this->actingAs($admin)->post(route('admin.academics.records.store'), [
            'user_id' => $student->id,
            'semester' => 'Fall 2026',
            'gpa' => '1.85',
            'credits_earned' => 12,
        ]);

        $response->assertRedirect();

        // Verify the database has the record
        $this->assertDatabaseHas('academic_records', [
            'user_id' => $student->id,
            'semester' => 'Fall 2026',
            'gpa' => '1.85',
            'status' => 'warning',
        ]);

        // Verify standard Laravel notification table contains the notification
        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => User::class,
            'notifiable_id' => $student->id,
            'type' => 'academic_warning',
        ]);

        // Check the notification endpoint returns the unread count and data
        $response = $this->actingAs($student)->getJson(route('notifications.index'));
        $response->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonFragment(['title' => 'Academic Warning']);
    }

    public function test_admin_can_export_academic_records_csv(): void
    {
        $org = Organization::query()->firstOrFail();
        $admin = User::factory()->create(['role' => 'admin', 'organization_id' => $org->id]);
        $student = User::factory()->create(['role' => 'student', 'organization_id' => $org->id]);

        // Create some dummy academic records
        AcademicRecord::create([
            'user_id' => $student->id,
            'semester' => 'Spring 2026',
            'gpa' => '3.80',
            'credits_earned' => 15,
            'status' => 'good_standing',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.academics.export'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('semester,student_id,student_name', $response->streamedContent());
        $this->assertStringContainsString('Spring 2026', $response->streamedContent());
        $this->assertStringContainsString('3.80', $response->streamedContent());
    }
}
