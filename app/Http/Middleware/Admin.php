<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // This is a middleware we created by ourselves
        // dd(Illuminate\Support\Facades\Auth::class);
        // dd(Illuminate\Support\Facades\Auth::user());
        // dd(auth());
        // dd(auth()->user());

        // Multiple Authentication    // https://laravel.com/docs/9.x/passport#multiple-authentication-guards
        // Determining If The Current User Is Authenticated: https://laravel.com/docs/9.x/authentication#determining-if-the-current-user-is-authenticated
        // Accessing Specific Guard Instances: https://laravel.com/docs/9.x/authentication#accessing-specific-guard-instances
        if (! Auth::guard('admin')->check()) { // If the user making the incoming HTTP request is not authenticated, redirect to login page
            if ($request->is('channel*')) {
                return redirect()->route('channel.login');
            }

            return redirect('/admin/login');
        }

        $admin = Auth::guard('admin')->user();
        if ($admin->type === 'vendor' && $request->is('admin/*')) {
            $vendorRoutes = [
                'admin/dashboard',
                'admin/logout',
                'admin/update-admin-password',
                'admin/check-admin-password',
                'admin/update-admin-details',
                'admin/update-vendor-details/*',
                'admin/products',
                'admin/update-product-status',
                'admin/delete-product/*',
                'admin/add-edit-product',
                'admin/add-edit-product/*',
                'admin/delete-product-image/*',
                'admin/delete-product-video/*',
                'admin/add-edit-attributes/*',
                'admin/update-attribute-status',
                'admin/delete-attribute/*',
                'admin/edit-attributes/*',
                'admin/add-images/*',
                'admin/update-image-status',
                'admin/delete-image/*',
                'admin/coupons',
                'admin/update-coupon-status',
                'admin/delete-coupon/*',
                'admin/add-edit-coupon',
                'admin/add-edit-coupon/*',
                'admin/orders',
                'admin/orders/*',
                'admin/update-order-item-status',
                'admin/settlements/*/export',
                'admin/settlements/*/payout',
                'admin/settlements/*/billing',
            ];

            abort_unless($request->is(...$vendorRoutes), 403);
        }

        return $next($request);
    }
}
