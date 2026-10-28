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
        $this->actingAs($user)->post(route('mypage.channel_admin'))->assertRedirect(route('channel.index'));
        $owner=Admin::where('vendor_id',$vendor->id)->where('type','vendor')->firstOrFail();
        $this->assertAuthenticatedAs($owner,'admin');$this->assertAuthenticatedAs($user,'web');
        $this->post(route('mypage.channel_admin'))->assertRedirect();
        $this->assertSame(1,Admin::where('vendor_id',$vendor->id)->count());
    }

    public function test_general_member_cannot_open_owner_or_unassigned_subadmin_portal(): void
    {
        $user=User::factory()->create(['type'=>'general','status'=>1]);
        $this->actingAs($user)->post(route('mypage.channel_admin'))->assertForbidden();
        $this->post(route('mypage.channel_admin'),['sub_account_id'=>99999])->assertForbidden();
        $this->assertGuest('admin');
    }
}
