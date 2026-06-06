<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->role === 'student' && $user->approval_status !== 'approved') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your account is pending approval.'], 403);
            }
            return redirect()->route('approval.pending');
        }

        return $next($request);
    }
}
