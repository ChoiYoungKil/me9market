<?php
namespace Tests\Feature;
use App\Models\{User,Order,OrdersProduct,Contact};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class MemberOrderInquiryTest extends TestCase {
    use RefreshDatabase;
    public function test_inquiry_is_linked_to_own_order_and_cannot_spoof_owner_or_seller(): void {
        $user=User::factory()->create();$other=User::factory()->create();
        $order=Order::forceCreate(['user_id'=>$user->id,'name'=>'검수','address'=>'검수 주소','city'=>'검수','state'=>'서울','country'=>'Korea','pincode'=>'04524','mobile'=>'01000000000','email'=>$user->email,'shipping_charges'=>0,'order_status'=>'New','payment_method'=>'Mock Card','payment_gateway'=>'Me9 Mock Payment','grand_total'=>1000]);
        $item=OrdersProduct::create(['order_id'=>$order->id,'user_id'=>$user->id,'vendor_id'=>10,'shop_channel_id'=>20,'admin_id'=>1,'product_id'=>1,'product_code'=>'QA-INQ','product_name'=>'검수상품','product_color'=>'검정','product_size'=>'기본옵션','product_price'=>1000,'product_qty'=>1,'item_status'=>'배송완료']);
        $data=['order_item_id'=>$item->id,'subject'=>'판매자 확인 요청','message'=>'검수 문의 내용','user_id'=>$other->id,'vendor_id'=>999];
        $this->actingAs($other)->post(route('mypage.order.inquiry'),$data)->assertNotFound();
        $this->assertSame(0,Contact::count());
        $this->actingAs($user)->from(route('mypage.order.list'))->post(route('mypage.order.inquiry'),$data)->assertRedirect(route('mypage.order.list'))->assertSessionHas('success_message');
        $this->assertDatabaseHas('contacts',['user_id'=>$user->id,'vendor_id'=>10,'shop_channel_id'=>20,'order_id'=>$order->id,'order_product_id'=>$item->id,'subject'=>'판매자 확인 요청','type'=>'inquiry','status'=>'pending']);
    }
}
