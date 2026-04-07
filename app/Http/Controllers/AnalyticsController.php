<?php

namespace App\Http\Controllers;

use App\Models\Sport;
use App\Models\User;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function index(): View
    {
        $sports = Sport::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $students = User::query()
            ->where('role', 'student')
            ->orderBy('name')
            ->limit(500)
            ->get(['id', 'name', 'email']);

        return view('analytics.index', compact('sports', 'students'));
    }
}
