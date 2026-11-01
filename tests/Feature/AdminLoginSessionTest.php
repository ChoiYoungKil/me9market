<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Admin;
use App\Models\Distributor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withMiddleware(VerifyCsrfToken::class);
        $this->app->bind(VerifyCsrfToken::class, fn ($app) => new class($app, $app['encrypter']) extends VerifyCsrfToken
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });
    }

    private function administrator(): Admin
    {
        return Admin::forceCreate([
            'name' => 'Session review admin', 'type' => 'admin', 'vendor_id' => 0,
            'email' => 'session-review@example.test', 'mobile' => '01000000000',
            'password' => Hash::make('ExistingTestPassword'), 'confirm' => 'Yes', 'status' => 1,
        ]);
    }

    public function test_stale_login_token_restarts_the_form_without_authenticating_or_retaining_the_password(): void
    {
        $admin = $this->administrator();
        $this->withSession(['_token' => 'current-session-token'])->post('/admin/login', [
            '_token' => 'old-tab-token', 'email' => $admin->email, 'password' => 'ExistingTestPassword',
        ])->assertRedirect('/admin/login')
            ->assertSessionHas('error_message', '로그인 화면의 유효시간이 지났습니다. 비밀번호를 다시 입력해 주세요.')
            ->assertSessionHas('_old_input.email', $admin->email)
            ->assertSessionMissing('_old_input.password');
        $this->assertGuest('admin');
        $this->get('/admin/login')->assertOk()->assertSee('로그인 화면의 유효시간이 지났습니다.')
            ->assertDontSee('ExistingTestPassword');
    }

    public function test_a_fresh_token_authenticates_and_regenerates_the_session(): void
    {
        $admin = $this->administrator();
        $this->get('/admin/login')->assertOk();
        $session = $this->app['session.store'];
        $previousId = $session->getId();
        $this->post('/admin/login', [
            '_token' => $session->token(), 'email' => $admin->email, 'password' => 'ExistingTestPassword',
        ])->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertNotSame($previousId, $session->getId());
    }

    public function test_expired_json_requests_and_other_admin_actions_remain_rejected(): void
    {
        $this->withSession(['_token' => 'current-session-token']);
        $this->postJson('/admin/login', ['_token' => 'stale-token'])->assertStatus(419);
        $this->post('/admin/update-admin-details', ['_token' => 'stale-token'])->assertStatus(419);
        $this->assertGuest('admin');
    }

    public function test_missing_token_and_malformed_email_do_not_error_or_echo_credentials(): void
    {
        $this->post('/admin/login', ['email' => ['invalid'], 'password' => 'DoNotRetain'])
            ->assertRedirect('/admin/login')->assertSessionMissing('_old_input.password')
            ->assertSessionMissing('_old_input.email');
        $this->assertGuest('admin');
    }

    public function test_stale_member_channel_and_distributor_logins_restart_without_replaying_credentials(): void
    {
        foreach ([
            ['/member/login', 'error_message', ['login_id' => 'review-member']],
            ['/channel/login', 'error_message', ['login_id' => 'review-channel']],
            ['/distributor/login', 'flash_message_error', ['email' => 'review@example.test', 'name' => 'Review partner']],
        ] as [$path, $messageKey, $identity]) {
            $this->withSession(['_token' => 'fresh-token'])->post($path, $identity + [
                '_token' => 'old-tab-token', 'password' => 'NeverRetainThisPassword', 'otp' => 'NeverRetainThisCode',
            ])->assertRedirect($path)->assertSessionHas($messageKey)
                ->assertSessionMissing('_old_input.password')->assertSessionMissing('_old_input.otp');
            foreach ($identity as $field => $value) {
                $this->assertSame($value, session('_old_input.'.$field));
            }
            $this->assertGuest('web');
            $this->assertGuest('admin');
            $this->assertNull(session('distributor_id'));
            $this->get($path)->assertOk()->assertSee('로그인 화면의 유효시간이 지났습니다.')
                ->assertDontSee('NeverRetainThisPassword')->assertDontSee('NeverRetainThisCode');
            $this->postJson($path, ['_token' => 'old-tab-token'])->assertStatus(419);
        }
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('admins', 0);
        $this->assertDatabaseCount('distributors', 0);
    }

    public function test_stale_portal_login_does_not_flash_malformed_or_unbounded_identity_input(): void
    {
        $this->withSession(['_token' => 'fresh-token'])->post('/channel/login', [
            '_token' => 'old-tab-token', 'login_id' => ['invalid'], 'password' => 'DoNotRetain',
        ])->assertRedirect('/channel/login')->assertSessionMissing('_old_input.login_id')
            ->assertSessionMissing('_old_input.password');
        $this->post('/distributor/login', ['_token' => 'old-tab-token', 'name' => str_repeat('x', 1000)])
            ->assertRedirect('/distributor/login')->assertSessionHas('_old_input.name', str_repeat('x', 100));
        $this->assertGuest('admin');
        $this->assertNull(session('distributor_id'));
    }

    public function test_login_form_is_not_cached_and_admin_entry_redirects_to_protected_dashboard(): void
    {
        $response = $this->get('/admin/login')->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->get('/admin/')->assertRedirect('/admin/dashboard');
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
    }

    public function test_stale_shop_forms_restart_with_channel_context_without_replaying_codes_or_passwords(): void
    {
        $this->withSession(['_token' => 'current-token']);
        $this->post(route('shop.login.submit'), ['_token' => 'stale-token', 'channel' => 'AUTH-Shop', 'login_id' => 'review-user', 'password' => 'DoNotRetain'])
            ->assertRedirect(route('shop.login', ['channel' => 'AUTH-Shop']))->assertSessionMissing('_old_input.password');
        $this->post(route('shop.gate.submit'), ['_token' => 'stale-token', 'channel' => 'AUTH-Shop', 'phone' => '01000002120', 'entry_code' => 'DoNotRetain'])
            ->assertRedirect(route('shop.gate', ['channel' => 'AUTH-Shop']))->assertSessionMissing('_old_input.entry_code');
        $this->postJson(route('shop.login.submit'), ['_token' => 'stale-token'])->assertStatus(419);
        $this->postJson(route('shop.gate.submit'), ['_token' => 'stale-token'])->assertStatus(419);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_admin_portal_buttons_share_csrf_token_but_rotate_session_ids(): void
    {
        $admin = $this->administrator();
        $manager = Distributor::create(['name' => 'Portal session review', 'email' => 'portal-session@example.test',
            'password' => Hash::make('ExistingTestPassword'), 'status' => 1]);
        $this->actingAs($admin, 'admin')->get('/admin/order-managers')->assertOk();
        $session = $this->app['session.store'];
        $formToken = $session->token();
        foreach (['pending', 'completed', 'pending'] as $destination) {
            $previousId = $session->getId();
            // Reuse the token from the original list, as a back-cached form or
            // an already open admin tab would, with the real CSRF middleware enabled.
            $this->post(route('admin.order_managers.portal', $manager->id), [
                '_token' => $formToken, 'destination' => $destination,
            ])->assertRedirect(route('distributor.orders.'.$destination))
                ->assertSessionHas('distributor_id', $manager->id);
            $this->assertNotSame($previousId, $session->getId());
            $this->assertSame($formToken, $session->token());
            $this->assertAuthenticatedAs($admin, 'admin');
            $this->get(route('distributor.orders.'.$destination))->assertOk()->assertSee($manager->name);
        }
    }

    public function test_stale_member_channel_form_returns_to_mypage_without_switching_admin_or_replaying_input(): void
    {
        $user = \App\Models\User::factory()->create(['type' => 'general', 'status' => 1]);
        $this->actingAs($user, 'web')->withSession(['_token' => 'fresh-member-token'])
            ->post(route('mypage.channel_admin'), ['_token' => 'old-tab-token', 'sub_account_id' => 99999])
            ->assertRedirect(route('mypage.dashboard'))->assertSessionHas('error_message')
            ->assertSessionMissing('_old_input.sub_account_id');
        $this->assertGuest('admin');
        $this->assertDatabaseCount('admins', 0);
        $this->postJson(route('mypage.channel_admin'), ['_token' => 'old-tab-token'])->assertStatus(419);
        $this->assertGuest('admin');
    }

    public function test_admin_portal_rejects_missing_or_forged_csrf_tokens(): void
    {
        $admin = $this->administrator();
        $manager = Distributor::create(['name' => 'Protected portal review', 'email' => 'protected-portal@example.test',
            'password' => Hash::make('ExistingTestPassword'), 'status' => 1]);
        $this->actingAs($admin, 'admin')->withSession(['_token' => 'valid-portal-token']);
        foreach ([[], ['_token' => 'forged-token']] as $payload) {
            $this->post(route('admin.order_managers.portal', $manager->id), $payload)
                ->assertStatus(419)->assertSessionMissing('distributor_id');
        }
        $this->assertAuthenticatedAs($admin, 'admin');
    }
}
