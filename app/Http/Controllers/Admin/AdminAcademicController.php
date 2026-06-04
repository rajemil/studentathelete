<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Academic\AcademicEligibilityService;
use App\Models\AcademicRecord;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAcademicController extends Controller
{
    protected AcademicEligibilityService $academicEligibilityService;

    public function __construct(AcademicEligibilityService $academicEligibilityService)
    {
        $this->academicEligibilityService = $academicEligibilityService;
    }

    public function index(): View
    {
        $this->authorize('manage', AcademicRecord::class);

        $orgId = auth()->user()->organization_id;

        $metrics = $this->academicEligibilityService->getAcademicMetrics($orgId);

        $students = $metrics['students'];
        $warningsCount = $metrics['warningsCount'];
        $ineligibleCount = $metrics['ineligibleCount'];
        $averageGpa = $metrics['averageGpa'];

        return view('admin.academics.index', compact('students', 'warningsCount', 'ineligibleCount', 'averageGpa'));
    }

    public function storeRecord(Request $request): RedirectResponse
    {
        $this->authorize('manage', AcademicRecord::class);

        $orgId = (int) $request->user()->organization_id;

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q
                    ->where('organization_id', $orgId)
                    ->where('role', 'student')),
            ],
            'semester' => ['required', 'string', 'max:50'],
            'gpa' => ['required', 'numeric', 'min:0', 'max:4.0'],
            'credits_earned' => ['required', 'integer', 'min:0'],
        ]);

        $student = User::findOrFail($validated['user_id']);

        $this->academicEligibilityService->storeRecord($student, $validated);

        return redirect()->back()->with('success', 'Academic record saved successfully.');
    }

    public function storeAttendance(Request $request): RedirectResponse
    {
        $this->authorize('manage', AcademicRecord::class);

        $orgId = (int) $request->user()->organization_id;

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q
                    ->where('organization_id', $orgId)
                    ->where('role', 'student')),
            ],
            'course_name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:present,absent,excused,tardy'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $student = User::findOrFail($validated['user_id']);

        $this->academicEligibilityService->storeAttendance($student, $validated);

        return redirect()->back()->with('success', 'Attendance record logged successfully.');
    }

    public function storeReview(Request $request): RedirectResponse
    {
        $this->authorize('manage', AcademicRecord::class);

        $orgId = (int) $request->user()->organization_id;

        $validated = $request->validate([
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q
                    ->where('organization_id', $orgId)
                    ->where('role', 'student')),
            ],
            'review_date' => ['required', 'date'],
            'status' => ['required', 'string', 'in:eligible,ineligible,probation,pending'],
            'comments' => ['nullable', 'string', 'max:1000'],
        ]);

        $student = User::findOrFail($validated['user_id']);

        $this->academicEligibilityService->storeReview($student, auth()->id(), $validated);

        return redirect()->back()->with('success', 'Academic eligibility review recorded successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('manage', AcademicRecord::class);

        $orgId = $request->user()->organization_id;
        $now = CarbonImmutable::now();

        $validated = $request->validate([
            'semester' => ['nullable', 'string', 'max:50'],
            'student_id' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'string', 'max:50'],
        ]);

        $semester = $validated['semester'] ?? null;

        $studentId = null;
        if (isset($validated['student_id'])) {
            $studentIdValue = User::query()
                ->where('organization_id', $orgId)
                ->where('role', 'student')
                ->whereKey((int) $validated['student_id'])
                ->value('id');
            $studentId = $studentIdValue !== null ? (int) $studentIdValue : null;
        }

        $status = $validated['status'] ?? null;

        $filename = 'academic-records-'.$now->format('Y-m-d').'.csv';

        activity()
            ->causedBy($request->user())
            ->withProperties([
                'organization_id' => $orgId,
                'semester' => $semester,
                'student_id' => $studentId,
                'status' => $status,
            ])
            ->log('report_export_academic_records');

        return Response::streamDownload(function () use ($orgId, $semester, $studentId, $status): void {
            $out = fopen('php://output', 'w');
            if ($out === false) {
                return;
            }

            fputcsv($out, [
                'semester',
                'student_id',
                'student_name',
                'student_email',
                'gpa',
                'credits_earned',
                'status',
            ]);

            AcademicRecord::query()
                ->select([
                    'academic_records.semester',
                    'academic_records.user_id',
                    'academic_records.gpa',
                    'academic_records.credits_earned',
                    'academic_records.status',
                    'users.name as student_name',
                    'users.email as student_email',
                ])
                ->join('users', 'users.id', '=', 'academic_records.user_id')
                ->where('users.organization_id', $orgId)
                ->when($semester !== null, fn ($q) => $q->where('academic_records.semester', $semester))
                ->when($studentId !== null, fn ($q) => $q->where('academic_records.user_id', $studentId))
                ->when($status !== null, fn ($q) => $q->where('academic_records.status', $status))
                ->orderBy('academic_records.semester', 'desc')
                ->orderBy('users.name', 'asc')
                ->chunk(500, function ($rows) use ($out): void {
                    foreach ($rows as $row) {
                        fputcsv($out, [
                            (string) $row->semester,
                            (string) $row->user_id,
                            (string) ($row->student_name ?? ''),
                            (string) ($row->student_email ?? ''),
                            (string) $row->gpa,
                            (string) $row->credits_earned,
                            (string) $row->status,
                        ]);
                    }
                });

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}

