<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicRecord;
use App\Models\AttendanceRecord;
use App\Models\EligibilityReview;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentAcademicController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $this->authorize('view', [AcademicRecord::class, $user]);

        $academicRecords = AcademicRecord::query()
            ->where('user_id', $user->id)
            ->orderByDesc('semester')
            ->get();

        $attendanceRecords = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->orderByDesc('date')
            ->get();

        $eligibilityReviews = EligibilityReview::query()
            ->where('user_id', $user->id)
            ->with('reviewer:id,name')
            ->orderByDesc('review_date')
            ->get();

        return view('student.academics.index', compact('academicRecords', 'attendanceRecords', 'eligibilityReviews'));
    }
}
