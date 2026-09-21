<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = $request->user() ?: auth()->user();

        // If no specific roles passed, just check authenticated
        if (empty($roles)) {
            return $next($request);
        }

        // Check if user has any of the required roles
        if (!$user || !$user->hasRole($roles)) {
            abort(Response::HTTP_FORBIDDEN, 'Access denied: You do not have the required role to access this page.');
        }

        return $next($request);
    }
}
