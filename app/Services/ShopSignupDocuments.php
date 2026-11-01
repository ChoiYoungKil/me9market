<?php

namespace App\Services;

class ShopSignupDocuments
{
    public function all(): array
    {
        $rows = [
            'terms_service' => ['이용약관 동의', true, 'shop', '', null, null],
            'terms_third_party' => ['제3자 정보제공 동의', true, 'seller-provision', '공유상품 구매 시 판매자 제공 동의의 회원가입 적용 범위는 별도 확정이 필요합니다.', null, null],
            'marketing_opt_in' => ['마케팅 활용동의', false, 'member-marketing', '', '구매정보 및 혜택정보 수신 목적 개인정보 이용 동의', '광고성 정보 수신 동의'],
            'notification_opt_in' => ['알림정보 수신동의', false, 'member-marketing', '필수 서비스 안내는 광고성 정보 수신에 관한 선택 동의와 별도로 처리됩니다.', '광고성 정보 수신 동의', null],
            'terms_privacy' => ['개인정보 수집 및 이용에 관한 안내', true, 'member-marketing', '', '회원 가입 개인정보 처리 안내', '구매정보 및 혜택정보 수신 목적 개인정보 이용 동의'],
        ];
        $result = [];
        foreach ($rows as $field => [$label, $required, $slug, $scopeNote, $startHeading, $endHeading]) {
            $path = resource_path('policies/'.$slug.'.json');
            $document = json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
            // Keep the original files intact; omit the document cover and revision note in signup.
            $blocks = array_slice($document['blocks'], 2);
            $headings = array_column($blocks, 'text');
            $start = $startHeading === null ? 0 : array_search($startHeading, $headings, true);
            $end = $endHeading === null ? count($blocks) : array_search($endHeading, $headings, true);
            if ($start === false || $end === false || $end <= $start) {
                throw new \LogicException('Signup policy section is missing: '.$field);
            }
            $document['blocks'] = array_slice($blocks, $start, $end - $start);
            $document += ['slug' => $slug, 'sha256' => hash_file('sha256', $path), 'section' => $startHeading,
                'display_sha256' => hash('sha256', json_encode($document['blocks'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR))];
            $result[$field] = ['label' => $label, 'required' => $required, 'available' => true, 'documents' => [$document], 'scope_note' => $scopeNote];
        }

        return $result;
    }

    public function evidence(): array
    {
        $result = [];
        foreach ($this->all() as $field => $item) {
            $result[$field] = [
                'scope_note' => $item['scope_note'],
                'documents' => array_map(fn ($d) => array_intersect_key($d, array_flip(['title', 'version', 'source', 'draft', 'slug', 'sha256', 'section', 'display_sha256'])), $item['documents']),
            ];
        }

        return $result;
    }

    public function canRegister(): bool
    {
        if (! config('shop_channel.signup_terms_approved', false)) {
            return false;
        }
        if (! app(ShopChannelRuntime::class)->canRegister()) {
            return false;
        }
        if (! app()->environment('production')) {
            return true;
        }
        foreach ($this->all() as $item) {
            if ($item['required'] && ($item['scope_note'] !== '' || collect($item['documents'])->contains(fn ($d) => $d['draft'] ?? true))) {
                return false;
            }
        }

        return true;
    }
}
