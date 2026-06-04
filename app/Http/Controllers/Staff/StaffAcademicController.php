<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AcademicRecord;
use App\Models\User;
use App\Support\RosterAccess;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffAcademicController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', AcademicRecord::class);

        $user = $request->user();
        $orgId = $user->organization_id;

        $athleteIds = RosterAccess::coachedStudentIds($user);

        // Fetch students assigned to coach's teams
        $students = User::query()
            ->where('organization_id', $orgId)
            ->where('role', 'student')
            ->whereIn('id', $athleteIds)
            ->with(['academicRecords', 'attendanceRecords', 'eligibilityReviews'])
            ->get();

        return view('staff.academics.index', compact('students'));
    }
}
