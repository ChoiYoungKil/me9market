<?php

namespace App\Services;

use App\Models\PointTransaction;
use App\Models\ShopChannelProduct;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class MemberWithdrawal
{
    public function sharedSellingCount(User $user): int
    {
        if (! $user->vendor_id) {
            return 0;
        }

        return ShopChannelProduct::where('status', 1)
            ->where('approval_status', 'approved')
            ->whereHas('product', fn ($query) => $query->where('vendor_id', $user->vendor_id)->where('status', 1))
            ->whereHas('shopChannel', fn ($query) => $query->where('vendor_id', '!=', $user->vendor_id)->where('status', 1))
            ->count();
    }

    public function withdraw(User $member, string $reason, ?string $detail): array
    {
        return DB::transaction(function () use ($member, $reason, $detail) {
            $user = User::whereKey($member->id)->lockForUpdate()->firstOrFail();
            if ((int) $user->status !== 1) {
                throw ValidationException::withMessages(['account' => '이미 탈퇴한 회원입니다.']);
            }
            if ($this->sharedSellingCount($user) > 0) {
                throw ValidationException::withMessages(['selling' => '내 상품을 다른 회원사에서 판매 중일 때는 탈퇴가 거부 됩니다.']);
            }

            $wallets = PointTransaction::where('user_id', $user->id)
                ->select('shop_channel_id')->selectRaw('SUM(points) as balance')->groupBy('shop_channel_id')->get();
            $expired = 0;
            foreach ($wallets as $wallet) {
                $balance = (int) $wallet->balance;
                if ($balance > 0) {
                    PointTransaction::create([
                        'user_id' => $user->id, 'shop_channel_id' => $wallet->shop_channel_id,
                        'type' => 'withdraw_expire', 'points' => -$balance,
                        'description' => '회원 탈퇴에 따른 잔여 포인트 소멸',
                        'reference_key' => 'withdraw:'.$user->id.':'.($wallet->shop_channel_id ?? 'me9'),
                    ]);
                    $expired += $balance;
                }
            }
            DB::table('member_withdrawals')->insert([
                'user_id' => $user->id, 'reason' => $reason, 'reason_detail' => $reason === 'reason_other' ? $detail : null,
                'expired_points' => $expired, 'withdrawn_at' => now(),
            ]);
            $user->forceFill(['status' => 0, 'remember_token' => null])->save();
            if (Schema::hasTable('oauth_access_tokens')) {
                $tokenIds = DB::table('oauth_access_tokens')->where('user_id', $user->id)->pluck('id');
                DB::table('oauth_access_tokens')->whereIn('id', $tokenIds)->update(['revoked' => true]);
                if (Schema::hasTable('oauth_refresh_tokens')) {
                    DB::table('oauth_refresh_tokens')->whereIn('access_token_id', $tokenIds)->update(['revoked' => true]);
                }
            }

            return ['name' => $user->name, 'username' => $user->username ?: $user->email,
                'date' => now()->format('Y년 m월 d일'), 'expires_at' => now()->addMinutes(10)->timestamp];
        });
    }
}
