<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $sports = Sport::query()
            ->withCount('students')
            ->orderBy('name')
            ->paginate(12);

        return view('sports.index', compact('sports'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('sports.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', 'unique:sports,name'],
            'slug' => ['nullable', 'string', 'max:140', 'unique:sports,slug'],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']);

        $sport = Sport::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('sports.show', $sport)
            ->with('status', 'Sport created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sport $sport): View
    {
        $sport->loadCount('students');

        $students = $sport->students()
            ->where('role', 'student')
            ->orderBy('name')
            ->paginate(12, ['users.*'], 'students_page');

        $availableStudents = User::query()
            ->where('role', 'student')
            ->whereDoesntHave('sports', fn ($q) => $q->where('sports.id', $sport->id))
            ->orderBy('name')
            ->limit(250)
            ->get(['id', 'name', 'email']);

        return view('sports.show', compact('sport', 'students', 'availableStudents'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sport $sport): View
    {
        return view('sports.edit', compact('sport'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sport $sport): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120', Rule::unique('sports', 'name')->ignore($sport->id)],
            'slug' => ['nullable', 'string', 'max:140', Rule::unique('sports', 'slug')->ignore($sport->id)],
            'description' => ['nullable', 'string', 'max:5000'],
        ]);

        $sport->update([
            'name' => $validated['name'],
            'slug' => $validated['slug'] ?? Str::slug($validated['name']),
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()->route('sports.show', $sport)
            ->with('status', 'Sport updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sport $sport): RedirectResponse
    {
        $sport->delete();

        return redirect()->route('sports.index')
            ->with('status', 'Sport deleted.');
    }
}
