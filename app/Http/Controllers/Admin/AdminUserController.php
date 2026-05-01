<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', User::class);

        $orgId = auth()->user()->organization_id;

        $users = User::query()
            ->where('organization_id', $orgId)
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function edit(User $user): View
    {
        $this->authorize('view', $user);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('updateRole', $user);

        $validated = $request->validate([
            'role' => ['required', 'string', 'in:admin,coach,instructor,student'],
        ]);

        if ($user->role === 'admin' && $validated['role'] !== 'admin') {
            $admins = User::query()
                ->where('organization_id', $request->user()->organization_id)
                ->where('role', 'admin')
                ->count();

            if ($admins <= 1) {
                return back()->withErrors(['role' => 'You cannot demote the only administrator.']);
            }
        }

        $user->update(['role' => $validated['role']]);

        activity()
            ->performedOn($user)
            ->causedBy($request->user())
            ->withProperties(['new_role' => $validated['role']])
            ->log('user_role_updated');

        return redirect()->route('admin.users.index')
            ->with('status', 'User role updated.');
    }
}
