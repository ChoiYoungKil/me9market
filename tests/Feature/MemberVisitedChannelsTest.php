<?php
namespace Tests\Feature;

use App\Models\{User,Vendor,ShopChannel,VisitedChannel};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberVisitedChannelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_visits_use_actual_shop_codes_names_and_local_qr_and_filter_each_shop(): void
    {
        $vendor=Vendor::create(['name'=>'검수 회원사','email'=>'visits@example.test','mobile'=>'01000000000','status'=>1,'commission'=>0]);
        $user=User::factory()->create();$other=User::factory()->create();
        foreach(['qa-home'=>'생활용품','qa-paper'=>'문구'] as $code=>$name){
            $shop=ShopChannel::create(['vendor_id'=>$vendor->id,'channel_code'=>$code,'channel_name'=>$name,'status'=>1,'copyright'=>'검수','keywords'=>[]]);
            VisitedChannel::create(['user_id'=>$user->id,'vendor_id'=>$vendor->id,'shop_channel_id'=>$shop->id]);
        }
        $private=ShopChannel::create(['vendor_id'=>$vendor->id,'channel_code'=>'other-private','channel_name'=>'다른 회원만 방문','status'=>1,'copyright'=>'검수','keywords'=>[]]);
        VisitedChannel::create(['user_id'=>$other->id,'vendor_id'=>$vendor->id,'shop_channel_id'=>$private->id]);
        $this->actingAs($user)->get(route('mypage.visited_channels'))->assertOk()->assertSee('생활용품')->assertSee('문구')->assertSee(route('shop.enter','qa-home'))->assertSee('data:image/png;base64,',false)->assertDontSee('다른 회원만 방문');
        $this->get(route('mypage.visited_channels',['search'=>'문구']))->assertOk()->assertSee('문구')->assertDontSee('생활용품');
        $this->get(route('mypage.visited_channels',['search'=>'없음']))->assertOk()->assertSee('검색된 방문 채널이 없습니다.');
    }

    public function test_individual_and_all_deletion_only_remove_the_logged_in_members_visits(): void
    {
        $vendor=Vendor::create(['name'=>'검수','email'=>'delete@example.test','mobile'=>'01000000000','status'=>1,'commission'=>0]);
        $user=User::factory()->create();$other=User::factory()->create();
        $own=VisitedChannel::create(['user_id'=>$user->id,'vendor_id'=>$vendor->id]);
        $foreign=VisitedChannel::create(['user_id'=>$other->id,'vendor_id'=>$vendor->id]);
        $this->actingAs($user)->post(route('mypage.visited_channels.delete',$foreign->id))->assertRedirect();
        $this->assertDatabaseHas('visited_channels',['id'=>$foreign->id]);
        $this->post(route('mypage.visited_channels.delete',$own->id))->assertRedirect();
        $this->assertDatabaseMissing('visited_channels',['id'=>$own->id]);
        VisitedChannel::create(['user_id'=>$user->id,'vendor_id'=>$vendor->id]);
        $this->post(route('mypage.visited_channels.delete_all'))->assertRedirect();
        $this->assertSame(0,VisitedChannel::where('user_id',$user->id)->count());
        $this->assertDatabaseHas('visited_channels',['id'=>$foreign->id]);
    }
}
