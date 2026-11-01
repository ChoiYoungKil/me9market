<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\ChannelMemberAccess;
use App\Services\MemberRegistration;
use App\Services\RegistrationConsent;
use App\Services\ShopChannelRuntime;
use App\Services\ShopSignupDocuments;
use App\Services\SocialLoginProvider;
use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class SocialLoginController extends Controller
{
    public function start(Request $request, string $provider, SocialLoginProvider $service)
    {
        if (Auth::check()) {
            $context = in_array($request->input('context'), ['channel', 'shop'], true) ? $request->input('context') : 'member';
            $runtime = app(ShopChannelRuntime::class);

            return $this->destination(['context' => $context, 'shop_channel_id' => $context === 'shop' && $runtime->hasActiveChannelAccess() ? $runtime->currentChannel()->id : null]);
        }

        return $this->authorizeProvider($request, $provider, $service);
    }

    public function connect(Request $request, string $provider, SocialLoginProvider $service)
    {
        if ($confirmation = $this->confirmPassword($request)) {
            return $confirmation;
        }
        abort_unless((int) $request->user()->status === 1, 403);

        return $this->authorizeProvider($request, $provider, $service, $request->user()->id);
    }

    private function authorizeProvider(Request $request, string $provider, SocialLoginProvider $service, ?int $userId = null)
    {
        $context = ! $userId && in_array($request->input('context'), ['channel', 'shop'], true) ? $request->input('context') : 'member';
        $service->requireReady($provider);
        if ($service->blockedOnHttp($provider)) {
            return $this->failed(SocialLoginProvider::GOOGLE_HTTPS_NOTICE, $context);
        }
        $state = Str::random(64);
        $verifier = Str::random(80);
        $runtime = app(ShopChannelRuntime::class);
        if ($context === 'shop' && ! $runtime->hasActiveChannelAccess()) {
            return $this->failed('Shop 채널에 입장한 후 소셜 로그인을 진행해 주세요.', 'shop');
        }
        $shopId = $request->input('context') === 'shop' && $runtime->hasActiveChannelAccess() ? $runtime->currentChannel()->id : null;
        $request->session()->forget('social_signup');
        $request->session()->put('social_oauth.'.$provider, ['state_hash' => hash('sha256', $state), 'verifier' => $verifier,
            'created_at' => now()->timestamp, 'user_id' => $userId, 'shop_channel_id' => $shopId, 'context' => $context]);

        return redirect()->away($service->authorization($provider, $state, $verifier));
    }

    public function callback(Request $request, string $provider, SocialLoginProvider $service)
    {
        $service->requireReady($provider);
        $flow = $request->session()->pull('social_oauth.'.$provider);
        $context = is_array($flow) && in_array($flow['context'] ?? '', ['channel', 'shop'], true) ? $flow['context'] : 'member';
        $state = $request->query('state');
        if (! is_array($flow) || ! is_string($state) || ! hash_equals($flow['state_hash'], hash('sha256', $state))
            || now()->timestamp - $flow['created_at'] > 600) {
            return $this->failed('로그인 요청이 만료되었거나 유효하지 않습니다. 처음부터 다시 시도해 주세요.', $context);
        }
        if ($request->filled('error')) {
            return $this->failed('소셜 로그인이 취소되었습니다.', $context);
        }
        if (! is_string($request->query('code')) || $request->query('code') === '' || strlen($request->query('code')) > 4096) {
            return $this->failed('소셜 로그인 응답을 확인할 수 없습니다. 다시 시도해 주세요.', $context);
        }
        $linkUserId = $flow['user_id'];
        if (($linkUserId && (int) Auth::id() !== (int) $linkUserId) || (! $linkUserId && Auth::check())) {
            return $this->failed('로그인 상태가 변경되었습니다. 다시 시도해 주세요.', $context);
        }
        if ($context === 'shop' && ! $this->shopFlowAvailable($flow)) {
            return $this->failed('Shop 채널 입장 정보가 변경되었거나 만료되었습니다. 다시 입장해 주세요.', 'shop');
        }
        try {
            $profile = $service->profile($provider, $request->query('code'), $state, $flow['verifier']);
        } catch (ValidationException) {
            return $this->failed('소셜 인증을 완료하지 못했습니다. 잠시 후 다시 시도해 주세요.', $context);
        }
        $identity = SocialAccount::with('user')->where('provider', $provider)->where('provider_user_id', $profile['provider_user_id'])->first();
        if ($linkUserId) {
            if ((int) Auth::user()->status !== 1) {
                return $this->failed('이용할 수 없는 회원 계정입니다.', $context);
            }
            if ($identity && (int) $identity->user_id !== (int) $linkUserId) {
                return redirect()->route('mypage.profile')->withErrors(['social' => '이미 다른 회원에게 연결된 소셜 계정입니다.']);
            }
            try {
                SocialAccount::firstOrCreate(['user_id' => $linkUserId, 'provider' => $provider], ['provider_user_id' => $profile['provider_user_id']]);
                $linked = SocialAccount::where('user_id', $linkUserId)->where('provider', $provider)->firstOrFail();
                if ($linked->provider_user_id !== $profile['provider_user_id']) {
                    throw new \RuntimeException('A different provider identity is already linked');
                }
            } catch (\Throwable) {
                return redirect()->route('mypage.profile')->withErrors(['social' => '이미 연결된 계정이 있습니다. 연결 상태를 확인해 주세요.']);
            }
            $request->session()->regenerate();

            return redirect()->route('mypage.profile')->with('success_message', '소셜 계정이 연결되었습니다.');
        }
        if ($identity) {
            if (! $identity->user || (int) $identity->user->status !== 1 || $identity->user->temporaryPasswordIsExpired()) {
                return $this->failed('이용할 수 없는 회원 계정입니다.', $context);
            }
            Auth::login($identity->user);
            $request->session()->regenerate();

            return $this->destination($flow);
        }
        if (! $profile['email']) {
            return $this->failed('가입하려면 소셜 계정의 이메일 제공 동의가 필요합니다. 기존 회원은 로그인 후 회원정보에서 소셜 계정을 연결해 주세요.', $context);
        }
        if (User::where('email', $profile['email'])->exists()) {
            return $this->failed('같은 이메일로 가입된 회원이 있습니다. 기존 방식으로 로그인한 뒤 회원정보에서 소셜 계정을 연결해 주세요.', $context);
        }
        $request->session()->put('social_signup', $profile + ['created_at' => now()->timestamp, 'shop_channel_id' => $flow['shop_channel_id'], 'context' => $context]);

        return redirect()->route('social.signup');
    }

    public function signup(Request $request)
    {
        $pending = $this->pending($request);
        if (($pending['context'] ?? '') === 'shop') {
            return response()->view('shop.social_signup', [
                'pending' => $pending, 'shop' => app(ShopChannelRuntime::class)->currentChannel(),
                'canRegister' => app(ShopSignupDocuments::class)->canRegister(), 'isPreview' => false,
                'documents' => app(ShopSignupDocuments::class)->all(),
            ])->header('Cache-Control', 'no-store, private')->header('Referrer-Policy', 'no-referrer');
        }

        return view('auth.social_signup', ['pending' => $pending, 'canRegister' => app(ShopChannelRuntime::class)->canRegister()]);
    }

    public function register(Request $request, RegistrationConsent $consent)
    {
        $pending = $this->pending($request);
        $shopSignup = ($pending['context'] ?? '') === 'shop';
        if ($shopSignup && ! app(ShopSignupDocuments::class)->canRegister()) {
            throw ValidationException::withMessages(['terms_service' => '회원가입 약관의 확정본과 적용 범위를 준비 중입니다. 판매자에게 문의해 주세요.']);
        }
        $data = $request->validate(['name' => ['required', 'string', 'max:100', function ($attribute, $value, $fail) {
            if (trim($value) === '') {
                $fail('이름을 입력해 주세요.');
            }
        }], 'gender' => 'nullable|in:Male,Female']);
        $evidence = $consent->capture($request);
        if ($shopSignup) {
            $evidence['terms_snapshot']['signup_context'] = 'shop';
            $evidence['terms_snapshot']['shop_channel_id'] = $pending['shop_channel_id'];
            $evidence['terms_snapshot']['displayed_documents'] = app(ShopSignupDocuments::class)->evidence();
            $evidence['terms_snapshot']['documents_published'] = app()->environment('production') && app(ShopSignupDocuments::class)->canRegister();
        }
        try {
            $user = DB::transaction(function () use ($pending, $data, $evidence) {
                if (User::where('email', $pending['email'])->exists() || SocialAccount::where('provider', $pending['provider'])->where('provider_user_id', $pending['provider_user_id'])->exists()) {
                    throw ValidationException::withMessages(['social' => '이미 가입된 계정입니다. 로그인부터 다시 진행해 주세요.']);
                }
                $user = User::create($evidence + ['name' => $data['name'], 'username' => 's'.Str::random(19), 'email' => $pending['email'],
                    'password' => Hash::make(Str::random(64)), 'type' => 'general', 'status' => 1, 'gender' => $data['gender'] ?? null]);
                if ($pending['email_verified']) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }
                SocialAccount::create(['user_id' => $user->id, 'provider' => $pending['provider'], 'provider_user_id' => $pending['provider_user_id']]);
                if (($pending['context'] ?? '') === 'shop') {
                    $user->forceFill(['member_number' => app(MemberRegistration::class)->memberNumber($user)])->save();
                }

                return $user;
            });
        } catch (QueryException) {
            throw ValidationException::withMessages(['social' => '이미 가입되었거나 연결된 계정입니다. 로그인부터 다시 진행해 주세요.']);
        }
        $request->session()->forget('social_signup');
        Auth::login($user);
        $request->session()->regenerate();
        if ($shopSignup) {
            app(ShopChannelRuntime::class)->recordAuthenticatedVisit();

            return redirect()->route('shop.channel_main')->with('flash_message_success', '간편회원 가입이 완료되었습니다.');
        }

        return redirect()->route('front.member.register.step1');
    }

    public function disconnect(Request $request, string $provider)
    {
        if ($confirmation = $this->confirmPassword($request)) {
            return $confirmation;
        }
        SocialAccount::where('user_id', $request->user()->id)->where('provider', $provider)->firstOrFail()->delete();

        return redirect()->route('mypage.profile')->with('success_message', '소셜 계정 연결을 해제했습니다.');
    }

    private function confirmPassword(Request $request)
    {
        if (now()->timestamp - (int) $request->session()->get('auth.password_confirmed_at', 0) > 900) {
            return redirect()->route('password.confirm')->with('url.intended', route('mypage.profile'));
        }

        return null;
    }

    private function pending(Request $request): array
    {
        $pending = $request->session()->get('social_signup');
        if (! is_array($pending) || now()->timestamp - ($pending['created_at'] ?? 0) > 600 || ! app(SocialLoginProvider::class)->ready($pending['provider'] ?? '')) {
            $request->session()->forget('social_signup');
            if (is_array($pending) && ($pending['context'] ?? '') === 'shop') {
                throw new HttpResponseException($this->failed('가입 요청이 만료되었습니다. 소셜 로그인부터 다시 진행해 주세요.', 'shop'));
            }
            abort(419, '가입 요청이 만료되었습니다. 소셜 로그인부터 다시 진행해 주세요.');
        }
        if (($pending['context'] ?? '') === 'shop' && ! $this->shopFlowAvailable($pending)) {
            $request->session()->forget('social_signup');
            throw new HttpResponseException($this->failed('Shop 채널 입장 정보가 변경되었거나 만료되었습니다. 다시 입장해 주세요.', 'shop'));
        }

        return $pending;
    }

    private function shopFlowAvailable(array $flow): bool
    {
        $runtime = app(ShopChannelRuntime::class);

        return $runtime->hasActiveChannelAccess() && (int) $runtime->currentChannel()->id === (int) ($flow['shop_channel_id'] ?? 0);
    }

    private function destination(array $flow)
    {
        if (($flow['context'] ?? '') === 'channel') {
            try {
                $admin = app(ChannelMemberAccess::class)->administratorFor(Auth::user());
            } catch (ValidationException $exception) {
                return $this->failed(collect($exception->errors())->flatten()->first(), 'channel');
            }
            Auth::guard('admin')->login($admin);
            request()->session()->regenerate();

            return redirect()->route('channel.index');
        }
        $runtime = app(ShopChannelRuntime::class);
        if (($flow['shop_channel_id'] ?? null) && $runtime->hasActiveChannelAccess() && (int) $runtime->currentChannel()->id === (int) $flow['shop_channel_id']) {
            $runtime->recordAuthenticatedVisit();

            return redirect()->route('shop.channel_main');
        }

        return redirect()->route('mypage.dashboard');
    }

    private function failed(string $message, string $context = 'member')
    {
        if ($context === 'shop') {
            return redirect()->route('shop.login')->with('error_message', $message);
        }

        return redirect()->route($context === 'channel' ? 'channel.login' : 'front.member.login')->with('error_message', $message);
    }
}
