<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $guard = null)
    {
        if (Auth::user() && Auth::user()->IsAdmin()) {
            return $next($request);
        } else {
            return response()->json(['status' => 'error', 'message' => 'You don\'t have permissions to access this route'], 403);
        }
    }
}
