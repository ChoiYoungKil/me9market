<?php

namespace App\Http\Middleware;

use App\Services\ShopChannelRuntime;
use Closure;
use Illuminate\Http\Request;

class EnsureShopChannelAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (! app(ShopChannelRuntime::class)->hasActiveChannelAccess()) {
            return redirect()->route('shop.gate')->with('flash_message_error', 'Shop 채널 입장 후 이용해 주세요.');
        }

        return $next($request);
    }
}
