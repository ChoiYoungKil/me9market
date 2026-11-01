<?php

namespace App\Services;

use App\Models\ShopChannel;
use App\Models\ShopChannelPrivateAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class ShopEntry
{
    public function context(Request $request): ?ShopChannel
    {
        $code = $request->input('channel', $request->session()->get('shop_entry_channel_code'));
        if ($code !== null && ! is_string($code)) {
            throw ValidationException::withMessages(['channel' => 'Shop 채널 주소를 확인해 주세요.']);
        }
        if (filled($code)) {
            $shop = ShopChannel::where('channel_code', $code)->first();

            return $shop && app(ShopChannelRuntime::class)->isChannelAvailable($shop) ? $shop : null;
        }
        $runtime = app(ShopChannelRuntime::class);

        return $runtime->hasActiveChannelAccess() ? $runtime->currentChannel() : null;
    }

    public function remember(ShopChannel $shop): void
    {
        session(['shop_entry_channel_code' => $shop->channel_code]);
    }

    public function restorePublicAccess(?ShopChannel $shop): void
    {
        if ($shop && (int) $shop->is_public === 1) {
            $runtime = app(ShopChannelRuntime::class);
            if (! $runtime->hasActiveChannelAccess() || $runtime->currentChannel()->id !== $shop->id) {
                $runtime->enterChannel($shop->channel_code);
            }
        }
    }

    public function verify(Request $request, ShopChannel $shop): ShopChannelPrivateAccess
    {
        $data = $request->validate(['phone' => ['required', 'string', 'max:30'], 'entry_code' => ['required', 'string', 'max:128']]);
        $phone = ShopChannelPrivateAccess::normalizePhone($data['phone']);
        $key = 'shop-entry:'.hash('sha256', $request->ip().'|'.$shop->id.'|'.$phone);
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['entry_code' => '입장 확인을 여러 번 시도했습니다. 잠시 후 다시 시도해 주세요.']);
        }
        RateLimiter::hit($key, 60);
        $access = $shop->privateAccesses()->where('phone_normalized', $phone)->first();
        if (! preg_match('/^01[016789][0-9]{7,8}$/', $phone) || ! $access || ! hash_equals((string) $access->entry_code, $data['entry_code'])) {
            throw ValidationException::withMessages(['entry_code' => '입장 정보가 일치하지 않습니다. 입장 정보를 확인하여 주세요.']);
        }
        RateLimiter::clear($key);

        return $access;
    }

    public function destination(?ShopChannel $shop)
    {
        $runtime = app(ShopChannelRuntime::class);
        if ($shop && $runtime->hasActiveChannelAccess() && $runtime->currentChannel()->id === $shop->id) {
            return redirect()->route('shop.channel_main');
        }

        return redirect()->route('shop.gate', $shop ? ['channel' => $shop->channel_code] : []);
    }
}
