<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $role = strtolower((string) ($request->user()?->role ?? 'student'));

        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'coach' => redirect()->route('coach.dashboard'),
            default => redirect()->route('student.dashboard'),
        };
    }
}

