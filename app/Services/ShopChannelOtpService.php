<?php

namespace App\Services;

use App\Models\ShopChannel;
use App\Models\ShopChannelAccessOtp;
use App\Models\ShopChannelPrivateAccess;
use App\Models\Sms;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ShopChannelOtpService
{
    public function request(string $channelCode, string $phone): ShopChannelAccessOtp
    {
        $access = $this->findAccess($channelCode, $phone);
        $latest = ShopChannelAccessOtp::where('shop_channel_private_access_id', $access->id)
            ->latest('id')
            ->first();
        if ($latest && $latest->sent_at->gt(now()->subMinute())) {
            throw ValidationException::withMessages(['phone' => '인증번호는 1분 후에 다시 요청할 수 있습니다.']);
        }

        $code = app()->environment('testing') ? '123456' : (string) random_int(100000, 999999);
        $otp = ShopChannelAccessOtp::create([
            'shop_channel_private_access_id' => $access->id,
            'phone_normalized' => $access->phone_normalized,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'expires_at' => now()->addMinutes(max(1, (int) config('services.sms.otp_ttl_minutes', 5))),
            'sent_at' => now(),
        ]);

        $message = '['.$access->shopChannel->channel_name.'] Shop 채널 입장 인증번호는 '.$code.'입니다.';
        $response = Sms::sendSms($message, $access->phone, (int) $access->shopChannel->vendor_id, (int) $access->shop_channel_id, 0, false);
        if ($response === false) {
            $otp->delete();
            throw ValidationException::withMessages(['phone' => '인증번호 발송에 실패했습니다.']);
        }

        return $otp;
    }

    public function verify(string $channelCode, string $phone, string $code): ShopChannelPrivateAccess
    {
        $access = $this->findAccess($channelCode, $phone);
        $maxAttempts = max(1, (int) config('services.sms.otp_max_attempts', 5));

        $error = null;

        DB::transaction(function () use ($access, $code, $maxAttempts, &$error) {
            $otp = ShopChannelAccessOtp::where('shop_channel_private_access_id', $access->id)
                ->whereNull('verified_at')
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $otp || $otp->expires_at->isPast()) {
                throw ValidationException::withMessages(['otp' => '인증번호가 만료되었거나 존재하지 않습니다.']);
            }
            if ($otp->attempts >= $maxAttempts) {
                throw ValidationException::withMessages(['otp' => '인증 시도 횟수를 초과했습니다. 인증번호를 다시 요청해 주세요.']);
            }

            $otp->increment('attempts');
            if (! Hash::check($code, $otp->code_hash)) {
                $error = '인증번호가 올바르지 않습니다.';

                return;
            }

            $otp->verified_at = now();
            $otp->save();

        });

        if ($error !== null) {
            throw ValidationException::withMessages(['otp' => $error]);
        }

        return $access;
    }

    private function findAccess(string $channelCode, string $phone): ShopChannelPrivateAccess
    {
        $normalized = ShopChannelPrivateAccess::normalizePhone($phone);
        $shop = ShopChannel::where('channel_code', trim($channelCode))
            ->where('status', 1)
            ->where('is_public', 0)
            ->first();
        $access = $shop?->privateAccesses()
            ->where('phone_normalized', $normalized)
            ->with('shopChannel')
            ->first();

        if (! $access) {
            throw ValidationException::withMessages(['phone' => '해당 채널에 등록된 휴대폰번호가 아닙니다.']);
        }

        return $access;
    }
}
