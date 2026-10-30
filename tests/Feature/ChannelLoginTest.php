<?php

namespace Tests\Feature;

use App\Models\{Admin, SocialAccount, User, Vendor};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Auth, Hash, Http};
use Tests\TestCase;

class ChannelLoginTest extends TestCase
{
    use RefreshDatabase;

    private function member(): array
    {
        $vendor = Vendor::forceCreate(['name'=>'채널 로그인 검수', 'email'=>'vendor67@example.test',
            'mobile'=>'01000000000', 'status'=>1, 'company_terms_accepted_at'=>now()]);
        $member = User::factory()->create(['username'=>'member67', 'email'=>'member67@example.test',
            'password'=>Hash::make('Member67Only'), 'type'=>'vendor', 'vendor_id'=>$vendor->id]);
        $admin = Admin::forceCreate(['vendor_id'=>$vendor->id, 'name'=>'회원 연결 관리자',
            'email'=>$member->email, 'password'=>Hash::make('DifferentAdminPassword'),
            'mobile'=>'01000000000', 'type'=>'vendor', 'status'=>1, 'confirm'=>'Yes']);
        return [$member,$admin];
    }

    public function test_channel_form_has_member_identity_and_real_social_entries_with_kakao_pending(): void
    {
        config(['app.url'=>'http://localhost', 'social_login.google'=>['enabled'=>true,'client_id'=>'client67',
            'client_secret'=>'secret67','redirect_uri'=>'http://localhost/auth/social/google/callback']]);
        $this->get('http://localhost/channel/login')->assertOk()->assertSee('아이디 또는 이메일')->assertSee('아이디 저장')
            ->assertSee('카카오 계정으로 로그인')->assertSee('연동 대기')
            ->assertSee('http://localhost/auth/social/google/redirect?context=channel', false)
            ->assertSee('name="remember_id"',false)->assertSee('autocomplete="username"',false);
    }

    public function test_member_username_and_email_login_use_the_members_password_and_own_channel(): void
    {
        [$member,$admin] = $this->member();
        foreach ([$member->username,$member->email] as $identity) {
            $this->post('/channel/login',['login_id'=>$identity,'password'=>'Member67Only'])
                ->assertRedirect(route('channel.index'));
            $this->assertAuthenticatedAs($member,'web');
            $this->assertAuthenticatedAs($admin,'admin');
            Auth::guard('admin')->logout(); Auth::guard('web')->logout();
        }
        $this->assertSame(1,Admin::count());
        $this->assertTrue(Hash::check('DifferentAdminPassword',$admin->fresh()->password));
    }

    public function test_remember_id_only_saves_the_identity_and_unchecked_login_removes_it(): void
    {
        [$member] = $this->member();
        $this->post('/channel/login',['login_id'=>$member->username,'password'=>'Member67Only','remember_id'=>'1'])
            ->assertRedirect(route('channel.index'))->assertCookie('me9_channel_login_id');
        Auth::guard('admin')->logout(); Auth::guard('web')->logout();
        $this->withCookie('me9_channel_login_id','member67')->get('/channel/login')
            ->assertOk()->assertSee('value="member67"',false)->assertDontSee('Member67Only');
        $this->post('/channel/login',['login_id'=>$member->username,'password'=>'Member67Only'])
            ->assertCookieExpired('me9_channel_login_id');
    }

    public function test_wrong_password_and_member_without_channel_rights_never_authenticate_an_admin(): void
    {
        [$member] = $this->member();
        $this->from('/channel/login')->post('/channel/login',['login_id'=>$member->username,'password'=>'wrong'])
            ->assertRedirect('/channel/login')->assertSessionHas('error_message');
        $this->assertGuest('admin');
        $ordinary = User::factory()->create(['password'=>Hash::make('Ordinary67')]);
        $this->from('/channel/login')->post('/channel/login',['login_id'=>$ordinary->username,'password'=>'Ordinary67'])
            ->assertRedirect('/channel/login')->assertSessionHasErrors('login_id');
        $this->assertGuest('admin');
        $this->assertSame('general',$ordinary->fresh()->type);
        $this->assertNull($ordinary->fresh()->vendor_id);
    }

    public function test_inactive_member_and_expired_temporary_password_cannot_enter_channel(): void
    {
        [$member] = $this->member();
        foreach ([['status'=>0],['status'=>1,'temporary_password_expires_at'=>now()->subMinute()]] as $state) {
            $member->forceFill($state)->save();
            $this->from('/channel/login')->post('/channel/login',['login_id'=>$member->username,'password'=>'Member67Only'])
                ->assertSessionHasErrors('login_id');
            $this->assertGuest('admin');
        }
    }

    public function test_invalid_identity_type_is_a_validation_error(): void
    {
        $this->from('/channel/login')->post('/channel/login',['login_id'=>['bad'],'password'=>'test'])
            ->assertSessionHasErrors('login_id');
        $this->get('/channel/login')->assertOk()->assertViewHas('loginId','');
    }

    private function beginSocial(): string
    {
        config(['app.url'=>'http://localhost','social_login.google'=>['enabled'=>true,'client_id'=>'client67',
            'client_secret'=>'secret67','redirect_uri'=>'http://localhost/auth/social/google/callback']]);
        $response = $this->get('/auth/social/google/redirect?context=channel')->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'),PHP_URL_QUERY),$query);
        $this->assertSame('channel',session('social_oauth.google.context'));
        return $query['state'];
    }

    public function test_social_login_preserves_channel_destination_and_uses_the_linked_members_rights(): void
    {
        [$member,$admin] = $this->member();
        SocialAccount::create(['user_id'=>$member->id,'provider'=>'google','provider_user_id'=>'channel-member67']);
        Http::preventStrayRequests();
        Http::fake([
            '*oauth2.googleapis.com/token'=>Http::response(['access_token'=>'test-token67']),
            '*openidconnect.googleapis.com/v1/userinfo'=>Http::response(['sub'=>'channel-member67',
                'email'=>$member->email,'email_verified'=>true,'name'=>$member->name]),
        ]);
        $state = $this->beginSocial();
        $this->get('/auth/social/google/callback?'.http_build_query(['state'=>$state,'code'=>'code67']))
            ->assertRedirect(route('channel.index'));
        $this->assertAuthenticatedAs($member,'web'); $this->assertAuthenticatedAs($admin,'admin');
    }

    public function test_social_cancel_returns_to_channel_login_without_authentication(): void
    {
        $state = $this->beginSocial();
        $this->get('/auth/social/google/callback?'.http_build_query(['state'=>$state,'error'=>'access_denied']))
            ->assertRedirect(route('channel.login'))->assertSessionHas('error_message','소셜 로그인이 취소되었습니다.');
        $this->assertGuest('web'); $this->assertGuest('admin');
    }
}
