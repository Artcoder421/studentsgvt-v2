<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login')->with('error', 'Please sign in to access that area.');
        }

        if (! $user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'Your account has been suspended. Please contact SONIT Administration.');
        }

        if (! empty($roles) && ! in_array($user->role, $roles, true)) {
            abort(403, 'You do not have the required role privileges to access this resource.');
        }

        return $next($request);
    }
}
