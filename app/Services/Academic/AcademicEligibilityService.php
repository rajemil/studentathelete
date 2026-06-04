<?php

namespace App\Services\Academic;

use App\Models\AcademicRecord;
use App\Models\AttendanceRecord;
use App\Models\EligibilityReview;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AcademicEligibilityService
{
    /**
     * Compute academic metrics for an organization.
     *
     * @param  int  $orgId
     * @return array{students: \Illuminate\Database\Eloquent\Collection<User>, warningsCount: int, ineligibleCount: int, averageGpa: float}
     */
    public function getAcademicMetrics(int $orgId): array
    {
        // Fetch students in the organization
        $students = User::query()
            ->where('organization_id', $orgId)
            ->where('role', 'student')
            ->with(['academicRecords', 'attendanceRecords', 'eligibilityReviews'])
            ->get();

        // Calculate metrics
        $warningsCount = 0;
        $ineligibleCount = 0;
        $totalGpa = 0;
        $studentGpaCount = 0;

        foreach ($students as $student) {
            $recentRecord = $student->academicRecords->sortByDesc('semester')->first();
            $recentReview = $student->eligibilityReviews->sortByDesc('review_date')->first();

            $gpa = $student->academicRecords->avg('gpa');
            if ($gpa) {
                $totalGpa += $gpa;
                $studentGpaCount++;
            }

            $presentCount = $student->attendanceRecords->whereIn('status', ['present', 'tardy'])->count();
            $totalAttendance = $student->attendanceRecords->count();
            $attendanceRate = $totalAttendance > 0 ? ($presentCount / $totalAttendance) * 100 : null;

            // Flags
            $isWarning = ($gpa && $gpa < 2.0) || ($attendanceRate !== null && $attendanceRate < 80.0) || ($recentRecord && $recentRecord->status === 'warning');
            $isIneligible = ($recentReview && $recentReview->status === 'ineligible') || ($recentRecord && $recentRecord->status === 'ineligible');

            if ($isIneligible) {
                $ineligibleCount++;
            } elseif ($isWarning) {
                $warningsCount++;
            }
        }

        $averageGpa = $studentGpaCount > 0 ? round($totalGpa / $studentGpaCount, 2) : 0.0;

        return [
            'students' => $students,
            'warningsCount' => $warningsCount,
            'ineligibleCount' => $ineligibleCount,
            'averageGpa' => $averageGpa,
        ];
    }

    /**
     * Store academic record and evaluate warning.
     *
     * @param  User  $student
     * @param  array  $data
     * @return AcademicRecord
     */
    public function storeRecord(User $student, array $data): AcademicRecord
    {
        $status = 'good_standing';
        if ($data['gpa'] < 2.0) {
            $status = 'warning';
        }

        $record = AcademicRecord::create([
            'user_id' => $student->id,
            'semester' => $data['semester'],
            'gpa' => $data['gpa'],
            'credits_earned' => $data['credits_earned'],
            'status' => $status,
        ]);

        if ($status === 'warning') {
            $this->createWarningNotification(
                $student,
                "GPA dropped to {$data['gpa']} in {$data['semester']}. Immediate improvement is required to maintain athletic eligibility."
            );
        }

        return $record;
    }

    /**
     * Store attendance record and evaluate dynamic warning.
     *
     * @param  User  $student
     * @param  array  $data
     * @return AttendanceRecord
     */
    public function storeAttendance(User $student, array $data): AttendanceRecord
    {
        $record = AttendanceRecord::create([
            'user_id' => $student->id,
            'course_name' => $data['course_name'],
            'date' => $data['date'],
            'status' => $data['status'],
            'notes' => $data['notes'] ?? null,
        ]);

        $presentCount = $student->attendanceRecords()->whereIn('status', ['present', 'tardy'])->count();
        $totalCount = $student->attendanceRecords()->count();

        if ($totalCount > 4) {
            $rate = ($presentCount / $totalCount) * 100;
            if ($rate < 80.0) {
                $this->createWarningNotification(
                    $student,
                    "Class attendance has dropped to " . round($rate, 1) . "%. Attendance must remain above 80% for sports eligibility."
                );
            }
        }

        return $record;
    }

    /**
     * Store eligibility review and notify user.
     *
     * @param  User  $student
     * @param  int  $reviewerId
     * @param  array  $data
     * @return EligibilityReview
     */
    public function storeReview(User $student, int $reviewerId, array $data): EligibilityReview
    {
        $review = EligibilityReview::create([
            'user_id' => $student->id,
            'reviewer_id' => $reviewerId,
            'review_date' => $data['review_date'],
            'status' => $data['status'],
            'comments' => $data['comments'] ?? null,
        ]);

        $this->createNotification(
            $student,
            'academic_review',
            "Your academic eligibility status has been updated to " . strtoupper($data['status']) . ".",
            'info'
        );

        return $review;
    }

    /**
     * Send a warning notification.
     *
     * @param  User  $student
     * @param  string  $message
     * @return void
     */
    protected function createWarningNotification(User $student, string $message): void
    {
        $this->createNotification($student, 'academic_warning', $message, 'warning');
    }

    /**
     * Send a general academic notification.
     *
     * @param  User  $student
     * @param  string  $type
     * @param  string  $message
     * @param  string  $severity
     * @return void
     */
    protected function createNotification(User $student, string $type, string $message, string $severity): void
    {
        DB::table('notifications')->insert([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => $type,
            'notifiable_type' => User::class,
            'notifiable_id' => $student->id,
            'data' => json_encode([
                'title' => $type === 'academic_warning' ? 'Academic Warning' : 'Academic Eligibility Update',
                'message' => $message,
                'severity' => $severity,
            ]),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
