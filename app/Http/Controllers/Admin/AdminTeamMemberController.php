<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TeamMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminTeamMemberController extends Controller
{
    public function index(): View
    {
        $members = TeamMember::where('organization_id', auth()->user()->organization_id)
            ->latest()
            ->paginate(15);

        return view('admin.team-members.index', compact('members'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TeamMember::class);

        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'role'        => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imagePath = null;
        if ($request->hasFile('photo')) {
            $imagePath = $request->file('photo')->store('team_members', 'public');
        }

        TeamMember::create([
            'organization_id' => auth()->user()->organization_id, // Hard-assigned server-side
            'name'            => $validated['name'],
            'role'            => $validated['role'],
            'description'     => $validated['description'],
            'image_path'      => $imagePath,
        ]);

        return back()->with('status', 'Team member added successfully.');
    }

    public function update(Request $request, TeamMember $teamMember): RedirectResponse
    {
        $this->authorize('update', $teamMember);

        $validated = $request->validate([
            'name'        => 'required|string|max:150',
            'role'        => 'required|string|max:100',
            'description' => 'nullable|string|max:1000',
            'photo'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            // Delete old image
            if ($teamMember->image_path) {
                Storage::disk('public')->delete($teamMember->image_path);
            }
            $teamMember->image_path = $request->file('photo')->store('team_members', 'public');
        }

        $teamMember->update([
            'name'        => $validated['name'],
            'role'        => $validated['role'],
            'description' => $validated['description'],
        ]);

        return back()->with('status', 'Team member updated successfully.');
    }

    public function destroy(TeamMember $teamMember): RedirectResponse
    {
        $this->authorize('delete', $teamMember);

        if ($teamMember->image_path) {
            Storage::disk('public')->delete($teamMember->image_path);
        }

        $teamMember->delete();

        return back()->with('status', 'Team member removed.');
    }
}
