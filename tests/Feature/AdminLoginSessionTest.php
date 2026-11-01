<?php

namespace Tests\Feature;

use App\Http\Middleware\VerifyCsrfToken;
use App\Models\Admin;
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

    public function test_login_form_is_not_cached_and_admin_entry_redirects_to_protected_dashboard(): void
    {
        $response = $this->get('/admin/login')->assertOk();
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->get('/admin/')->assertRedirect('/admin/dashboard');
        $this->get('/admin/dashboard')->assertRedirect('/admin/login');
    }
}
