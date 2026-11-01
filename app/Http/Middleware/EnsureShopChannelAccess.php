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
            return redirect()->route('shop.gate', session('shop_entry_channel_code') ? ['channel' => session('shop_entry_channel_code')] : [])->with('flash_message_error', 'Shop 채널 입장 후 이용해 주세요.');
        }

        $shop = app(ShopChannelRuntime::class)->currentChannel();
        if ($shop->is_member_only && ! auth()->check() && ! $request->routeIs('shop.register', 'shop.register.submit')) {
            return redirect()->route('shop.login', ['channel' => $shop->channel_code]);
        }

        return $next($request);
    }
}
