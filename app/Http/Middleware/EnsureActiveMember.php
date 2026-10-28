<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureActiveMember
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->hasSession() && Auth::guard('web')->check() && (int) Auth::guard('web')->user()->fresh()?->status !== 1) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $next($request);
    }
}
