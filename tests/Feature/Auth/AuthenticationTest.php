<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get('/member/login');

        $response->assertStatus(200);
        $response->assertSee(route('front.nonmember.order_check'), false);
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create();

        $response = $this->post('/member/login', [
            'login_id' => $user->username,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post('/member/login', [
            'login_id' => $user->username,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_email_login_and_intended_member_page_are_supported(): void
    {
        $user = User::factory()->create();
        $this->withSession(['url.intended'=>url('/mypage/order/list')])->post('/member/login', [
            'login_id'=>$user->email, 'password'=>'password',
        ])->assertRedirect('/mypage/order/list');
        $this->assertAuthenticatedAs($user);
    }

    public function test_failed_login_retains_identifier_but_never_password(): void
    {
        $user = User::factory()->create();
        $this->from('/member/login')->post('/member/login', [
            'login_id'=>$user->username, 'password'=>'wrong-password', 'remember_id'=>'1',
        ])->assertRedirect('/member/login')->assertSessionHasInput('login_id',$user->username)
            ->assertSessionMissing('_old_input.password');
        $this->get('/member/login')->assertOk()->assertSee($user->username)->assertDontSee('wrong-password');
        $this->assertGuest();
    }

    public function test_save_id_sets_an_encrypted_identifier_cookie_without_remember_login(): void
    {
        $user = User::factory()->create();
        $response = $this->post('/member/login', [
            'login_id'=>$user->username, 'password'=>'password', 'remember_id'=>'1',
        ])->assertRedirect('/')->assertCookie('me9_saved_login_id');
        $cookies = collect($response->headers->getCookies());
        $saved = $cookies->first(fn($cookie)=>$cookie->getName()==='me9_saved_login_id');
        $this->assertNotSame($user->username,$saved->getValue());
        $this->assertTrue($saved->isHttpOnly());
        $this->assertFalse($cookies->contains(fn($cookie)=>str_starts_with($cookie->getName(),'remember_web_')));
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
        $this->withCookie('me9_saved_login_id',$user->username)->get('/member/login')
            ->assertOk()->assertViewHas('loginId',$user->username)->assertViewHas('rememberId',true);
    }

    public function test_unchecking_save_id_removes_the_cookie(): void
    {
        $user = User::factory()->create();
        $this->withCookie('me9_saved_login_id',$user->username)->post('/member/login', [
            'login_id'=>$user->username, 'password'=>'password',
        ])->assertCookieExpired('me9_saved_login_id');
    }

    public function test_malformed_login_values_and_disabled_accounts_do_not_authenticate(): void
    {
        $this->from('/member/login')->post('/member/login', ['login_id'=>['bad'], 'password'=>['bad']])
            ->assertRedirect('/member/login')->assertSessionHasErrors(['login_id','password'])->assertSessionMissing('_old_input.password');
        $this->get('/member/login')->assertOk();
        $disabled = User::factory()->create(['status'=>0]);
        $this->post('/member/login',['login_id'=>$disabled->username,'password'=>'password'])->assertSessionHas('error_message');
        $this->assertGuest();
    }
}
