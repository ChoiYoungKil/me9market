<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RegistrationConsent
{
    public function capture(Request $request, array $fields = ['terms_service', 'terms_privacy', 'terms_third_party']): array
    {
        if (! app(ShopChannelRuntime::class)->canRegister()) {
            throw ValidationException::withMessages([$fields[0] => '회원가입 약관을 준비 중입니다. 판매자에게 문의해 주세요.']);
        }
        $request->validate(array_fill_keys($fields, 'accepted') + ['marketing_opt_in' => 'nullable|boolean', 'notification_opt_in' => 'nullable|boolean']);
        foreach (['marketing', 'notification'] as $optional) {
            $url = config('shop_channel.'.$optional.'_url');
            if (app()->environment('production') && $request->boolean($optional.'_opt_in')
                && (! filter_var($url, FILTER_VALIDATE_URL) || parse_url($url, PHP_URL_SCHEME) !== 'https')) {
                throw ValidationException::withMessages([$optional.'_opt_in' => '선택 동의 안내 문서가 준비되지 않았습니다. 해당 동의를 해제해 주세요.']);
            }
        }
        $acceptedAt = now();

        return [
            'terms_accepted_at' => $acceptedAt,
            'marketing_opt_in' => $request->boolean('marketing_opt_in'),
            'notification_opt_in' => $request->boolean('notification_opt_in'),
            'terms_snapshot' => [
                'version' => config('shop_channel.terms_version'),
                'documents_published' => app(ShopChannelRuntime::class)->hasPublishedRegistrationPolicies(),
                'published_documents_required' => (bool) config('shop_channel.require_published_terms', true),
                'terms_url' => config('shop_channel.terms_url'),
                'privacy_url' => config('shop_channel.privacy_url'),
                'third_party_url' => config('shop_channel.third_party_url'),
                'terms_service' => true, 'terms_privacy' => true, 'terms_third_party' => true,
                'marketing_url' => $request->boolean('marketing_opt_in') ? config('shop_channel.marketing_url') : null,
                'notification_url' => $request->boolean('notification_opt_in') ? config('shop_channel.notification_url') : null,
                'marketing_opt_in' => $request->boolean('marketing_opt_in'),
                'notification_opt_in' => $request->boolean('notification_opt_in'),
                'accepted_at' => $acceptedAt->toIso8601String(),
            ],
        ];
    }
}
