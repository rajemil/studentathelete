<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\CoachAssignment;
use App\Models\Sport;
use App\Models\SportApplication;
use App\Models\Team;
use App\Models\User;
use App\Notifications\SportApplicationSubmitted;
use App\Services\Sports\SportApplicationQualificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentSportBrowseController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $joinedIds = $user->sports()->pluck('sports.id');

        $sports = Sport::query()
            ->where('organization_id', $user->organization_id)
            ->withCount('students')
            ->orderBy('name')
            ->paginate(12);

        $applicationsBySportId = SportApplication::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('sport_id');

        return view('student.sports.index', compact('sports', 'joinedIds', 'applicationsBySportId'));
    }

    public function apply(Request $request, Sport $sport, SportApplicationQualificationService $qualification): RedirectResponse
    {
        $user = auth()->user();
        abort_unless((int) $sport->organization_id === (int) $user->organization_id, 403);

        if ($user->sports()->where('sports.id', $sport->id)->exists()) {
            return redirect()->route('student.sports.index')
                ->with('status', 'You are already enrolled in '.$sport->name.'.');
        }

        $rules = [
            'student_message' => ['nullable', 'string', 'max:1000'],
        ];

        if ($sport->require_report_card) {
            $rules['report_card'] = ['required', 'file', 'max:5120', 'mimes:pdf,jpeg,png,jpg'];
        }
        if ($sport->require_medical_form) {
            $rules['medical_form'] = ['required', 'file', 'max:5120', 'mimes:pdf,jpeg,png,jpg'];
        }
        if ($sport->require_bp) {
            $rules['medical_bp'] = ['required', 'string', 'max:50'];
        }
        if ($sport->require_heart_rate) {
            $rules['medical_heart_rate'] = ['required', 'string', 'max:50'];
        }
        if ($sport->require_allergies) {
            $rules['medical_allergies'] = ['required', 'string', 'max:1000'];
        }

        $validated = $request->validate($rules);

        $user->load('profile');
        $result = $qualification->evaluate($user, $sport);

        $applicationData = [
            'status' => 'pending',
            'student_message' => $validated['student_message'] ?? null,
            'qualification_passed' => $result['passed'],
            'qualification_detail' => $result['reasons'],
            'medical_bp' => $validated['medical_bp'] ?? null,
            'medical_heart_rate' => $validated['medical_heart_rate'] ?? null,
            'medical_allergies' => $validated['medical_allergies'] ?? null,
        ];

        if ($request->hasFile('report_card')) {
            $applicationData['report_card_path'] = $request->file('report_card')->store('coach-requirements', 'public');
        }
        if ($request->hasFile('medical_form')) {
            $applicationData['other_document_path'] = $request->file('medical_form')->store('coach-requirements', 'public');
        }

        $application = SportApplication::query()->updateOrCreate(
            [
                'sport_id' => $sport->id,
                'user_id' => $user->id,
            ],
            $applicationData
        );

        $recipientIds = collect();
        if ($sport->instructor_user_id) {
            $recipientIds->push((int) $sport->instructor_user_id);
        }
        
        // Also notify all staff explicitly assigned to this sport via the pivot table
        $assignedStaffIds = $sport->belongsToMany(User::class, 'sport_user')
            ->whereIn('role', ['coach', 'instructor', 'admin'])
            ->pluck('users.id');
        $recipientIds = $recipientIds->merge($assignedStaffIds);

        $teamIds = Team::query()->where('sport_id', $sport->id)->pluck('id');
        $coachIds = CoachAssignment::query()->whereIn('team_id', $teamIds)->pluck('coach_id')->unique();
        $recipientIds = $recipientIds->merge($coachIds)->unique()->filter()->values();

        User::query()->whereIn('id', $recipientIds)->each(function (User $recipient) use ($application): void {
            $recipient->notify(new SportApplicationSubmitted($application));
        });

        return redirect()->route('student.sports.index')
            ->with('status', 'Application submitted for '.$sport->name.'. Staff have been notified.');
    }

    public function withdraw(Sport $sport): RedirectResponse
    {
        $user = auth()->user();
        abort_unless((int) $sport->organization_id === (int) $user->organization_id, 403);

        $application = SportApplication::query()
            ->where('sport_id', $sport->id)
            ->where('user_id', $user->id)
            ->first();

        if (! $application || $application->status !== 'pending') {
            return redirect()->route('student.sports.index')
                ->with('status', 'No pending application to withdraw.');
        }

        $application->update(['status' => 'withdrawn']);

        return redirect()->route('student.sports.index')
            ->with('status', 'Application withdrawn for '.$sport->name.'.');
    }

    public function leave(Sport $sport): RedirectResponse
    {
        $user = auth()->user();
        abort_unless((int) $sport->organization_id === (int) $user->organization_id, 403);

        $user->sports()->detach($sport->id);

        SportApplication::query()->where('sport_id', $sport->id)->where('user_id', $user->id)->delete();

        return redirect()->route('student.sports.index')
            ->with('status', 'You left '.$sport->name.'.');
    }
}
