<?php
namespace Tests\Feature;

require_once __DIR__.'/../../../tests/Feature/SocialLoginTest.php';

class SocialExportTest extends SocialLoginTest
{
    public function test_export_social_pages(): void
    {
        $dir = '/tmp/me9-social-pages';
        if (!is_dir($dir)) { mkdir($dir, 0700, true); }
        $pages = [];
        foreach (['/member/login', '/shop/login'] as $path) {
            // Use the application's route names so snapshot URLs follow the real routing.
            $url = $path === '/member/login' ? route('front.member.login', [], false) : route('shop.login', [], false);
            $response = $this->get($url);
            $response->assertOk();
            $file = count($pages).'.html';
            file_put_contents($dir.'/'.$file, $response->getContent());
            $pages[] = ['path' => $url, 'file' => $file];
        }
        $response = $this->withSession(['social_signup' => ['provider' => 'google', 'provider_user_id' => 'snapshot-id',
            'email' => 'snapshot@example.com', 'name' => '화면 검증 회원', 'email_verified' => true, 'created_at' => now()->timestamp, 'shop_channel_id' => null]])->get(route('social.signup'));
        $response->assertOk();
        file_put_contents($dir.'/signup.html', $response->getContent());
        $pages[] = ['path' => route('social.signup', [], false), 'file' => 'signup.html'];
        $user = \App\Models\User::factory()->create(['status' => 1]);
        $response = $this->actingAs($user)->get(route('mypage.profile'));
        $response->assertOk();
        file_put_contents($dir.'/profile.html', $response->getContent());
        $pages[] = ['path' => route('mypage.profile', [], false), 'file' => 'profile.html'];
        file_put_contents($dir.'/index.json', json_encode($pages));
    }
}
