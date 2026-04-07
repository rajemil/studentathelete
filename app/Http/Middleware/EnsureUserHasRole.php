<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Usage: ->middleware('role:admin') or ->middleware('role:admin,coach')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            abort(401);
        }

        $allowed = collect($roles)
            ->filter(fn ($r) => is_string($r) && $r !== '')
            ->map(fn ($r) => strtolower(trim($r)))
            ->values();

        if ($allowed->isEmpty()) {
            return $next($request);
        }

        $userRole = strtolower((string) ($user->role ?? ''));

        abort_unless($allowed->contains($userRole), 403);

        return $next($request);
    }
}

