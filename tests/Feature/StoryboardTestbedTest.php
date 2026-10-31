<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Notice;
use App\Models\Order;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Vendor;
use App\Services\StoryboardReview;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StoryboardTestbedTest extends TestCase
{
    use RefreshDatabase;

    public function createApplication()
    {
        $app = require __DIR__.'/../../bootstrap/app.php';
        $app->booting(fn () => $app['config']->set('storyboard.enabled', true));
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    private function admin(string $type = 'admin'): Admin
    {
        return Admin::forceCreate(['type' => $type, 'vendor_id' => 0, 'name' => '검수 관리자', 'email' => $type.'@example.test',
            'password' => Hash::make('AdminTestOnly'), 'mobile' => '01000000000', 'status' => 1, 'confirm' => 'Yes']);
    }

    public function test_only_full_administrators_can_view_the_testbed(): void
    {
        $this->get('/admin/storyboard-test')->assertRedirect('/admin/login');
        $this->actingAs($this->admin('vendor'), 'admin')->get('/admin/storyboard-test')->assertForbidden();
        foreach (['admin', 'superadmin'] as $type) {
            $this->actingAs($this->admin($type), 'admin')->get('/admin/storyboard-test')->assertOk();
        }
    }

    public function test_testbed_lists_each_ppt_page_and_preserves_real_review_exceptions_without_mutation(): void
    {
        config(['storyboard.front_url' => 'http://127.0.0.1:8000', 'storyboard.admin_url' => 'http://127.0.0.1:8000',
            'storyboard.passwords.member' => 'MemberTestOnly']);
        $member = User::factory()->create(['username' => 'review-member', 'password' => Hash::make('MemberTestOnly')]);
        $originalPassword = $member->password;
        $notice = Notice::create(['title' => '공개 검수 공지', 'content' => '본문', 'status' => 1]);
        $draft = Notice::create(['title' => '비공개 공지', 'content' => '본문', 'status' => 0]);
        $order = Order::forceCreate(['user_id' => $member->id, 'name' => '검수', 'address' => '검수 주소',
            'city' => '서울', 'state' => '서울', 'country' => 'Korea', 'pincode' => '04524', 'mobile' => '01000000000',
            'email' => $member->email, 'shipping_charges' => 0, 'order_status' => 'New',
            'payment_method' => 'Mock Card', 'payment_gateway' => 'Me9 Mock Payment', 'grand_total' => 1000]);
        $response = $this->actingAs($this->admin(), 'admin')->get('/admin/storyboard-test')->assertOk()
            ->assertSee('review-member')->assertSee('실제 변경 보류')->assertSee('추가 확인 있음')
            ->assertSee('다음 진행: PPT 209번 Shop 채널')->assertSee('http://127.0.0.1:8000/notice/view/'.$notice->id)
            ->assertSee('http://127.0.0.1:8000/mypage/order/view?id='.$order->id)
            ->assertDontSee('http://127.0.0.1:8000/notice/view/'.$draft->id)
            ->assertDontSee('member30review1028')->assertDontSee('034-061')->assertDontSee($originalPassword);
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
        $this->assertSame('no-referrer', $response->headers->get('Referrer-Policy'));
        $pages = $response->viewData('pages');
        $this->assertSame(range(1, 208), array_column($pages, 'number'));
        $this->assertSame('deferred', $pages[38]['status']);
        $this->assertSame('partial', $pages[59]['status']);
        foreach ([81, 188, 190, 191] as $number) {
            $this->assertSame('excluded', $pages[$number - 1]['status']);
        }
        $this->assertSame('deferred', $pages[72]['status']);
        $this->assertSame('partial', $pages[66]['status']);
        $this->assertTrue($pages[172]['admin']);
        $this->assertSame('/channel/settings/refund', $pages[207]['path']);
        $response->assertSee('PPT 208번')->assertSee('검수 대상 제외');
        $response->assertSee('시연 과정·결과 보기')->assertSee('실제 시연 순서와 확인 결과')
            ->assertSee('자동 테스트 기록')->assertSee('미시연·보류·추가 확인')
            ->assertSee('검색된 기록 모두 펼치기')->assertSee('기록 모두 접기');
        $this->assertSame(208, substr_count($response->getContent(), 'data-review-history='));
        $this->assertSame('2026-10-27', $pages[14]['history']['date']);
        $this->assertSame('2026-10-31', $pages[205]['history']['date']);
        $this->assertStringContainsString('명칭 누락', $pages[205]['history']['cases'][0]['action']);
        $this->assertStringContainsString('필수 입력 차단', $pages[205]['history']['cases'][0]['outcome']);
        $this->assertSame($member->id, $response->viewData('accounts')->firstWhere('key', 'member')['id']);
        $this->assertSame('MemberTestOnly', $response->viewData('accounts')->firstWhere('key', 'member')['password']);
        $response->assertSee('비밀번호')->assertSee('MemberTestOnly');
        $this->assertSame($originalPassword, $member->fresh()->password);
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('order_claims', 0);
    }

    public function test_missing_review_data_uses_entry_links_and_never_creates_fixtures(): void
    {
        $pages = app(StoryboardReview::class)->pages(null);
        $this->assertSame('/notice', $pages[15]['path']);
        $this->assertSame('/mypage/order/list', $pages[63]['path']);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_history_preserves_manual_limits_and_does_not_invent_demonstrations_for_excluded_slides(): void
    {
        $pages = collect(app(StoryboardReview::class)->pages(null))->keyBy('number');
        foreach ($pages as $page) {
            $history = $page['history'];
            $this->assertNotEmpty($history['sources']);
            $this->assertNotEmpty($history['limitations']);
            if (in_array($page['status'], ['document', 'excluded'], true)) {
                $this->assertSame([], $history['cases']);
            } else {
                $this->assertNotEmpty($history['cases']);
                foreach ($history['cases'] as $case) {
                    $this->assertNotEmpty($case['action']);
                    $this->assertNotEmpty($case['outcome']);
                }
            }
        }
        $this->assertStringContainsString('실제 신규 소셜 재가입은 하지 않았다', implode(' ', $pages[29]['history']['limitations']));
        $this->assertStringContainsString('최종 변경을 실행하지 않고', $pages[39]['history']['cases'][1]['outcome']);
        $this->assertStringContainsString('Google 실제 콜백', implode(' ', $pages[67]['history']['limitations']));
        $this->assertStringContainsString('실제 삭제 확정은 브라우저에서 하지 않고', implode(' ', $pages[129]['history']['limitations']));
        $this->assertSame('PPT 154~163번 연속 시연 기록', $pages[159]['history']['scope']);
        $this->assertStringContainsString('회원번호·대표이메일', $pages[35]['steps']);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_claims', 0);
    }

    public function test_accounts_are_selected_from_this_servers_database_by_signup_method_and_real_owner_link(): void
    {
        $general = User::factory()->create(['username' => 'existing-normal']);
        $social = User::factory()->create(['username' => 'existing-social']);
        SocialAccount::create(['user_id' => $social->id, 'provider' => 'google', 'provider_user_id' => 'private-provider-identifier']);
        $vendor = Vendor::forceCreate(['name' => '기존 회원사', 'email' => 'company@example.test', 'mobile' => '01000000000',
            'status' => 1, 'company_terms_accepted_at' => now()]);
        $channelMember = User::factory()->create(['username' => 'existing-owner', 'vendor_id' => $vendor->id, 'type' => 'vendor']);
        $owner = Admin::forceCreate(['vendor_id' => $vendor->id, 'name' => '연결된 채널관리자', 'email' => 'owner@example.test',
            'password' => Hash::make('PrivateOwnerPassword'), 'mobile' => '01000000000', 'type' => 'vendor', 'status' => 1, 'confirm' => 'Yes']);
        config(['storyboard.passwords.member' => 'NotThisMembersPassword']);
        $response = $this->actingAs($this->admin(), 'admin')->get(route('admin.storyboard_testbed', [
            'member_id' => $general->id, 'social_id' => $social->id, 'channel_member_id' => $channelMember->id,
        ]))->assertOk()->assertSee('existing-normal')->assertSee('existing-social')->assertSee('Google')
            ->assertSee('existing-owner')->assertSee('owner@example.test')->assertDontSee('private-provider-identifier')
            ->assertDontSee('PrivateOwnerPassword')->assertDontSee($owner->password)->assertDontSee('NotThisMembersPassword')
            ->assertSee('선택 계정과 설정 비밀번호 불일치');
        $cards = $response->viewData('accounts')->keyBy('key');
        $this->assertSame($general->id, $cards['member']['id']);
        $this->assertSame($social->id, $cards['social']['id']);
        $this->assertSame($channelMember->id, $cards['channel']['id']);
        $this->assertSame($vendor->id, $cards['channel']['vendor_id']);
        $this->assertCount(2, $cards['member']['options']);
        $this->assertCount(1, $cards['social']['options']);
        $this->get(route('admin.storyboard_testbed', ['member_id' => $social->id]))->assertNotFound();
        $this->assertDatabaseCount('users', 3);
        $this->assertDatabaseCount('social_accounts', 1);
    }
}
