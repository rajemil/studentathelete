<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\YearLevel;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminSettingsController extends Controller
{
    public function index(): View
    {
        $orgId = auth()->user()->organization_id;

        $courses = Course::where('organization_id', $orgId)->orderBy('name')->get();
        $yearLevels = YearLevel::where('organization_id', $orgId)->orderBy('name')->get();
        $sections = Section::where('organization_id', $orgId)->orderBy('name')->get();

        return view('admin.settings.index', compact('courses', 'yearLevels', 'sections'));
    }

    // COURSES
    public function storeCourse(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
        ]);

        Course::create([
            'organization_id' => auth()->user()->organization_id,
            'name' => $validated['name'],
            'code' => $validated['code'],
        ]);

        return back()->with('status', 'Course added successfully.');
    }

    public function destroyCourse(Course $course): RedirectResponse
    {
        abort_unless($course->organization_id === auth()->user()->organization_id, 403);
        $course->delete();
        return back()->with('status', 'Course removed.');
    }

    // YEAR LEVELS
    public function storeYearLevel(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        YearLevel::create([
            'organization_id' => auth()->user()->organization_id,
            'name' => $validated['name'],
        ]);

        return back()->with('status', 'Year level added successfully.');
    }

    public function destroyYearLevel(YearLevel $yearLevel): RedirectResponse
    {
        abort_unless($yearLevel->organization_id === auth()->user()->organization_id, 403);
        $yearLevel->delete();
        return back()->with('status', 'Year level removed.');
    }

    // SECTIONS
    public function storeSection(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Section::create([
            'organization_id' => auth()->user()->organization_id,
            'name' => $validated['name'],
        ]);

        return back()->with('status', 'Section added successfully.');
    }

    public function destroySection(Section $section): RedirectResponse
    {
        abort_unless($section->organization_id === auth()->user()->organization_id, 403);
        $section->delete();
        return back()->with('status', 'Section removed.');
    }
}
