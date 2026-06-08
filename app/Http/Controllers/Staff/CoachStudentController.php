<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoachStudentController extends Controller
{
    public function index(Request $request): View
    {
        $orgId = $request->user()->organization_id;
        $status = $request->query('status', 'all');

        $query = User::query()
            ->where('organization_id', $orgId)
            ->where('role', 'student')
            ->with(['profile.course', 'profile.yearLevel', 'profile.section', 'sports']);

        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            $query->where('approval_status', $status);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('staff.students.index', compact('students', 'status', 'search'));
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $this->ensureInOrg($request, $user);

        $user->update([
            'approval_status' => 'approved',
        ]);

        // Bust dashboard caches for coaches assigned to any sport the student belongs to.
        $studentSportIds = $user->sports()->pluck('sports.id');
        if ($studentSportIds->isNotEmpty()) {
            $coachIds = User::query()
                ->where('role', 'coach')
                ->whereIn('sport_id', $studentSportIds)
                ->pluck('id');
            foreach ($coachIds as $coachId) {
                \App\Services\Analytics\AnalyticsCache::forgetUserDashboard((int) $coachId);
            }
        }

        return back()->with('status', 'Student approved successfully.');
    }

    public function reject(Request $request, User $user): RedirectResponse
    {
        $this->ensureInOrg($request, $user);

        $user->update([
            'approval_status' => 'rejected',
        ]);

        return back()->with('status', 'Student rejected.');
    }

    private function ensureInOrg(Request $request, User $user): void
    {
        abort_unless(
            $user->role === 'student' && 
            (int) $user->organization_id === (int) $request->user()->organization_id, 
            404
        );
    }
}
