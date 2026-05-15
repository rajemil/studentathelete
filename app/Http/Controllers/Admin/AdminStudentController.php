<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\StudentWelcomeMail;
use App\Models\Profile;
use App\Models\Sport;
use App\Models\User;
use App\Models\Course;
use App\Models\YearLevel;
use App\Models\Section;
use App\Support\AccessCode;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminStudentController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $orgId = auth()->user()->organization_id;

        $students = User::query()
            ->where('organization_id', $orgId)
            ->where('role', 'student')
            ->with(['sports', 'profile'])
            ->orderByDesc('created_at')
            ->paginate(20);

        $courses = Course::where('organization_id', $orgId)->orderBy('name')->get();
        $yearLevels = YearLevel::where('organization_id', $orgId)->orderBy('name')->get();
        $sections = Section::where('organization_id', $orgId)->orderBy('name')->get();

        return view('admin.students.index', compact('students', 'courses', 'yearLevels', 'sections'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $orgId = $request->user()->organization_id;

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'birthdate' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:male,female,other,prefer_not_to_say'],
            'address' => ['nullable', 'string', 'max:255'],
            'height_cm' => ['nullable', 'numeric', 'min:50', 'max:300'],
            'weight_kg' => ['nullable', 'numeric', 'min:10', 'max:350'],
            'photo' => ['nullable', 'file', 'image', 'max:5120'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'year_level_id' => ['nullable', 'integer', 'exists:year_levels,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
        ]);

        $plainCode = AccessCode::generate(6);

        $user = User::query()->create([
            'organization_id' => $orgId,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => 'student',
            'password' => Hash::make($plainCode),
            'email_verified_at' => CarbonImmutable::now(),
        ]);

        $birthdate = isset($validated['birthdate']) ? CarbonImmutable::parse($validated['birthdate']) : null;
        $computedAge = $birthdate ? (int) $birthdate->age : null;

        $height = isset($validated['height_cm']) ? (float) $validated['height_cm'] : null;
        $weight = isset($validated['weight_kg']) ? (float) $validated['weight_kg'] : null;
        $bmi = ($height && $weight && $height > 0) ? round($weight / (($height / 100.0) ** 2), 2) : null;

        $profile = Profile::query()->create([
            'user_id' => $user->id,
            'birthdate' => $birthdate?->toDateString(),
            'age' => $computedAge,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'height_cm' => $height,
            'weight_kg' => $weight,
            'bmi' => $bmi,
            'course_id' => $validated['course_id'] ?? null,
            'year_level_id' => $validated['year_level_id'] ?? null,
            'section_id' => $validated['section_id'] ?? null,
        ]);

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('student-photos', 'public');
            $profile->update(['photo_path' => $path]);
        }

        $allowedSportIds = Sport::query()
            ->where('organization_id', $orgId)
            ->pluck('id')
            ->map(fn ($v) => (int) $v)
            ->all();

        $desiredSportIds = collect($validated['sport_ids'] ?? [])
            ->map(fn ($v) => (int) $v)
            ->filter(fn (int $id) => in_array($id, $allowedSportIds, true))
            ->unique()
            ->values();

        $user->sports()->sync($desiredSportIds->all());

        activity()
            ->performedOn($user)
            ->causedBy($request->user())
            ->log('student_created');

        try {
            Mail::to($user->email)->send(new StudentWelcomeMail($user, $plainCode));
        } catch (\Throwable $e) {
            Log::error('student_welcome_mail_failed', ['user_id' => $user->id, 'exception' => $e->getMessage()]);

            return redirect()->route('admin.students.index')
                ->with('status', 'Student created, but the welcome email could not be sent. Share this access code with the student (shown once).')
                ->with('new_student_code', $plainCode);
        }

        return redirect()->route('admin.students.index')
            ->with('status', 'Student account created. A 6-character access code was sent to their email.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->ensureStudent($user);
        $this->authorize('updateRole', $user);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8'],
            'birthdate' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:male,female,other,prefer_not_to_say'],
            'address' => ['nullable', 'string', 'max:255'],
            'height_cm' => ['nullable', 'numeric', 'min:50', 'max:300'],
            'weight_kg' => ['nullable', 'numeric', 'min:10', 'max:350'],
            'photo' => ['nullable', 'file', 'image', 'max:5120'],
            'course_id' => ['nullable', 'integer', 'exists:courses,id'],
            'year_level_id' => ['nullable', 'integer', 'exists:year_levels,id'],
            'section_id' => ['nullable', 'integer', 'exists:sections,id'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (! empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        $birthdate = isset($validated['birthdate']) ? CarbonImmutable::parse($validated['birthdate']) : null;
        $computedAge = $birthdate ? (int) $birthdate->age : null;

        $height = isset($validated['height_cm']) ? (float) $validated['height_cm'] : null;
        $weight = isset($validated['weight_kg']) ? (float) $validated['weight_kg'] : null;
        $bmi = ($height && $weight && $height > 0) ? round($weight / (($height / 100.0) ** 2), 2) : null;

        $profile = $user->profile ?: Profile::query()->create(['user_id' => $user->id]);
        $profile->fill([
            'birthdate' => $birthdate?->toDateString(),
            'age' => $computedAge,
            'gender' => $validated['gender'] ?? null,
            'address' => $validated['address'] ?? null,
            'height_cm' => $height,
            'weight_kg' => $weight,
            'bmi' => $bmi,
            'course_id' => $validated['course_id'] ?? null,
            'year_level_id' => $validated['year_level_id'] ?? null,
            'section_id' => $validated['section_id'] ?? null,
        ])->save();

        if ($request->hasFile('photo')) {
            if ($profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }
            $path = $request->file('photo')->store('student-photos', 'public');
            $profile->update(['photo_path' => $path]);
        }

        // Sports are now chosen by students themselves.
        // $user->sports()->sync($desiredSportIds->all());

        activity()
            ->performedOn($user)
            ->causedBy($request->user())
            ->log('student_updated');

        return redirect()->route('admin.students.index')
            ->with('status', 'Student updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->ensureStudent($user);
        $this->authorize('delete', $user);

        if ($user->profile?->photo_path) {
            Storage::disk('public')->delete($user->profile->photo_path);
        }

        activity()
            ->performedOn($user)
            ->causedBy($request->user())
            ->log('student_deleted');

        $user->delete();

        return redirect()->route('admin.students.index')
            ->with('status', 'Student removed.');
    }

    private function ensureStudent(User $user): void
    {
        abort_unless(
            $user->role === 'student'
            && (int) $user->organization_id === (int) auth()->user()->organization_id,
            404
        );
    }
}
