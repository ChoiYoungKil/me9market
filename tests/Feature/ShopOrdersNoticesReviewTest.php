<?php

namespace Tests\Feature;

use App\Models\{Vendor, ShopChannel, Product, OrdersProduct, Order, OrderClaim, ShopChannelNotice, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ShopOrdersNoticesReviewTest extends TestCase
{
    use RefreshDatabase;

    private function shop(string $code='QA232'): ShopChannel
    {
        $vendor=Vendor::create(['name'=>'Review vendor','mobile'=>'01000000000','email'=>$code.'@example.test','status'=>1]);
        return ShopChannel::create(['vendor_id'=>$vendor->id,'channel_code'=>$code,'channel_name'=>'Review '.$code,'status'=>1,'is_public'=>1,'keywords'=>[],'settlement_type'=>1,'settlement_rate'=>0,'copyright'=>'QA']);
    }

    private function order(ShopChannel $shop,string $status='paid',int $userId=0): Order
    {
        $p=Product::create(['section_id'=>1,'category_id'=>1,'brand_id'=>1,'admin_id'=>1,'admin_type'=>'vendor','vendor_id'=>$shop->vendor_id,'product_name'=>'QA232 item','product_code'=>'QA232-'.uniqid(),'product_price'=>12000,'product_discount'=>0,'product_color'=>'Black','product_weight'=>1,'status'=>1]);
        $order=Order::forceCreate(['user_id'=>$userId,'name'=>'Recipient','buyer_name'=>'Review Buyer','buyer_mobile'=>'010-1111-2222','address'=>'Seoul','city'=>'Seoul','state'=>'Seoul','country'=>'Korea','pincode'=>'04524','mobile'=>'01099999999','email'=>'qa@example.test','shipping_charges'=>0,'order_status'=>'Payment Captured','payment_method'=>'Card','payment_gateway'=>'Me9 Mock Payment','grand_total'=>12000]);
        OrdersProduct::create(['order_id'=>$order->id,'user_id'=>$userId,'vendor_id'=>$shop->vendor_id,'shop_channel_id'=>$shop->id,'admin_id'=>1,'product_id'=>$p->id,'product_name'=>$p->product_name,'product_code'=>$p->product_code,'product_color'=>'Black','product_size'=>'Basic','product_price'=>12000,'product_qty'=>1,'line_total'=>12000,'supply_price'=>10000,'selling_price'=>12000,'status_code'=>$status,'item_status'=>\App\Support\OrderItemStatus::label($status),'commission'=>0,'settlement_status'=>'pending']);
        return $order;
    }

    private function enter(ShopChannel $shop): void { $this->get(route('shop.enter',$shop->channel_code))->assertRedirect(); }

    public function test_two_field_guest_lookup_scopes_to_matching_guest_orders_in_current_shop_and_expires(): void
    {
        $shop=$this->shop(); $a=$this->order($shop); $b=$this->order($shop); $member=$this->order($shop,'paid',User::factory()->create()->id); $other=$this->order($this->shop('OTHER'));
        $this->enter($shop);
        $this->get(route('front.shop.order.confirm'))->assertOk()->assertDontSee('name="order_id"',false);
        $this->post(route('front.shop.order.confirm.submit'),['name'=>'Review Buyer','phone_prefix'=>'010','phone_middle'=>'1111','phone_last'=>'2222'])->assertRedirect(route('front.shop.order.details'));
        $this->assertEqualsCanonicalizing([$a->id,$b->id],session('shop_verified_orders.ids'));
        $this->get(route('front.shop.order.view',$a->id))->assertOk();
        foreach([$member,$other] as $hidden)$this->get(route('front.shop.order.view',$hidden->id))->assertNotFound();
        $this->travel(31)->minutes();
        $this->get(route('front.shop.order.view',$a->id))->assertNotFound();
    }

    public function test_failed_lookup_clears_previous_verified_scope_and_member_lookup_cannot_access_another_member(): void
    {
        $shop=$this->shop(); $u=User::factory()->create(); $own=$this->order($shop,'paid',$u->id); $other=$this->order($shop,'paid',User::factory()->create()->id);
        $this->actingAs($u); $this->enter($shop);
        $this->post(route('front.shop.order.confirm.submit'),['name'=>'Review Buyer','phone'=>'01011112222'])->assertRedirect(route('front.shop.order.details'));
        $this->assertSame([$own->id],session('shop_verified_orders.ids'));
        $this->get(route('front.shop.order.view',$other->id))->assertNotFound();
        $this->postJson(route('front.shop.order.confirm.submit'),['name'=>'Wrong Name','phone'=>'01011112222'])->assertUnprocessable()->assertSessionMissing('shop_verified_orders');
    }

    public function test_claim_other_reason_is_required_and_resubmission_does_not_duplicate_claim_or_change_previous_status(): void
    {
        $shop=$this->shop(); $o=$this->order($shop); $item=$o->orders_products->sole(); $this->enter($shop); $this->withSession(['last_shop_order_id'=>$o->id]);
        $url=route('front.shop.order.item.status',$item->id);
        $this->postJson($url,['action'=>'cancel','reason_code'=>'other','reason_detail'=>'   '])->assertUnprocessable();
        $this->assertDatabaseCount('order_claims',0);
        $this->post($url,['action'=>'cancel','reason_code'=>'other','reason_detail'=>'Review cancellation'])->assertRedirect();
        $this->assertSame('paid',$item->fresh()->claim_previous_status);
        $this->assertSame('기타',OrderClaim::sole()->reason); $this->assertSame('Review cancellation',OrderClaim::sole()->detail_reason);
        $this->postJson($url,['action'=>'cancel','reason_code'=>'change_mind'])->assertUnprocessable();
        $this->assertDatabaseCount('order_claims',1);
        $this->assertSame('paid',$item->fresh()->claim_previous_status);
    }

    public function test_return_and_exchange_have_separate_lists_filters_and_closed_claim_shipment_cannot_change(): void
    {
        $shop=$this->shop(); $u=User::factory()->create(); $this->actingAs($u); $this->enter($shop);
        $r=$this->order($shop,'delivered',$u->id); $e=$this->order($shop,'delivered',$u->id);
        foreach([[$r,'return'],[$e,'exchange']] as [$o,$action])$this->post(route('front.shop.order.item.status',$o->orders_products->sole()->id),['action'=>$action,'reason_code'=>'defect','pickup_method'=>'manual'])->assertRedirect();
        $this->get(route('front.shop.return.details'))->assertOk()->assertViewHas('orders',fn($v)=>$v->pluck('id')->all()===[$r->id]);
        $this->get(route('front.shop.exchange.details'))->assertOk()->assertViewHas('orders',fn($v)=>$v->pluck('id')->all()===[$e->id]);
        $this->get(route('front.shop.return.details',['claim_status'=>'completed']))->assertOk()->assertViewHas('orders',fn($v)=>$v->isEmpty());
        $this->getJson(route('front.shop.return.details',['claim_status'=>'arbitrary']))->assertUnprocessable();
        OrderClaim::where('order_id',$e->id)->update(['status'=>'held_after']);
        $this->get(route('front.shop.exchange.details',['claim_status'=>'held']))->assertOk()->assertViewHas('orders',fn($v)=>$v->pluck('id')->all()===[$e->id]);
        $item=$r->orders_products->sole(); $claim=OrderClaim::where('order_product_id',$item->id)->sole();
        $this->post(route('front.shop.order.claim.shipment',$claim->id),['customer_courier_name'=>'QA courier','customer_tracking_number'=>'QA237'])->assertRedirect();
        $item->update(['status_code'=>'returned']);
        $this->postJson(route('front.shop.order.claim.shipment',$claim->id),['customer_courier_name'=>'Changed','customer_tracking_number'=>'FORGED'])->assertUnprocessable();
        $this->assertSame('QA237',$claim->fresh()->customer_tracking_number);
    }

    public function test_purchase_confirmation_rejects_early_and_non_half_ratings_and_only_records_once(): void
    {
        $shop=$this->shop(); $u=User::factory()->create(); $this->actingAs($u);$this->enter($shop); $o=$this->order($shop,'paid',$u->id);$item=$o->orders_products->sole();$url=route('front.shop.order.item.status',$item->id);
        $this->postJson($url,['action'=>'confirm','rating'=>4.5])->assertUnprocessable();
        $item->update(['status_code'=>'delivered']);
        $this->postJson($url,['action'=>'confirm','rating'=>4.7])->assertUnprocessable();
        $this->post($url,['action'=>'confirm','rating'=>4.5,'review'=>'QA239 review'])->assertRedirect();
        $this->assertSame('confirmed',$item->fresh()->status_code);$this->assertNotNull($item->fresh()->confirmed_at);
        $this->postJson($url,['action'=>'confirm','rating'=>4.5])->assertUnprocessable();
        $this->assertDatabaseCount('ratings',1);$this->assertDatabaseHas('ratings',['rating'=>4.5,'status'=>0]);
    }

    public function test_notices_search_content_pin_notice_and_preserve_list_context_with_scoped_download(): void
    {
        Storage::fake('local');$shop=$this->shop();$this->enter($shop);
        $make=fn($data)=>ShopChannelNotice::create(array_merge(['shop_channel_id'=>$shop->id,'type'=>'general','title'=>'QA245 title','content'=>'<p>BODY-ONLY-NEEDLE</p>','author'=>'Review','status'=>1],$data));
        $pin=$make(['type'=>'notice']);for($i=0;$i<11;$i++)$make([]);
        $other=$make(['shop_channel_id'=>$this->shop('OTHER')->id,'title'=>'Other secret']);$hidden=$make(['status'=>0,'title'=>'Hidden secret']);
        $r=$this->get(route('shop.notices',['search'=>'BODY-ONLY-NEEDLE']))->assertOk()->assertDontSee('Other secret')->assertDontSee('Hidden secret');
        $this->assertSame($pin->id,$r->viewData('notices')->first()->id);$this->assertSame(12,$r->viewData('notices')->total());
        $r=$this->get(route('shop.notices',['search'=>'BODY-ONLY-NEEDLE','page'=>2]))->assertOk();$notice=$r->viewData('notices')->first();
        Storage::disk('local')->put('channel-notices/'.$shop->id.'/qa246.txt','QA246 download bytes');
        $notice->forceFill(['attachment'=>'channel-notices/'.$shop->id.'/qa246.txt','attachment_name'=>'qa246.txt'])->save();
        $r=$this->get(route('shop.notices.show',['id'=>$notice->id,'search'=>'BODY-ONLY-NEEDLE','page'=>2]))->assertOk();
        $r->assertSee(route('shop.notices',['search'=>'BODY-ONLY-NEEDLE','page'=>2]));
        $this->get(route('shop.notices.attachment',$notice->id))->assertDownload('qa246.txt');
        foreach([$other,$hidden] as $private){$this->get(route('shop.notices.show',$private->id))->assertNotFound();$this->get(route('shop.notices.attachment',$private->id))->assertNotFound();}
        $this->getJson(route('shop.notices.show',['id'=>$notice->id,'page'=>-1]))->assertUnprocessable();
    }
}
