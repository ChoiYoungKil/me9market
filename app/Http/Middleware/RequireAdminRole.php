<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RequireAdminRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $admin = Auth::guard('admin')->user();

        abort_unless($admin && in_array($admin->type, $roles, true), 403);

        return $next($request);
    }
}
