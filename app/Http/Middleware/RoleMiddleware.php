<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Usage: ->middleware('role:admin') atau ->middleware('role:admin,pic')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role;

        if (! in_array($userRole, $roles)) {
            return redirect()->route(match ($userRole) {
                'admin' => 'admin.dashboard',
                'pic' => 'pic.dashboard',
                'guru', 'organisasi' => 'guru.dashboard',
                default => 'calendar.public',
            });
        }

        return $next($request);
    }
}
