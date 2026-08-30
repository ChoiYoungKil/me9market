<?php

namespace App\Models;

use App\Services\ChannelPointService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

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

        $url = rtrim((string) config('services.sms.endpoint'), '?').'?'.http_build_query($param);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $curl_scraped_page = curl_exec($ch);
        $httpStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $curl_scraped_page !== false && $httpStatus >= 200 && $httpStatus < 300
            ? $curl_scraped_page
            : false;
    }
}
