<?php

namespace Tests\Feature;

use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SocialLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.url' => 'http://localhost']);
        foreach (['google', 'naver'] as $provider) {
            config(['social_login.'.$provider => ['enabled' => true, 'client_id' => 'test-client',
                'client_secret' => 'test-secret', 'redirect_uri' => 'http://localhost/auth/social/'.$provider.'/callback']]);
        }
        Http::preventStrayRequests();
    }

    private function begin(string $provider = 'google', bool $connect = false): string
    {
        $response = $connect ? $this->post(route('social.connect', $provider)) : $this->get(route('social.start', $provider));
        $response->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('test-client', $query['client_id']);
        $this->assertSame('code', $query['response_type']);
        if ($provider === 'google') {
            $this->assertSame('S256', $query['code_challenge_method']);
            $verifier = session('social_oauth.google.verifier');
            $this->assertSame(rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='), $query['code_challenge']);
        }
        return $query['state'];
    }

    private function fakeProfile(string $provider = 'google', string $email = 'social@example.com', bool $verified = true): void
    {
        Http::fake([
            '*oauth2.googleapis.com/token' => Http::response(['access_token' => 'test-access-token']),
            '*openidconnect.googleapis.com/v1/userinfo' => Http::response(['sub' => 'provider-123', 'email' => $email, 'email_verified' => $verified, 'name' => 'Social member']),
            '*nid.naver.com/oauth2.0/token' => Http::response(['access_token' => 'test-access-token']),
            '*openapi.naver.com/v1/nid/me' => Http::response(['resultcode' => '00', 'response' => ['id' => 'provider-123', 'email' => $email, 'name' => 'Social member']]),
        ]);
    }

    public function test_unconfigured_providers_are_hidden_and_cannot_start(): void
    {
        config(['social_login.google.enabled' => false, 'social_login.naver.client_secret' => '']);
        $this->get(route('front.member.login'))->assertOk()->assertDontSee(route('social.start', 'google'))->assertDontSee(route('social.start', 'naver'));
        $this->get(route('social.start', 'google'))->assertNotFound();
        $this->get(route('social.start', 'naver'))->assertNotFound();
    }

    public function test_callback_must_match_site_and_use_https_in_production(): void
    {
        config(['social_login.google.redirect_uri' => 'https://other.example/auth/social/google/callback']);
        $this->get(route('social.start', 'google'))->assertNotFound();
        config(['social_login.google.redirect_uri' => 'http://localhost/auth/social/google/callback']);
        $this->app->instance('env', 'production');
        $this->get(route('social.start', 'google'))->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_production_http_naver_can_authenticate_a_linked_member(): void
    {
        $this->app->instance('env', 'production');
        config(['app.url' => 'http://front.example.com',
            'social_login.naver.redirect_uri' => 'http://front.example.com/auth/social/naver/callback']);
        $user = User::factory()->create(['status' => 1]);
        SocialAccount::create(['user_id' => $user->id, 'provider' => 'naver', 'provider_user_id' => 'provider-123']);
        $this->fakeProfile('naver');
        $response = $this->get('http://front.example.com/auth/social/naver/redirect');
        $response->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->assertSame('http://front.example.com/auth/social/naver/callback', $query['redirect_uri']);
        $this->get('http://front.example.com/auth/social/naver/callback?'.http_build_query(['state' => $query['state'], 'code' => 'test-code']))
            ->assertRedirect(route('mypage.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_public_http_shows_google_notice_and_naver_link_without_starting_google_oauth(): void
    {
        $this->app->instance('env', 'production');
        config(['app.url' => 'http://front.example.com',
            'social_login.google.redirect_uri' => 'https://front.example.com/auth/social/google/callback',
            'social_login.naver.redirect_uri' => 'http://front.example.com/auth/social/naver/callback']);
        $this->get('http://front.example.com/member/login')->assertOk()
            ->assertSee('Google로 계속하기')->assertSee('네이버로 계속하기')
            ->assertSee('onclick="alert(', false)->assertSee('social-login-google')
            ->assertSee('http://front.example.com/auth/social/naver/redirect', false)
            ->assertDontSee('href="http://front.example.com/auth/social/google/redirect', false);
        $this->get('http://front.example.com/auth/social/google/redirect')
            ->assertRedirect(route('front.member.login'))->assertSessionHas('error_message', \App\Services\SocialLoginProvider::GOOGLE_HTTPS_NOTICE);
        $this->assertNull(session('social_oauth.google'));
        Http::assertNothingSent();
    }

    public function test_https_google_login_remains_available_without_http_notice(): void
    {
        $this->app->instance('env', 'production');
        config(['app.url' => 'https://front.example.com',
            'social_login.google.redirect_uri' => 'https://front.example.com/auth/social/google/callback']);
        $this->get('https://front.example.com/member/login')->assertOk()
            ->assertSee('href="https://front.example.com/auth/social/google/redirect', false)
            ->assertDontSee('onclick="alert(', false);
        $this->get('https://front.example.com/auth/social/google/redirect')
            ->assertRedirectContains('https://accounts.google.com/o/oauth2/v2/auth?');
    }

    public function test_masked_console_value_is_not_treated_as_a_configured_secret(): void
    {
        foreach (['[redacted]', '[REDACTED]', '[masked]', '********', '••••••'] as $masked) {
            config(['social_login.naver.client_secret' => $masked]);
            $this->assertFalse(app(\App\Services\SocialLoginProvider::class)->ready('naver'));
        }
        $this->get(route('social.start', 'naver'))->assertNotFound();
        Http::assertNothingSent();
    }

    public function test_callback_rejects_invalid_expired_and_replayed_state_without_token_exchange(): void
    {
        $state = $this->begin();
        $this->get(route('social.callback', ['provider' => 'google', 'state' => 'wrong', 'code' => 'code']))->assertRedirect(route('front.member.login'));
        $this->get(route('social.callback', ['provider' => 'google', 'state' => $state, 'code' => 'code']))->assertRedirect(route('front.member.login'));
        $state = $this->begin();
        $this->travel(11)->minutes();
        $this->get(route('social.callback', ['provider' => 'google', 'state' => $state, 'code' => 'code']))->assertRedirect(route('front.member.login'));
        Http::assertNothingSent();
        $this->assertGuest();
    }

    public function test_google_signup_requires_consent_and_uses_only_provider_email(): void
    {
        $this->fakeProfile();
        $state = $this->begin();
        $this->get(route('social.callback', ['provider' => 'google', 'state' => $state, 'code' => 'code']))->assertRedirect(route('social.signup'));
        $this->get(route('social.signup'))->assertOk()->assertSee('social@example.com');
        $this->post(route('social.register'), ['name' => 'Social member'])->assertSessionHasErrors(['terms_service', 'terms_privacy', 'terms_third_party']);
        $this->assertDatabaseCount('social_accounts', 0);
        $this->post(route('social.register'), ['name' => 'Social member', 'email' => 'attacker@example.com',
            'terms_service' => 1, 'terms_privacy' => 1, 'terms_third_party' => 1])->assertRedirect(route('front.member.register.step1'));
        $user = User::where('email', 'social@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertTrue($user->terms_snapshot['terms_service']);
        $this->assertDatabaseHas('social_accounts', ['user_id' => $user->id, 'provider' => 'google', 'provider_user_id' => 'provider-123']);
        $this->assertDatabaseMissing('users', ['email' => 'attacker@example.com']);
        $this->assertNull(session('social_signup'));
        Http::assertSent(fn ($request) => $request->url() === 'https://oauth2.googleapis.com/token' && isset($request['code_verifier']) && $request['client_secret'] === 'test-secret');
    }

    public function test_social_signup_waits_for_production_policies_before_accepting_consents(): void
    {
        $this->app->instance('env', 'production');
        config(['shop_channel.terms_url' => null, 'shop_channel.privacy_url' => null,
            'shop_channel.third_party_url' => null, 'shop_channel.terms_version' => null]);
        $this->fakeProfile('naver');
        $state = $this->begin('naver');
        $this->get(route('social.callback', ['provider' => 'naver', 'state' => $state, 'code' => 'test-code']))
            ->assertRedirect(route('social.signup'));
        $this->get(route('social.signup'))->assertOk()->assertSee('현재 신규 회원가입을 이용할 수 없습니다.')
            ->assertSee('약관 준비 중')->assertSee('aria-describedby="social-registration-unavailable"', false)
            ->assertDontSee('필수 약관에 동의하면 가입됩니다.');
        $data = ['name' => 'Social member', 'terms_service' => 1, 'terms_privacy' => 1, 'terms_third_party' => 1];
        $this->post(route('social.register'), $data)->assertSessionHasErrors('terms_service');
        $this->assertDatabaseCount('social_accounts', 0);
        $this->assertGuest();
        config(['shop_channel.terms_url' => 'https://example.test/terms',
            'shop_channel.privacy_url' => 'https://example.test/privacy',
            'shop_channel.third_party_url' => 'https://example.test/third-party', 'shop_channel.terms_version' => 'test-v1']);
        $this->get(route('social.signup'))->assertOk()->assertSee('동의하고 가입하기')
            ->assertDontSee('social-registration-unavailable')->assertSee('https://example.test/terms');
        $this->post(route('social.register'), $data)->assertRedirect(route('front.member.register.step1'));
        $this->assertAuthenticated();
        $this->assertDatabaseCount('social_accounts', 1);
    }

    public function test_production_signup_can_temporarily_allow_missing_documents_but_still_requires_checkboxes(): void
    {
        $this->app->instance('env', 'production');
        config(['shop_channel.require_published_terms' => false, 'shop_channel.terms_url' => null,
            'shop_channel.privacy_url' => null, 'shop_channel.third_party_url' => null, 'shop_channel.terms_version' => null]);
        $this->fakeProfile('naver');
        $state = $this->begin('naver');
        $this->get(route('social.callback', ['provider' => 'naver', 'state' => $state, 'code' => 'test-code']))
            ->assertRedirect(route('social.signup'));
        $this->get(route('social.signup'))->assertOk()->assertSee('동의하고 가입하기')
            ->assertDontSee('social-registration-unavailable')->assertDontSee('약관 준비 중');
        $this->post(route('social.register'), ['name' => 'Social member'])
            ->assertSessionHasErrors(['terms_service', 'terms_privacy', 'terms_third_party']);
        $this->assertDatabaseCount('social_accounts', 0);
        $this->post(route('social.register'), ['name' => 'Social member', 'terms_service' => 1,
            'terms_privacy' => 1, 'terms_third_party' => 1])->assertRedirect(route('front.member.register.step1'));
        $user = User::where('email', 'social@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertFalse($user->terms_snapshot['documents_published']);
        $this->assertFalse($user->terms_snapshot['published_documents_required']);
        $this->assertNull($user->terms_snapshot['version']);
        $this->assertTrue($user->terms_snapshot['terms_service']);
        $this->assertDatabaseHas('social_accounts', ['user_id' => $user->id, 'provider' => 'naver']);
        config(['shop_channel.require_published_terms' => true]);
        $this->assertFalse(app(\App\Services\ShopChannelRuntime::class)->canRegister());
    }

    public function test_existing_email_is_never_automatically_linked(): void
    {
        $user = User::factory()->create(['email' => 'social@example.com']);
        $this->fakeProfile();
        $state = $this->begin();
        $this->get(route('social.callback', ['provider' => 'google', 'state' => $state, 'code' => 'code']))->assertRedirect(route('front.member.login'))->assertSessionHas('error_message');
        $this->assertGuest();
        $this->assertDatabaseMissing('social_accounts', ['user_id' => $user->id]);
        $this->assertNull(session('social_signup'));
    }

    public function test_linked_naver_identity_logs_in_using_stable_id_even_if_email_changes(): void
    {
        $user = User::factory()->create(['status' => 1]);
        SocialAccount::create(['user_id' => $user->id, 'provider' => 'naver', 'provider_user_id' => 'provider-123']);
        $this->fakeProfile('naver', 'changed@example.com');
        $state = $this->begin('naver');
        $this->get(route('social.callback', ['provider' => 'naver', 'state' => $state, 'code' => 'code']))->assertRedirect(route('mypage.dashboard'));
        $this->assertAuthenticatedAs($user);
        Http::assertSent(fn ($request) => $request->url() === 'https://nid.naver.com/oauth2.0/token' && $request['state'] === $state);
    }

    public function test_disabled_member_cannot_use_social_login(): void
    {
        $user = User::factory()->create(['status' => 0]);
        SocialAccount::create(['user_id' => $user->id, 'provider' => 'google', 'provider_user_id' => 'provider-123']);
        $this->fakeProfile();
        $state = $this->begin();
        $this->get(route('social.callback', ['provider' => 'google', 'state' => $state, 'code' => 'code']))->assertRedirect(route('front.member.login'));
        $this->assertGuest();
    }

    public function test_link_and_disconnect_require_recent_password_confirmation(): void
    {
        $user = User::factory()->create(['status' => 1]);
        $this->actingAs($user)->post(route('social.connect', 'google'))->assertRedirect(route('password.confirm'));
        $this->delete(route('social.disconnect', 'google'))->assertRedirect(route('password.confirm'));
        $this->withSession(['auth.password_confirmed_at' => now()->timestamp]);
        $this->fakeProfile();
        $state = $this->begin('google', true);
        $this->get(route('social.callback', ['provider' => 'google', 'state' => $state, 'code' => 'code']))->assertRedirect(route('mypage.profile'));
        $this->assertDatabaseHas('social_accounts', ['user_id' => $user->id, 'provider' => 'google']);
        $this->delete(route('social.disconnect', 'google'))->assertRedirect(route('mypage.profile'));
        $this->assertDatabaseMissing('social_accounts', ['user_id' => $user->id, 'provider' => 'google']);
    }

    public function test_cancelled_or_failed_provider_response_never_authenticates_or_exposes_token(): void
    {
        $state = $this->begin();
        $this->get(route('social.callback', ['provider' => 'google', 'state' => $state, 'error' => 'access_denied']))->assertRedirect(route('front.member.login'));
        Http::assertNothingSent();
        Http::fake(['*' => Http::response(['error' => 'secret-provider-error'], 400)]);
        $state = $this->begin();
        $response = $this->get(route('social.callback', ['provider' => 'google', 'state' => $state, 'code' => 'code']));
        $response->assertRedirect(route('front.member.login'));
        $this->assertStringNotContainsString('secret-provider-error', session('error_message'));
        $this->assertGuest();
    }

    public function test_google_unverified_email_cannot_create_membership(): void
    {
        $this->fakeProfile('google', 'social@example.com', false);
        $state = $this->begin();
        $this->get(route('social.callback', ['provider' => 'google', 'state' => $state, 'code' => 'code']))->assertRedirect(route('front.member.login'));
        $this->assertNull(session('social_signup'));
        $this->assertGuest();
    }

    public function test_linking_never_steals_another_members_provider_identity(): void
    {
        $owner = User::factory()->create(['status' => 1]);
        $other = User::factory()->create(['status' => 1]);
        SocialAccount::create(['user_id' => $owner->id, 'provider' => 'google', 'provider_user_id' => 'provider-123']);
        $this->actingAs($other)->withSession(['auth.password_confirmed_at' => now()->timestamp]);
        $this->fakeProfile();
        $state = $this->begin('google', true);
        $this->get(route('social.callback', ['provider' => 'google', 'state' => $state, 'code' => 'code']))
            ->assertRedirect(route('mypage.profile'))->assertSessionHasErrors('social');
        $this->assertDatabaseHas('social_accounts', ['user_id' => $owner->id, 'provider_user_id' => 'provider-123']);
        $this->assertDatabaseMissing('social_accounts', ['user_id' => $other->id]);
    }

    public function test_callback_cannot_link_after_the_signed_in_user_changes(): void
    {
        $user = User::factory()->create(['status' => 1]);
        $other = User::factory()->create(['status' => 1]);
        $this->actingAs($user)->withSession(['auth.password_confirmed_at' => now()->timestamp]);
        $state = $this->begin('google', true);
        $this->actingAs($other)->get(route('social.callback', ['provider' => 'google', 'state' => $state, 'code' => 'code']))
            ->assertRedirect(route('front.member.login'));
        Http::assertNothingSent();
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_shop_social_login_returns_to_the_channel_that_started_it(): void
    {
        $vendor = \App\Models\Vendor::create(['name' => 'Social vendor', 'email' => 'social-vendor@example.com', 'mobile' => '01011112222', 'status' => 1]);
        $shop = \App\Models\ShopChannel::create(['vendor_id' => $vendor->id, 'channel_code' => 'SOCIAL-RETURN', 'channel_name' => 'Social shop', 'status' => 1, 'is_public' => 1, 'copyright' => 'Social', 'keywords' => []]);
        $user = User::factory()->create(['status' => 1]);
        SocialAccount::create(['user_id' => $user->id, 'provider' => 'google', 'provider_user_id' => 'provider-123']);
        $this->fakeProfile();
        $response = $this->withSession(['shop_channel_id' => $shop->id])->get(route('social.start', ['provider' => 'google', 'context' => 'shop']));
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $query);
        $this->get(route('social.callback', ['provider' => 'google', 'state' => $query['state'], 'code' => 'code']))
            ->assertRedirect(route('shop.channel_main'))->assertSessionHas('shop_channel_id', $shop->id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_general_registration_does_not_consume_or_link_a_social_identity(): void
    {
        $this->fakeProfile();
        $state = $this->begin();
        $this->get(route('social.callback',['provider'=>'google','state'=>$state,'code'=>'code']))->assertRedirect(route('social.signup'));
        $this->get('/register/member')->assertOk()->assertDontSee('social@example.com');
        $this->postJson('/register/member',[
            'register_type'=>'general','username'=>'separatemember','email_prefix'=>'separate','email_domain'=>'example.test',
            'password'=>'Password123!','password_confirmation'=>'Password123!',
            'agree_terms'=>1,'agree_privacy'=>1,'agree_third_party'=>1,
        ])->assertOk()->assertJson(['redirect_url'=>route('front.member.register.step1')]);
        $this->assertAuthenticatedAs(User::where('email','separate@example.test')->firstOrFail());
        $this->assertDatabaseCount('social_accounts',0);
        $this->assertNull(session('social_signup'));
    }

    public function test_social_and_general_signup_create_distinct_members_and_both_finish_at_step1(): void
    {
        $this->fakeProfile();
        $state = $this->begin();
        $this->get(route('social.callback',['provider'=>'google','state'=>$state,'code'=>'code']))->assertRedirect(route('social.signup'));
        $this->post(route('social.register'),['name'=>'Social member','terms_service'=>1,'terms_privacy'=>1,'terms_third_party'=>1])
            ->assertRedirect(route('front.member.register.step1'));
        $social = User::where('email','social@example.com')->firstOrFail();
        $this->post('/logout');
        $this->postJson('/register/member',[
            'register_type'=>'general','username'=>'separatemember','email_prefix'=>'separate','email_domain'=>'example.test',
            'password'=>'Password123!','password_confirmation'=>'Password123!',
            'agree_terms'=>1,'agree_privacy'=>1,'agree_third_party'=>1,
        ])->assertOk()->assertJson(['redirect_url'=>route('front.member.register.step1')]);
        $general = User::where('email','separate@example.test')->firstOrFail();
        $this->assertNotSame($social->id,$general->id);
        $this->assertDatabaseHas('social_accounts',['user_id'=>$social->id,'provider'=>'google']);
        $this->assertFalse(SocialAccount::where('user_id',$general->id)->exists());
        $this->assertAuthenticatedAs($general);
    }
}
