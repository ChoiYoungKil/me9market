<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ShopChannel;
use App\Models\ShopChannelPrivateAccess;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ShopChannelRuntime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShopAuthenticationFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Exercise registration in the isolated test DB; application approval remains pending.
        config(['shop_channel.signup_terms_approved' => true]);
        config(['app.url' => 'http://localhost', 'social_login.naver' => ['enabled' => true, 'client_id' => 'test-client', 'client_secret' => 'test-secret', 'redirect_uri' => 'http://localhost/auth/social/naver/callback']]);
        Http::preventStrayRequests();
    }

    private function shop(bool $private = false, string $suffix = 'one'): ShopChannel
    {
        $vendor = Vendor::forceCreate(['name' => 'Auth vendor '.$suffix, 'email' => $suffix.'@vendor.test', 'mobile' => '01000000000', 'status' => 1]);

        return ShopChannel::create(['vendor_id' => $vendor->id, 'channel_code' => 'AUTH-'.$suffix, 'channel_name' => 'Auth Shop '.$suffix, 'status' => 1, 'is_public' => $private ? 0 : 1, 'is_member_only' => 0, 'keywords' => [], 'copyright' => 'Auth review', 'settlement_type' => 1, 'settlement_rate' => 0]);
    }

    private function access(ShopChannel $shop): ShopChannelPrivateAccess
    {
        return ShopChannelPrivateAccess::create(['shop_channel_id' => $shop->id, 'phone' => '010-0000-2120', 'phone_normalized' => '01000002120', 'entry_code' => 'Review-212-Code']);
    }

    private function enter(ShopChannel $shop): void
    {
        $this->get(route('shop.enter', $shop->channel_code))->assertRedirect();
    }

    private function socialStart(ShopChannel $shop): string
    {
        $this->enter($shop);
        $r = $this->get(route('social.start', ['provider' => 'naver', 'context' => 'shop']));
        parse_str(parse_url($r->headers->get('Location'), PHP_URL_QUERY), $q);
        $this->assertSame('test-client', $q['client_id'] ?? null);

        return $q['state'];
    }

    private function profile(): void
    {
        Http::fake([
            '*nid.naver.com/oauth2.0/token' => Http::response(['access_token' => 'test-only-token']),
            '*openapi.naver.com/v1/nid/me' => Http::response(['resultcode' => '00', 'response' => ['id' => 'qa215-identity', 'email' => 'qa215@example.test', 'name' => 'Provider name']]),
        ]);
    }

    private function pending(ShopChannel $shop): void
    {
        $this->profile();
        $state = $this->socialStart($shop);
        $this->get(route('social.callback', ['provider' => 'naver', 'state' => $state, 'code' => 'test-code']))->assertRedirect(route('social.signup'));
    }

    public function test_private_phone_and_per_person_code_enter_and_code_is_never_flashed(): void
    {
        $shop = $this->shop(true);
        $access = $this->access($shop);
        $gate = route('shop.gate', ['channel' => $shop->channel_code]);
        $this->get($gate)->assertOk()->assertSee('핸드폰번호')->assertSee('입장 확인')->assertDontSee('SMS 인증번호 받기')->assertDontSee($access->entry_code);
        $this->from($gate)->post(route('shop.gate.submit'), ['channel' => $shop->channel_code, 'phone' => $access->phone, 'entry_code' => 'wrong'])->assertRedirect($gate)->assertSessionHasErrors('entry_code')->assertSessionMissing('_old_input.entry_code');
        $this->get(route('shop.channel_main'))->assertRedirect($gate);
        $this->post(route('shop.gate.submit'), ['channel' => $shop->channel_code, 'phone' => $access->phone, 'entry_code' => $access->entry_code])->assertRedirect(route('shop.channel_main'));
        $this->get(route('shop.channel_main'))->assertOk()->assertSee($shop->channel_name);
        $this->assertSame(1, $access->fresh()->access_count);
        $this->assertNotNull($access->fresh()->first_accessed_at);
        $this->assertDatabaseCount('shop_channel_access_otps', 0);
    }

    public function test_wrong_phone_and_wrong_code_have_the_same_error_and_a_failed_retry_revokes_access(): void
    {
        $shop = $this->shop(true);
        $a = $this->access($shop);
        $payload = ['channel' => $shop->channel_code, 'phone' => $a->phone, 'entry_code' => 'wrong'];
        $this->postJson(route('shop.gate.submit'), $payload)->assertUnprocessable();
        $r1 = $this->postJson(route('shop.gate.submit'), $payload);
        $r2 = $this->postJson(route('shop.gate.submit'), array_replace($payload, ['phone' => '01099998888']));
        $this->assertSame($r1->json('errors.entry_code'), $r2->json('errors.entry_code'));
        $this->post(route('shop.gate.submit'), array_replace($payload, ['entry_code' => $a->entry_code]))->assertRedirect(route('shop.channel_main'));
        $this->postJson(route('shop.gate.submit'), $payload)->assertUnprocessable();
        $this->assertFalse(app(ShopChannelRuntime::class)->hasActiveChannelAccess());
    }

    public function test_private_entry_limits_attempts_and_revokes_a_changed_or_removed_access_list_entry(): void
    {
        $shop = $this->shop(true);
        $a = $this->access($shop);
        $p = ['channel' => $shop->channel_code, 'phone' => $a->phone, 'entry_code' => 'wrong'];
        foreach (range(1, 5) as $i) {
            $this->postJson(route('shop.gate.submit'), $p)->assertUnprocessable();
        }
        $this->postJson(route('shop.gate.submit'), array_replace($p, ['entry_code' => $a->entry_code]))->assertUnprocessable()->assertJsonPath('errors.entry_code.0', '입장 확인을 여러 번 시도했습니다. 잠시 후 다시 시도해 주세요.');
        $this->travel(61)->seconds();
        $this->post(route('shop.gate.submit'), array_replace($p, ['entry_code' => $a->entry_code]))->assertRedirect(route('shop.channel_main'));
        $a->update(['entry_code' => 'changed-code']);
        $this->assertFalse(app(ShopChannelRuntime::class)->hasActiveChannelAccess());
        $this->post(route('shop.gate.submit'), array_replace($p, ['entry_code' => 'changed-code']))->assertRedirect(route('shop.channel_main'));
        $a->delete();
        $this->assertFalse(app(ShopChannelRuntime::class)->hasActiveChannelAccess());
    }

    public function test_closed_expired_and_other_channel_codes_cannot_grant_private_access(): void
    {
        $shop = $this->shop(true);
        $a = $this->access($shop);
        $other = $this->shop(true, 'two');
        $p = ['channel' => $other->channel_code, 'phone' => $a->phone, 'entry_code' => $a->entry_code];
        $this->postJson(route('shop.gate.submit'), $p)->assertUnprocessable();
        $shop->update(['use_period_type' => 1, 'end_at' => now()->subMinute()]);
        $this->postJson(route('shop.gate.submit'), array_replace($p, ['channel' => $shop->channel_code]))->assertUnprocessable();
        $shop->update(['use_period_type' => 0, 'status' => 0]);
        $this->postJson(route('shop.gate.submit'), array_replace($p, ['channel' => $shop->channel_code]))->assertUnprocessable();
        $this->assertFalse(app(ShopChannelRuntime::class)->hasActiveChannelAccess());
    }

    public function test_member_only_shop_requires_login_and_id_save_does_not_issue_a_remember_cookie(): void
    {
        $shop = $this->shop();
        $shop->update(['is_member_only' => 1]);
        $this->enter($shop);
        $this->get(route('shop.channel_main'))->assertRedirect(route('shop.login', ['channel' => $shop->channel_code]));
        $this->get(route('shop.login'))->assertOk()->assertSee('아이디 저장')->assertDontSee('로그인 유지');
        $user = User::factory()->create(['username' => 'shop213', 'status' => 1, 'password' => Hash::make('ExistingReviewPassword')]);
        $r = $this->post(route('shop.login.submit'), ['channel' => $shop->channel_code, 'login_id' => 'shop213', 'password' => 'ExistingReviewPassword', 'save_id' => 1, 'remember' => 1]);
        $r->assertRedirect(route('shop.channel_main'));
        $this->assertAuthenticatedAs($user);
        foreach ($r->headers->getCookies() as $cookie) {
            $this->assertFalse(str_starts_with($cookie->getName(), 'remember_web_'));
        }
        $this->post(route('shop.logout'))->assertRedirect(route('shop.login', ['channel' => $shop->channel_code]))->assertSessionMissing('shop_channel_id');
        $this->assertGuest();
    }

    public function test_login_rejects_expired_temporary_password_and_cannot_skip_private_admission(): void
    {
        $shop = $this->shop(true);
        $this->access($shop);
        $user = User::factory()->create(['username' => 'expired-user', 'status' => 1, 'password' => Hash::make('ExistingReviewPassword'), 'temporary_password_expires_at' => now()->subMinute()]);
        $p = ['channel' => $shop->channel_code, 'login_id' => $user->username, 'password' => 'ExistingReviewPassword'];
        $this->postJson(route('shop.login.submit'), $p)->assertUnprocessable();
        $this->assertGuest();
        $user->forceFill(['temporary_password_expires_at' => null])->save();
        $this->post(route('shop.login.submit'), $p)->assertRedirect(route('shop.gate', ['channel' => $shop->channel_code]));
        $this->assertFalse(app(ShopChannelRuntime::class)->hasActiveChannelAccess());
    }

    public function test_shop_social_cancellation_and_cross_tab_channel_changes_do_not_authenticate(): void
    {
        $shop = $this->shop();
        $state = $this->socialStart($shop);
        $this->get(route('social.callback', ['provider' => 'naver', 'state' => $state, 'error' => 'access_denied']))->assertRedirect(route('shop.login'))->assertSessionHas('error_message');
        $state = $this->socialStart($shop);
        $other = $this->shop(false, 'other');
        $this->enter($other);
        $this->get(route('social.callback', ['provider' => 'naver', 'state' => $state, 'code' => 'test-code']))->assertRedirect(route('shop.login'));
        $this->assertGuest();
        Http::assertNothingSent();
    }

    public function test_existing_social_member_returns_to_the_same_shop_without_new_account(): void
    {
        $shop = $this->shop();
        $user = User::factory()->create(['status' => 1]);
        SocialAccount::create(['user_id' => $user->id, 'provider' => 'naver', 'provider_user_id' => 'qa215-identity']);
        $this->profile();
        $state = $this->socialStart($shop);
        $this->get(route('social.callback', ['provider' => 'naver', 'state' => $state, 'code' => 'test-code']))->assertRedirect(route('shop.channel_main'));
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('social_accounts', 1);
    }

    public function test_shop_social_signup_renders_ppt_fields_and_commits_only_provider_identity_and_consent(): void
    {
        $shop = $this->shop();
        $this->pending($shop);
        $response = $this->get(route('social.signup'))->assertOk()->assertViewIs('shop.social_signup')->assertSee('성별')->assertSee('가입 완료 시 자동 발급')->assertSee('qa215@example.test')->assertDontSee('new-password');
        $response->assertDontSee('회원 개인정보 처리 안내 및 동의')->assertDontSee('수정안')->assertDontSee('.docx')->assertDontSee('me9마켓 통합 서비스 이용약관')->assertDontSee('me9마켓 개인정보처리방침');
        $documents = $response->viewData('documents');
        foreach ($documents as $item) {
            $this->assertCount(1, $item['documents']);
        }
        $this->assertSame('shop', $documents['terms_service']['documents'][0]['slug']);
        $privacy = array_column($documents['terms_privacy']['documents'][0]['blocks'], 'text');
        $marketing = array_column($documents['marketing_opt_in']['documents'][0]['blocks'], 'text');
        $notification = array_column($documents['notification_opt_in']['documents'][0]['blocks'], 'text');
        $this->assertContains('개인정보 수집 및 이용 동의', $privacy);
        $this->assertNotContains('광고성 정보 수신 동의', $privacy);
        $this->assertContains('□ [선택] 마케팅 목적 개인정보 이용에 동의합니다.', $marketing);
        $this->assertNotContains('개인정보 수집 및 이용 동의', $marketing);
        $this->assertContains('광고성 정보 수신 동의', $notification);
        $this->assertNotContains('개인정보 수집 및 이용 동의', $notification);
        $this->post(route('social.register'), ['name' => ' '])->assertSessionHasErrors('name');
        $this->post(route('social.register'), ['name' => 'Shop member'])->assertSessionHasErrors(['terms_service', 'terms_privacy', 'terms_third_party']);
        $this->assertDatabaseCount('users', 0);
        $payload = ['name' => 'New Shop member', 'gender' => 'Female', 'email' => 'attacker@example.test', 'shop_channel_id' => 99999, 'terms_service' => 1, 'terms_privacy' => 1, 'terms_third_party' => 1];
        $this->post(route('social.register'), $payload)->assertRedirect(route('shop.channel_main'));
        $user = User::where('email', 'qa215@example.test')->sole();
        $this->assertSame('Female', $user->gender);
        $this->assertNotEmpty($user->member_number);
        $this->assertAuthenticatedAs($user);
        $this->assertSame($shop->id, $user->terms_snapshot['shop_channel_id']);
        $this->assertSame('03_개별쇼핑몰이용약관.docx', $user->terms_snapshot['displayed_documents']['terms_service']['documents'][0]['source']);
        $this->assertSame(hash_file('sha256', resource_path('policies/shop.json')), $user->terms_snapshot['displayed_documents']['terms_service']['documents'][0]['sha256']);
        $this->assertSame(hash('sha256', json_encode($documents['terms_privacy']['documents'][0]['blocks'], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)), $user->terms_snapshot['displayed_documents']['terms_privacy']['documents'][0]['display_sha256']);
        $this->assertFalse($user->terms_snapshot['documents_published']);
        $this->assertFalse($user->marketing_opt_in);
        $this->assertFalse($user->notification_opt_in);
        $this->assertNull(session('social_signup'));
        $this->assertDatabaseHas('visited_channels', ['user_id' => $user->id, 'shop_channel_id' => $shop->id]);
        $this->assertDatabaseMissing('users', ['email' => 'attacker@example.test']);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('social_accounts', 1);
        $this->post(route('social.register'), $payload)->assertRedirect(route('shop.channel_main'));
        $this->get(route('social.signup'))->assertRedirect(route('shop.channel_main'));
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('social_accounts', 1);
    }

    public function test_shop_signup_missing_consents_invalid_gender_and_duplicate_identity_do_not_partially_create_members(): void
    {
        $shop = $this->shop();
        $this->pending($shop);
        $p = ['name' => 'Signup member', 'gender' => 'Other', 'terms_service' => 1, 'terms_privacy' => 1, 'terms_third_party' => 1];
        $this->post(route('social.register'), $p)->assertSessionHasErrors('gender');
        $this->assertDatabaseCount('users', 0);
        $this->post(route('social.register'), array_replace($p, ['gender' => 'Male', 'terms_privacy' => 0]))->assertSessionHasErrors('terms_privacy');
        User::factory()->create(['email' => 'qa215@example.test']);
        $this->post(route('social.register'), array_replace($p, ['gender' => 'Male']))->assertSessionHasErrors('social');
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('social_accounts', 0);
        $this->assertGuest();
    }

    public function test_expired_or_channel_switched_signup_is_rejected_before_user_creation(): void
    {
        $shop = $this->shop();
        $this->pending($shop);
        $this->travel(11)->minutes();
        $this->get(route('social.signup'))->assertRedirect(route('shop.login'))->assertSessionMissing('social_signup');
        $this->travelBack();
        $this->pending($shop);
        $other = $this->shop(false, 'other');
        $this->enter($other);
        $this->post(route('social.register'), ['name' => 'Do not create'])->assertRedirect(route('shop.login'));
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_signup_preview_is_local_admin_only_and_never_sets_oauth_or_creates_users(): void
    {
        $shop = $this->shop();
        $this->app->instance('env', 'local');
        $this->get(route('local.preview.shop_signup', ['channel' => $shop->id]))->assertRedirect(route('admin.login'));
        $admin = Admin::forceCreate(['name' => 'Preview admin', 'type' => 'admin', 'vendor_id' => 0, 'email' => 'preview@admin.test', 'mobile' => '01000000000', 'password' => Hash::make('ExistingPassword'), 'status' => 1, 'confirm' => 'Yes']);
        $this->actingAs($admin, 'admin')->get(route('local.preview.shop_signup', ['channel' => $shop->id]))->assertOk()->assertSee('실제 소셜 인증이나 회원가입을 수행하지 않습니다.')->assertSee('미리보기에서는 제출하지 않음');
        $this->assertNull(session('social_signup'));
        $this->assertNull(session('social_oauth'));
        $this->assertDatabaseCount('users', 0);
        $this->app->instance('env', 'production');
        $this->get(route('local.preview.shop_signup', ['channel' => $shop->id]))->assertNotFound();
    }

    public function test_shop_signup_does_not_treat_uploaded_draft_documents_as_published_production_consents(): void
    {
        $shop = $this->shop();
        $this->app->instance('env', 'production');
        config(['shop_channel.terms_url' => 'https://example.test/terms', 'shop_channel.privacy_url' => 'https://example.test/privacy', 'shop_channel.third_party_url' => 'https://example.test/provision', 'shop_channel.terms_version' => 'external-v1']);
        $this->pending($shop);
        $this->get(route('social.signup'))->assertOk()->assertSee('회원가입 약관을 준비 중입니다.');
        $this->post(route('social.register'), ['name' => 'Do not create', 'terms_service' => 1, 'terms_privacy' => 1, 'terms_third_party' => 1])->assertSessionHasErrors('terms_service');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_pending_terms_approval_blocks_signup_even_in_local_environment(): void
    {
        $shop = $this->shop();
        $this->pending($shop);
        $this->app->instance('env', 'local');
        config(['shop_channel.signup_terms_approved' => false]);
        $this->get(route('social.signup'))->assertOk()->assertSee('약관 승인 전까지 가입 완료를 보류합니다.');
        $this->post(route('social.register'), ['name' => 'Do not create', 'terms_service' => 1, 'terms_privacy' => 1, 'terms_third_party' => 1])->assertSessionHasErrors('terms_service');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_an_invalid_requested_shop_cannot_reuse_another_channels_login_context(): void
    {
        $shop = $this->shop();
        $this->enter($shop);
        User::factory()->create(['username' => 'review-member', 'password' => Hash::make('ExistingReviewPassword'), 'status' => 1]);
        $this->get(route('shop.login', ['channel' => 'missing-channel']))->assertRedirect(route('shop.gate', ['channel' => 'missing-channel']));
        $this->postJson(route('shop.login.submit'), ['channel' => 'missing-channel', 'login_id' => 'review-member', 'password' => 'ExistingReviewPassword'])->assertUnprocessable()->assertJsonValidationErrors('channel');
        $this->assertGuest();
    }
}
