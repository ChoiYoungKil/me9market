<?php
namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Contact;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_does_not_show_another_members_inquiry_when_phone_is_shared(): void
    {
        $user=User::factory()->create(['mobile'=>'010-0000-2222']);
        $other=User::factory()->create(['mobile'=>$user->mobile]);
        foreach ([[$user,'MY-INQUIRY'],[$other,'OTHER-PRIVATE-INQUIRY']] as [$owner,$subject]) {
            Contact::create(['user_id'=>$owner->id,'name'=>$owner->name,'email'=>$owner->email,'phone'=>$owner->mobile,'subject'=>$subject,'message'=>'검수 내용','type'=>'partnership','status'=>'pending']);
        }
        $this->actingAs($user)->get('/mypage/main')->assertOk()->assertSee('MY-INQUIRY')->assertDontSee('OTHER-PRIVATE-INQUIRY')->assertSee('최근 주문')->assertSee('방문한 채널')->assertSee('서브관리 채널목록');
    }

    public function test_shortcuts_persist_and_reject_external_or_unknown_links(): void
    {
        $user=User::factory()->create();
        $this->actingAs($user)->post(route('mypage.shortcuts'),['shortcuts'=>['delivery','wishlist']])->assertRedirect(route('mypage.dashboard'));
        $this->assertSame(['delivery','wishlist'],$user->fresh()->mypage_shortcuts);
        $this->postJson(route('mypage.shortcuts'),['shortcuts'=>['https://example.test']])->assertUnprocessable();
        $this->assertSame(['delivery','wishlist'],$user->fresh()->mypage_shortcuts);
    }

    public function test_approved_member_can_open_own_channel_without_changing_member_login(): void
    {
        $vendor=new Vendor;$vendor->name='검수 회원사';$vendor->email='owner@example.test';$vendor->mobile='01000002222';
        $vendor->status=1;$vendor->confirm='Yes';$vendor->commission=0;$vendor->company_terms_accepted_at=now();$vendor->save();
        $user=User::factory()->create(['vendor_id'=>$vendor->id,'type'=>'vendor','status'=>1,'mobile'=>'010-0000-2222']);
        $this->actingAs($user)->get(route('mypage.dashboard'))->assertOk()->assertSee('class="mypage-channel-launch"', false);
        $this->assertDatabaseCount('admins', 0);
        $this->actingAs($user)->post(route('mypage.channel_admin'))->assertRedirect(route('channel.index'));
        $owner=Admin::where('vendor_id',$vendor->id)->where('type','vendor')->firstOrFail();
        $this->assertAuthenticatedAs($owner,'admin');$this->assertAuthenticatedAs($user,'web');
        $this->post(route('mypage.channel_admin'))->assertRedirect();
        $this->assertSame(1,Admin::where('vendor_id',$vendor->id)->count());
    }

    public function test_general_member_cannot_open_owner_or_unassigned_subadmin_portal(): void
    {
        $user=User::factory()->create(['type'=>'general','status'=>1]);
        $this->actingAs($user)->get(route('mypage.dashboard'))->assertOk()->assertDontSee('class="mypage-channel-launch"', false);
        $this->actingAs($user)->post(route('mypage.channel_admin'))->assertRedirect(route('mypage.dashboard'))
            ->assertSessionHas('error_message');
        $this->post(route('mypage.channel_admin'),['sub_account_id'=>99999])->assertForbidden();
        $this->assertGuest('admin');
    }

    public function test_single_assigned_subadmin_opens_its_channel_and_multiple_assignments_require_selection(): void
    {
        $user = User::factory()->create(['type' => 'general', 'status' => 1]);
        $accounts = [];
        foreach ([1, 2] as $index) {
            $vendor = Vendor::forceCreate(['name' => 'Assigned '.$index, 'email' => 'assigned-'.$index.'@example.test',
                'mobile' => '01000000000', 'status' => 1]);
            $admin = Admin::forceCreate(['vendor_id' => $vendor->id, 'type' => 'subadmin', 'status' => 1, 'confirm' => 'Yes',
                'name' => 'Assigned staff '.$index, 'email' => 'staff-'.$index.'@example.test',
                'mobile' => '01000000000', 'password' => bcrypt('ExistingTestPassword')]);
            $accounts[] = \App\Models\ChannelSubAccount::create(['vendor_id' => $vendor->id, 'admin_id' => $admin->id,
                'user_id' => $user->id, 'member_no' => $user->member_number,
                'started_at' => now()->subHour(), 'ended_at' => now()->addHour(), 'permissions' => ['shop']]);
            if ($index === 1) {
                $this->actingAs($user)->get(route('mypage.dashboard'))->assertOk()->assertSee('class="mypage-channel-launch"', false);
                $this->actingAs($user)->post(route('mypage.channel_admin'))->assertRedirect(route('channel.index'));
                $this->assertAuthenticatedAs($admin, 'admin');
                $this->assertAuthenticatedAs($user, 'web');
                $this->get(route('channel.product_own'))->assertForbidden();
                \Illuminate\Support\Facades\Auth::guard('admin')->logout();
            }
        }
        $this->actingAs($user)->post(route('mypage.channel_admin'))->assertRedirect(route('mypage.dashboard'))
            ->assertSessionHas('error_message', fn ($message) => str_contains($message, '여러 개'));
        $this->assertGuest('admin');
        $this->post(route('mypage.channel_admin'), ['sub_account_id' => $accounts[1]->id])
            ->assertRedirect(route('channel.index'));
        $this->assertAuthenticatedAs($accounts[1]->admin, 'admin');
    }

    public function test_unavailable_channel_does_not_replace_an_existing_admin_session_or_create_an_owner(): void
    {
        $admin = Admin::forceCreate(['name' => 'Existing admin', 'type' => 'admin', 'vendor_id' => 0,
            'status' => 1, 'confirm' => 'Yes', 'email' => 'existing-admin@example.test',
            'mobile' => '01000000000', 'password' => bcrypt('ExistingTestPassword')]);
        $vendor = Vendor::forceCreate(['name' => 'Incomplete company', 'email' => 'incomplete@example.test',
            'mobile' => '01000000000', 'status' => 0, 'company_terms_accepted_at' => null]);
        $user = User::factory()->create(['type' => 'company', 'vendor_id' => $vendor->id, 'status' => 1]);
        $this->actingAs($admin, 'admin')->actingAs($user, 'web')->post(route('mypage.channel_admin'))
            ->assertRedirect(route('mypage.dashboard'))->assertSessionHas('error_message');
        $this->assertAuthenticatedAs($admin, 'admin');
        $this->assertDatabaseCount('admins', 1);
    }

    public function test_header_hides_channel_entry_for_stopped_future_and_expired_assignments(): void
    {
        $user = User::factory()->create(['type' => 'general', 'status' => 1]);
        $vendor = Vendor::forceCreate(['name' => 'Visibility review', 'email' => 'visibility@example.test',
            'mobile' => '01000000000', 'status' => 1]);
        $admin = Admin::forceCreate(['vendor_id' => $vendor->id, 'type' => 'subadmin', 'status' => 1, 'confirm' => 'Yes',
            'name' => 'Visibility staff', 'email' => 'visibility-staff@example.test',
            'mobile' => '01000000000', 'password' => bcrypt('ExistingTestPassword')]);
        $account = \App\Models\ChannelSubAccount::create(['vendor_id' => $vendor->id, 'admin_id' => $admin->id,
            'user_id' => $user->id, 'member_no' => $user->member_number, 'permissions' => ['shop'],
            'started_at' => now()->subHour(), 'ended_at' => now()->addHour()]);
        $this->actingAs($user)->get(route('mypage.coupon'))->assertOk()->assertSee('class="mypage-channel-launch"', false);
        $admin->status = 0; $admin->save();
        $this->get(route('mypage.coupon'))->assertOk()->assertDontSee('class="mypage-channel-launch"', false);
        $admin->status = 1; $admin->save();
        $account->update(['started_at' => now()->addHour(), 'ended_at' => now()->addHours(2)]);
        $this->get(route('mypage.coupon'))->assertOk()->assertDontSee('class="mypage-channel-launch"', false);
        $account->update(['started_at' => now()->subHours(2), 'ended_at' => now()->subHour()]);
        $this->get(route('mypage.coupon'))->assertOk()->assertDontSee('class="mypage-channel-launch"', false);
    }
}
