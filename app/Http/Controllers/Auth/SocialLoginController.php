<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use App\Services\RegistrationConsent;
use App\Services\ShopChannelRuntime;
use App\Services\SocialLoginProvider;
use Illuminate\Database\QueryException;
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
            return redirect()->route('mypage.dashboard');
        }
        return $this->authorizeProvider($request, $provider, $service);
    }

    public function connect(Request $request, string $provider, SocialLoginProvider $service)
    {
        if ($confirmation = $this->confirmPassword($request)) { return $confirmation; }
        abort_unless((int) $request->user()->status === 1, 403);
        return $this->authorizeProvider($request, $provider, $service, $request->user()->id);
    }

    private function authorizeProvider(Request $request, string $provider, SocialLoginProvider $service, ?int $userId = null)
    {
        $service->requireReady($provider);
        $state = Str::random(64);
        $verifier = Str::random(80);
        $runtime = app(ShopChannelRuntime::class);
        $shopId = $request->input('context') === 'shop' && $runtime->hasActiveChannelAccess() ? $runtime->currentChannel()->id : null;
        $request->session()->forget('social_signup');
        $request->session()->put('social_oauth.'.$provider, ['state_hash' => hash('sha256', $state), 'verifier' => $verifier,
            'created_at' => now()->timestamp, 'user_id' => $userId, 'shop_channel_id' => $shopId]);
        return redirect()->away($service->authorization($provider, $state, $verifier));
    }

    public function callback(Request $request, string $provider, SocialLoginProvider $service)
    {
        $service->requireReady($provider);
        $flow = $request->session()->pull('social_oauth.'.$provider);
        $state = $request->query('state');
        if (!is_array($flow) || !is_string($state) || !hash_equals($flow['state_hash'], hash('sha256', $state))
            || now()->timestamp - $flow['created_at'] > 600) {
            return $this->failed('로그인 요청이 만료되었거나 유효하지 않습니다. 처음부터 다시 시도해 주세요.');
        }
        if ($request->filled('error')) {
            return $this->failed('소셜 로그인이 취소되었습니다.');
        }
        if (!is_string($request->query('code')) || $request->query('code') === '' || strlen($request->query('code')) > 4096) {
            return $this->failed('소셜 로그인 응답을 확인할 수 없습니다. 다시 시도해 주세요.');
        }
        $linkUserId = $flow['user_id'];
        if (($linkUserId && (int) Auth::id() !== (int) $linkUserId) || (!$linkUserId && Auth::check())) {
            return $this->failed('로그인 상태가 변경되었습니다. 다시 시도해 주세요.');
        }
        try {
            $profile = $service->profile($provider, $request->query('code'), $state, $flow['verifier']);
        } catch (ValidationException) {
            return $this->failed('소셜 인증을 완료하지 못했습니다. 잠시 후 다시 시도해 주세요.');
        }
        $identity = SocialAccount::with('user')->where('provider', $provider)->where('provider_user_id', $profile['provider_user_id'])->first();
        if ($linkUserId) {
            if ((int) Auth::user()->status !== 1) {
                return $this->failed('이용할 수 없는 회원 계정입니다.');
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
            if (!$identity->user || (int) $identity->user->status !== 1) {
                return $this->failed('이용할 수 없는 회원 계정입니다.');
            }
            Auth::login($identity->user);
            $request->session()->regenerate();
            return $this->destination($flow);
        }
        if (!$profile['email']) {
            return $this->failed('가입하려면 소셜 계정의 이메일 제공 동의가 필요합니다. 기존 회원은 로그인 후 회원정보에서 소셜 계정을 연결해 주세요.');
        }
        if (User::where('email', $profile['email'])->exists()) {
            return $this->failed('같은 이메일로 가입된 회원이 있습니다. 기존 방식으로 로그인한 뒤 회원정보에서 소셜 계정을 연결해 주세요.');
        }
        $request->session()->put('social_signup', $profile + ['created_at' => now()->timestamp, 'shop_channel_id' => $flow['shop_channel_id']]);
        return redirect()->route('social.signup');
    }

    public function signup(Request $request)
    {
        $pending = $this->pending($request);
        return view('auth.social_signup', ['pending' => $pending, 'canRegister' => app(ShopChannelRuntime::class)->canRegister()]);
    }

    public function register(Request $request, RegistrationConsent $consent)
    {
        $pending = $this->pending($request);
        $data = $request->validate(['name' => 'required|string|max:100']);
        $evidence = $consent->capture($request);
        try {
            $user = DB::transaction(function () use ($pending, $data, $evidence) {
                if (User::where('email', $pending['email'])->exists() || SocialAccount::where('provider', $pending['provider'])->where('provider_user_id', $pending['provider_user_id'])->exists()) {
                    throw ValidationException::withMessages(['social' => '이미 가입된 계정입니다. 로그인부터 다시 진행해 주세요.']);
                }
                $user = User::create($evidence + ['name' => $data['name'], 'username' => 's'.Str::random(19), 'email' => $pending['email'],
                    'password' => Hash::make(Str::random(64)), 'type' => 'general', 'status' => 1]);
                if ($pending['email_verified']) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }
                SocialAccount::create(['user_id' => $user->id, 'provider' => $pending['provider'], 'provider_user_id' => $pending['provider_user_id']]);
                return $user;
            });
        } catch (QueryException) {
            throw ValidationException::withMessages(['social' => '이미 가입되었거나 연결된 계정입니다. 로그인부터 다시 진행해 주세요.']);
        }
        $request->session()->forget('social_signup');
        Auth::login($user);
        $request->session()->regenerate();
        return $this->destination($pending);
    }

    public function disconnect(Request $request, string $provider)
    {
        if ($confirmation = $this->confirmPassword($request)) { return $confirmation; }
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
        if (!is_array($pending) || now()->timestamp - ($pending['created_at'] ?? 0) > 600 || !app(SocialLoginProvider::class)->ready($pending['provider'] ?? '')) {
            $request->session()->forget('social_signup');
            abort(419, '가입 요청이 만료되었습니다. 소셜 로그인부터 다시 진행해 주세요.');
        }
        return $pending;
    }

    private function destination(array $flow)
    {
        $runtime = app(ShopChannelRuntime::class);
        if (($flow['shop_channel_id'] ?? null) && $runtime->hasActiveChannelAccess() && (int) $runtime->currentChannel()->id === (int) $flow['shop_channel_id']) {
            $runtime->recordAuthenticatedVisit();
            return redirect()->route('shop.channel_main');
        }
        return redirect()->route('mypage.dashboard');
    }

    private function failed(string $message)
    {
        return redirect()->route('front.member.login')->with('error_message', $message);
    }
}
