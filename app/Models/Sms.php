<?php

namespace App\Models;

use App\Services\ChannelPointService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class Sms extends Model
{
    use HasFactory;

    // Sending an offline SMS using an SMS API
    public static function sendSms($message, $mobile, ?int $vendorId = null, ?int $shopChannelId = null, int $pointPerMessage = 20, bool $debitPoints = true)
    {
        if ($vendorId && $debitPoints) {
            $debited = app(ChannelPointService::class)->recordSmsDebit(
                $vendorId,
                1,
                $pointPerMessage,
                $shopChannelId,
                '문자 발송 포인트 차감'
            );

            if (! $debited) {
                return false;
            }
        }

        if (config('services.sms.driver') === 'log') {
            Log::info('SMS log driver', ['mobile' => $mobile, 'message' => $message]);

            return 'logged';
        }

        $param = [
            'authorization' => config('services.sms.authorization'),
            'sender_id' => config('services.sms.sender_id'),
            'message' => $message,
            'numbers' => $mobile,
            'username' => config('services.sms.username'),
            'password' => config('services.sms.password'),
            'language' => 'english',
            'route' => 'p',
        ];
        if (empty($param['authorization']) || empty($param['sender_id'])) {
            Log::error('SMS provider credentials are not configured.');

            return false;
        }

        try {
            $response = Http::timeout(10)
                ->connectTimeout(5)
                ->retry(2, 200)
                ->get((string) config('services.sms.endpoint'), $param);

            if (! $response->successful()) {
                Log::error('SMS provider request failed.', ['status' => $response->status()]);

                return false;
            }

            return $response->body();
        } catch (\Throwable $e) {
            report($e);

            return false;
        }
    }
}
