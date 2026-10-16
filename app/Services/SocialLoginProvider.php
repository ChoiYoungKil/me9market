<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SocialLoginProvider
{
    public const LABELS = ['google' => 'Google', 'naver' => '네이버'];

    private const ENDPOINTS = [
        'google' => ['authorize' => 'https://accounts.google.com/o/oauth2/v2/auth', 'token' => 'https://oauth2.googleapis.com/token', 'profile' => 'https://openidconnect.googleapis.com/v1/userinfo'],
        'naver' => ['authorize' => 'https://nid.naver.com/oauth2.0/authorize', 'token' => 'https://nid.naver.com/oauth2.0/token', 'profile' => 'https://openapi.naver.com/v1/nid/me'],
    ];

    public function ready(string $provider): bool
    {
        if (!isset(self::LABELS[$provider])) {
            return false;
        }
        $config = config('social_login.'.$provider, []);
        $uri = $config['redirect_uri'] ?? '';
        $scheme = parse_url($uri, PHP_URL_SCHEME);
        return ($config['enabled'] ?? false) && filled($config['client_id'] ?? null) && filled($config['client_secret'] ?? null)
            && !preg_match('/^(?:\[(?:redacted|masked)\]|\*+|[•●]+)$/iu', trim((string) $config['client_secret']))
            && filter_var($uri, FILTER_VALIDATE_URL) !== false
            && in_array($scheme, app()->environment('production') ? ['https'] : ['http', 'https'], true)
            && parse_url($uri, PHP_URL_HOST) === parse_url(config('app.url'), PHP_URL_HOST)
            && parse_url($uri, PHP_URL_PORT) === parse_url(config('app.url'), PHP_URL_PORT)
            && parse_url($uri, PHP_URL_PATH) === '/auth/social/'.$provider.'/callback'
            && !parse_url($uri, PHP_URL_QUERY) && !parse_url($uri, PHP_URL_FRAGMENT)
            && !parse_url($uri, PHP_URL_USER);
    }

    public function available(): array
    {
        return array_filter(self::LABELS, fn ($label, $provider) => $this->ready($provider), ARRAY_FILTER_USE_BOTH);
    }

    public function authorization(string $provider, string $state, string $verifier): string
    {
        $this->requireReady($provider);
        $query = ['response_type' => 'code', 'client_id' => config('social_login.'.$provider.'.client_id'),
            'redirect_uri' => config('social_login.'.$provider.'.redirect_uri'), 'state' => $state];
        if ($provider === 'google') {
            $query += ['scope' => 'openid email profile', 'code_challenge_method' => 'S256',
                'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '=')];
        }
        return self::ENDPOINTS[$provider]['authorize'].'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);
    }

    public function profile(string $provider, string $code, string $state, string $verifier): array
    {
        $this->requireReady($provider);
        try {
            $body = ['grant_type' => 'authorization_code', 'client_id' => config('social_login.'.$provider.'.client_id'),
                'client_secret' => config('social_login.'.$provider.'.client_secret'), 'redirect_uri' => config('social_login.'.$provider.'.redirect_uri'), 'code' => $code];
            $body += $provider === 'google' ? ['code_verifier' => $verifier] : ['state' => $state];
            $tokenResponse = Http::asForm()->acceptJson()->withOptions(['allow_redirects' => false])->connectTimeout(5)->timeout(10)
                ->post(self::ENDPOINTS[$provider]['token'], $body);
            $token = $tokenResponse->json('access_token');
            if (!$tokenResponse->successful() || !is_string($token) || $token === '') {
                throw new \RuntimeException('Token exchange failed');
            }
            $response = Http::withToken($token)->acceptJson()->withOptions(['allow_redirects' => false])->connectTimeout(5)->timeout(10)
                ->get(self::ENDPOINTS[$provider]['profile']);
            if (!$response->successful()) {
                throw new \RuntimeException('Profile lookup failed');
            }
            $data = $response->json();
            if ($provider === 'naver') {
                if (($data['resultcode'] ?? '') !== '00') {
                    throw new \RuntimeException('Provider rejected profile');
                }
                $data = $data['response'] ?? [];
            }
            $id = $data[$provider === 'google' ? 'sub' : 'id'] ?? null;
            $email = $data['email'] ?? null;
            if (!is_string($id) || $id === '' || strlen($id) > 191) {
                throw new \RuntimeException('Profile has no stable identifier');
            }
            if ($provider === 'google' && ($data['email_verified'] ?? false) !== true) {
                $email = null;
            }
            return ['provider' => $provider, 'provider_user_id' => $id,
                'email' => is_string($email) && strlen($email) <= 150 && filter_var($email, FILTER_VALIDATE_EMAIL) ? Str::lower(trim($email)) : null,
                'name' => is_string($data['name'] ?? null) ? mb_substr($data['name'], 0, 100) : '',
                'email_verified' => $provider === 'google' && ($data['email_verified'] ?? false) === true];
        } catch (\Throwable) {
            // Provider responses, authorization codes and tokens must not reach logs or error pages.
            throw ValidationException::withMessages(['social' => '소셜 인증을 완료하지 못했습니다. 잠시 후 다시 시도해 주세요.']);
        }
    }

    public function requireReady(string $provider): void
    {
        abort_unless($this->ready($provider), 404);
    }
}
