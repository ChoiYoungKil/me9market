<?php

namespace Tests\Feature;

use App\Models\{Category, Contact, JointPurchase, Order, Product, ProductsAttribute, ShopChannel, ShopChannelProduct, User, Vendor};
use App\Services\{JointPurchasePricingService, ShopChannelRuntime};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ShopStorefrontReviewTest extends TestCase
{
    use RefreshDatabase;

    private function shop(string $code = 'REVIEW217'): ShopChannel
    {
        $vendor = Vendor::create(['name' => 'Storefront vendor', 'mobile' => '01000000000', 'email' => $code.'@example.test', 'status' => 1]);
        return ShopChannel::create(['vendor_id' => $vendor->id, 'channel_code' => $code, 'channel_name' => 'Review shop '.$code, 'copyright' => 'QA', 'status' => 1, 'is_public' => 1, 'keywords' => ['Living', 'Stationery'], 'settlement_type' => 1, 'settlement_rate' => 0]);
    }

    private function product(ShopChannel $shop, string $code, float $price = 10000, int $category = 1): ShopChannelProduct
    {
        $product = Product::create(['section_id' => 1, 'category_id' => $category, 'brand_id' => 1, 'vendor_id' => $shop->vendor_id, 'admin_id' => 1, 'admin_type' => 'vendor', 'product_name' => 'Living '.$code, 'product_code' => $code, 'product_color' => 'Black', 'product_price' => $price, 'product_discount' => 0, 'product_weight' => 1, 'status' => 1, 'shipping_policy_type' => 'free']);
        return ShopChannelProduct::create(['shop_channel_id' => $shop->id, 'product_id' => $product->id, 'product_type' => 'own', 'approval_status' => 'approved', 'status' => 1, 'stock' => 20, 'constraint_type' => 'none', 'product_price' => $price, 'selling_price' => $price, 'profit' => 0]);
    }

    private function enter(ShopChannel $shop): void
    {
        $this->get(route('shop.enter', $shop->channel_code))->assertRedirect();
    }

    private function joint(ShopChannelProduct $listing): JointPurchase
    {
        $joint = JointPurchase::create(['product_id' => $listing->product_id, 'sale_name' => 'Group Living', 'purchase_code' => 'GROUP-'.$listing->id, 'min_quantity' => 5, 'current_quantity' => 0, 'discount_price' => 8000, 'status' => 1, 'start_date' => now()->toDateString(), 'end_date' => now()->addDay()->toDateString(), 'benefits' => [['quantity' => 5, 'amount' => 1000]]]);
        app(JointPurchasePricingService::class)->syncTiers($joint->id, [['min_quantity' => 1, 'max_quantity' => 4, 'unit_price' => 8000], ['min_quantity' => 5, 'max_quantity' => null, 'unit_price' => 7000]]);
        return $joint;
    }

    private function payload(string $token): array
    {
        return ['checkout_token' => $token, 'name' => 'Review Recipient', 'buyer_name' => 'Review Buyer', 'buyer_mobile' => '01000000000', 'mobile' => '01000000000', 'email' => 'qa@example.test', 'pincode' => '04524', 'address' => 'Seoul', 'address_detail' => 'Unit 217', 'order_confirmed' => 1, 'payment_method' => 'Card'];
    }

    public function test_main_filters_registered_keywords_and_never_leaks_other_shops(): void
    {
        $shop = $this->shop();
        $a = $this->product($shop, 'CODE217');
        $b = $this->product($shop, 'PAPER');
        $b->product->update(['product_name' => 'Stationery notebook']);
        $this->product($this->shop('OTHER'), 'PRIVATE-OTHER');
        $this->enter($shop);
        $this->get(route('shop.channel_main'))->assertOk()->assertSee('Living CODE217')->assertDontSee('PRIVATE-OTHER');
        $this->get(route('shop.channel_main', ['keyword' => 'Stationery']))->assertOk()->assertSee('Stationery notebook')->assertDontSee('Living CODE217');
        $this->getJson(route('shop.channel_main', ['keyword' => 'not-registered']))->assertUnprocessable();
        $this->get(route('shop.product_details', $a->id))->assertOk()->assertSee('data-purchase-dock', false)->assertSee('data-inquiry-editor-body', false);
    }

    public function test_hidden_additional_images_are_not_used_by_shop_gallery_cards_or_cart(): void
    {
        $shop = $this->shop();
        $listing = $this->product($shop, 'IMAGE-VISIBILITY');
        \App\Models\ProductsImage::forceCreate(['product_id' => $listing->product_id,
            'image' => 'hidden-additional.png', 'status' => 0]);
        \App\Models\ProductsImage::forceCreate(['product_id' => $listing->product_id,
            'image' => 'visible-additional.png', 'status' => 1]);
        $this->enter($shop);
        $this->get(route('shop.product_details', $listing->id))->assertOk()
            ->assertSee('visible-additional.png')->assertDontSee('hidden-additional.png');
        $this->get(route('shop.channel_main'))->assertOk()
            ->assertSee('visible-additional.png')->assertDontSee('hidden-additional.png');
        $this->post(route('front.shop.cart.add'), ['shop_product_id' => $listing->id, 'qty' => 1])->assertRedirect();
        $this->get(route('front.shop.cart.index'))->assertOk()
            ->assertSee('visible-additional.png')->assertDontSee('hidden-additional.png');
    }

    public function test_catalog_searches_codes_descendant_categories_and_separates_joint_products(): void
    {
        $root = Category::forceCreate(['parent_id' => 0, 'section_id' => 1, 'category_name' => 'Root', 'category_image' => '', 'url' => 'root', 'status' => 1]);
        $mid = Category::forceCreate(['parent_id' => $root->id, 'section_id' => 1, 'category_name' => 'Middle', 'category_image' => '', 'url' => 'middle', 'status' => 1]);
        $leaf = Category::forceCreate(['parent_id' => $mid->id, 'section_id' => 1, 'category_name' => 'Leaf', 'category_image' => '', 'url' => 'leaf', 'status' => 1]);
        $shop = $this->shop();
        $a = $this->product($shop, 'SEARCH219', 15000, $leaf->id);
        $b = $this->product($shop, 'CHEAP219', 5000, $leaf->id);
        $group = $this->product($shop, 'GROUP226', 12000, $leaf->id);
        $this->joint($group);
        $this->enter($shop);
        $this->get(route('shop.products_list', ['search' => 'SEARCH219']))->assertOk()->assertSee('Living SEARCH219')->assertDontSee('Living CHEAP219');
        $r = $this->get(route('shop.products_list', ['category1' => $root->id, 'category2' => $mid->id, 'category3' => $leaf->id, 'sort' => 'price_asc']))->assertOk()->assertDontSee('Living GROUP226');
        $this->assertSame([$b->id, $a->id], $r->viewData('products')->pluck('id')->all());
        $this->getJson(route('shop.products_list', ['category1' => $root->id, 'category2' => $leaf->id]))->assertUnprocessable();
        $this->get(route('shop.joint_purchases_list'))->assertOk()->assertSee('Living GROUP226')->assertDontSee('Living SEARCH219')->assertSee('마감일순');
    }

    public function test_product_inquiry_preserves_rich_text_rejects_empty_html_and_is_private_to_its_author(): void
    {
        $shop = $this->shop(); $p = $this->product($shop, 'INQUIRY224');
        $user = User::factory()->create(); $other = User::factory()->create();
        $this->actingAs($user); $this->enter($shop);
        $url = route('front.shop.order.inquiry');
        $base = ['shop_product_id' => $p->id, 'inquiry_category' => 'product', 'subject' => 'Question 224'];
        $this->postJson($url, $base + ['message' => '<p><br></p><script>alert(1)</script>'])->assertUnprocessable();
        $this->assertDatabaseCount('contacts', 0);
        $this->post($url, $base + ['message' => '<p><strong>Keep bold</strong></p><ol><li>Line 2</li></ol><img src=x onerror=alert(1)><script>bad()</script>'])->assertRedirect(route('shop.product_details', $p->id).'#inquiry-panel');
        $c = Contact::sole();
        $this->assertStringContainsString('<strong>Keep bold</strong>', $c->message);
        $this->assertStringNotContainsString('onerror', $c->message);
        $this->assertStringNotContainsString('bad()', $c->message);
        $this->get(route('shop.product_details', $p->id))->assertOk()->assertSee('<strong>Keep bold</strong>', false)->assertSee('Question 224');
        $this->actingAs($other)->get(route('shop.product_details', $p->id))->assertOk()->assertDontSee('Question 224');
    }

    public function test_selected_checkout_is_idempotent_and_preserves_unselected_cart_and_stock(): void
    {
        Mail::fake(); $shop = $this->shop();
        $a = $this->product($shop, 'SELECTED228'); $b = $this->product($shop, 'REMAINING228');
        $this->enter($shop);
        foreach ([$a, $b] as $p) $this->post(route('front.shop.cart.add'), ['shop_product_id' => $p->id, 'qty' => 1])->assertRedirect(route('front.shop.cart.index'));
        $this->postJson(route('front.shop.cart.checkout_selection'), [])->assertUnprocessable();
        $this->postJson(route('front.shop.cart.checkout_selection'), ['shop_product_ids' => [999999]])->assertUnprocessable();
        $this->post(route('front.shop.cart.checkout_selection'), ['shop_product_ids' => [(string)$a->id]])->assertRedirect(route('front.shop.order.form'));
        $r = $this->get(route('front.shop.order.form'))->assertOk()->assertSee('Living SELECTED228')->assertDontSee('Living REMAINING228');
        $token = $r->viewData('checkoutToken');
        $this->post(route('front.shop.order.checkout'), $this->payload($token))->assertRedirect(route('front.shop.order.complete'));
        $this->post(route('front.shop.order.checkout'), $this->payload($token))->assertRedirect(route('front.shop.order.complete'));
        $this->assertDatabaseCount('orders', 1);
        $this->assertSame(19, (int)$a->fresh()->stock); $this->assertSame(20, (int)$b->fresh()->stock);
        $this->assertSame('Unit 217', Order::sole()->address_detail);
        $this->assertSame([$b->id], array_column(app(ShopChannelRuntime::class)->cartItems(), 'id'));
        $this->get(route('front.shop.order.view', Order::sole()->id))->assertOk();
    }

    public function test_buy_now_and_order_removal_cannot_pull_unselected_cart_items_into_checkout(): void
    {
        $shop = $this->shop(); $a = $this->product($shop, 'DIRECT220'); $b = $this->product($shop, 'KEEP228');
        $this->enter($shop);
        $this->post(route('front.shop.cart.add'), ['shop_product_id' => $b->id, 'qty' => 1]);
        $this->post(route('front.shop.cart.add'), ['shop_product_id' => $a->id, 'qty' => 1, 'buy_now' => 1])->assertRedirect(route('front.shop.order.form'));
        $this->assertSame([$a->id], array_column(app(ShopChannelRuntime::class)->checkoutItems(), 'id'));
        $this->post(route('front.shop.order.remove_item'), ['cart_key' => (string)$a->id])->assertRedirect();
        $this->assertSame([], app(ShopChannelRuntime::class)->checkoutItems());
        $this->assertCount(2, app(ShopChannelRuntime::class)->cartItems());
        $this->postJson(route('front.shop.order.checkout'), $this->payload((string)$this->get(route('front.shop.order.form'))->viewData('checkoutToken')))->assertUnprocessable();
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_authenticated_multi_option_cart_update_preserves_string_and_numeric_keys(): void
    {
        $shop = $this->shop(); $p = $this->product($shop, 'MULTI228');
        foreach (['Black','White'] as $option) ProductsAttribute::create(['product_id'=>$p->product_id,'size'=>$option,'sku'=>$option,'price'=>10000,'stock'=>10,'status'=>1]);
        $this->actingAs(User::factory()->create()); $this->enter($shop);
        $this->post(route('front.shop.cart.add'), ['shop_product_id'=>$p->id,'options'=>[['option'=>'Black','qty'=>1],['option'=>'White','qty'=>2]]])->assertRedirect();
        $this->post(route('front.shop.cart.update'), ['shop_product_id'=>$p->id,'cart_key'=>(string)$p->id,'option'=>'Black','qty'=>2])->assertRedirect();
        $items=app(ShopChannelRuntime::class)->cartItems();
        $this->assertCount(2,$items); $this->assertSame([2,2],array_column($items,'qty'));
        $this->post(route('front.shop.cart.remove'), ['shop_product_id'=>$p->id,'cart_key'=>$items[1]['key']])->assertRedirect();
        $this->assertCount(1,app(ShopChannelRuntime::class)->cartItems());
    }

    public function test_member_order_contact_fields_are_readonly_and_server_ignores_forged_values(): void
    {
        Mail::fake(); $shop=$this->shop(); $p=$this->product($shop,'CONTACT229');
        $user=User::factory()->create(['username'=>'review-229','member_number'=>'M9-REVIEW-229','email'=>'member229@example.test','mobile'=>'01000002290']);
        $this->actingAs($user); $this->enter($shop);
        $this->post(route('front.shop.cart.add'),['shop_product_id'=>$p->id,'qty'=>1])->assertRedirect();
        $r=$this->get(route('front.shop.order.form'))->assertOk()->assertSee('review-229')->assertSee('M9-REVIEW-229');
        $dom=new \DOMDocument();@$dom->loadHTML($r->getContent());$xpath=new \DOMXPath($dom);
        foreach(['buyer_mobile','email'] as $field)$this->assertSame(1,$xpath->query('//input[@name="'.$field.'" and @readonly]')->length);
        $payload=$this->payload($r->viewData('checkoutToken'));$payload['email']='forged@example.test';$payload['buyer_mobile']='01099999999';
        $this->post(route('front.shop.order.checkout'),$payload)->assertRedirect(route('front.shop.order.complete'));
        $this->assertSame($user->email,Order::sole()->email);$this->assertSame($user->mobile,Order::sole()->buyer_mobile);
    }

    public function test_joint_details_hide_option_picker_and_only_expose_masked_active_participants(): void
    {
        $shop = $this->shop(); $p = $this->product($shop, 'GROUP227'); $joint = $this->joint($p);
        $this->enter($shop);
        $this->get(route('shop.joint_purchase_details', $joint->id))->assertOk()->assertSee('공동구매현황')->assertDontSee('data-option-toggle', false)->assertSee('GROUP-'.$p->id);
        $p->product->update(['status' => 0]);
        $this->get(route('shop.joint_purchase_details', $joint->id))->assertNotFound();
    }

    public function test_checkout_preserves_every_privacy_paragraph_and_table_cell_with_required_consent(): void
    {
        $shop = $this->shop(); $p = $this->product($shop, 'NOTICE229'); $this->enter($shop);
        $this->post(route('front.shop.cart.add'), ['shop_product_id'=>$p->id,'qty'=>1]);
        $r = $this->get(route('front.shop.order.form'))->assertOk();
        $document = json_decode(file_get_contents(resource_path('policies/privacy.json')), true);
        foreach (array_slice($document['blocks'], 2) as $block) {
            foreach ($block['type'] === 'table' ? array_merge(...$block['rows']) : [$block['text']] as $text) $r->assertSee($text);
        }
        $r->assertSee('전문 펼쳐보기')->assertSee('scope="col"', false)->assertSee('data-label="근거와 기간"', false);
        $payload = $this->payload($r->viewData('checkoutToken')); unset($payload['order_confirmed']);
        $this->postJson(route('front.shop.order.checkout'), $payload)->assertUnprocessable()->assertJsonValidationErrors('order_confirmed');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_single_point_input_is_allocated_against_locked_balances_and_recipient_contact_is_saved_separately(): void
    {
        Mail::fake(); $shop=$this->shop(); $p=$this->product($shop,'POINT229');
        $user=User::factory()->create(['email'=>'buyer@example.test','mobile'=>'01000002290']);
        foreach ([$shop->id=>200,0=>300] as $channel=>$points) \App\Models\PointTransaction::forceCreate(['user_id'=>$user->id,'shop_channel_id'=>$channel ?: null,'type'=>'earn','points'=>$points,'description'=>'QA229']);
        $this->actingAs($user); $this->enter($shop);
        $this->post(route('front.shop.cart.add'),['shop_product_id'=>$p->id,'qty'=>1]);
        $token=$this->get(route('front.shop.order.form'))->assertOk()->viewData('checkoutToken');
        $payload=$this->payload($token)+['point_use'=>400,'recipient_email_local'=>'recipient','recipient_email_domain'=>'example.test','recipient_phone_prefix'=>'010','recipient_phone_middle'=>'1234','recipient_phone_last'=>'5678','channel_points'=>999999,'me9_points'=>999999];
        $this->postJson(route('front.shop.order.checkout'),array_replace($payload,['point_use'=>501]))->assertUnprocessable();
        $this->assertDatabaseCount('orders',0);
        $this->post(route('front.shop.order.checkout'),$payload)->assertRedirect(route('front.shop.order.complete'));
        $order=Order::sole();
        $this->assertEquals(9600,$order->grand_total); $this->assertEquals(400,$order->used_point);
        $this->assertSame('recipient@example.test',$order->recipient_email); $this->assertSame($user->email,$order->email);
        $this->assertSame('010-1234-5678',$order->mobile);
        $this->assertSame(['channel'=>0,'me9'=>100],app(\App\Services\CustomerPointService::class)->balances($user->id,$shop->id));
        $this->post(route('front.shop.order.checkout'),$payload)->assertRedirect();
        $this->assertDatabaseCount('orders',1);
        $this->assertSame(['channel'=>0,'me9'=>100],app(\App\Services\CustomerPointService::class)->balances($user->id,$shop->id));
    }

    public function test_receipt_keeps_original_payment_snapshot_after_joint_price_changes(): void
    {
        Mail::fake(); $shop=$this->shop(); $p=$this->product($shop,'RECEIPT231'); $this->joint($p);
        $p->product->update(['shipping_policy_type'=>'fixed','shipping_base_fee'=>2500]);
        $this->enter($shop); $this->post(route('front.shop.cart.add'),['shop_product_id'=>$p->id,'qty'=>1]);
        $token=$this->get(route('front.shop.order.form'))->viewData('checkoutToken');
        $this->post(route('front.shop.order.checkout'),$this->payload($token)+['recipient_email'=>'receiver@example.test'])->assertRedirect();
        $order=Order::sole(); $order->orders_products->first()->update(['line_total'=>7000,'product_price'=>7000]);
        $this->get(route('front.shop.order.view',$order->id))->assertOk()->assertSee('receiver@example.test')->assertSee('8,000원')->assertSee('2,500원')->assertSee('10,500원')->assertDontSee('7,000원');
    }

    public function test_cart_tabs_and_selection_totals_use_only_selected_products_and_do_not_mutate_the_cart(): void
    {
        $shop=$this->shop(); $own=$this->product($shop,'OWN228'); $public=$this->product($shop,'PUBLIC228',8000); $public->update(['product_type'=>'public']);
        $this->enter($shop);
        foreach([$own,$public] as $p)$this->post(route('front.shop.cart.add'),['shop_product_id'=>$p->id,'qty'=>2]);
        $this->get(route('front.shop.cart.index'))->assertOk()->assertSee('Living OWN228')->assertDontSee('Living PUBLIC228');
        $this->get(route('front.shop.cart.index',['type'=>'public']))->assertOk()->assertSee('Living PUBLIC228')->assertDontSee('Living OWN228');
        $this->postJson(route('front.shop.cart.selection_total'),['shop_product_ids'=>[(string)$public->id]])->assertOk()->assertJson(['subtotal'=>16000,'shipping'=>0,'total'=>16000,'quantity'=>2]);
        $this->postJson(route('front.shop.cart.selection_total'),[])->assertOk()->assertJson(['total'=>0,'quantity'=>0]);
        $this->postJson(route('front.shop.cart.selection_total'),['shop_product_ids'=>['999999']])->assertUnprocessable();
        $this->assertCount(2,app(ShopChannelRuntime::class)->cartItems());
    }

    public function test_catalog_paginates_and_joint_deadline_and_price_sorts_use_the_displayed_prices(): void
    {
        $shop=$this->shop(); $this->enter($shop);
        for($i=0;$i<13;$i++)$this->product($shop,'PAGE219-'.$i);
        $r=$this->get(route('shop.products_list'))->assertOk(); $this->assertCount(12,$r->viewData('products')); $this->assertSame(13,$r->viewData('products')->total());
        $this->assertCount(1,$this->get(route('shop.products_list',['page'=>2]))->assertOk()->viewData('products'));
        $a=$this->product($shop,'JOINT-A'); $b=$this->product($shop,'JOINT-B'); $ja=$this->joint($a); $jb=$this->joint($b);
        $ja->update(['end_date'=>now()->addDays(2)->toDateString()]);
        app(JointPurchasePricingService::class)->syncTiers($jb->id,[['min_quantity'=>1,'max_quantity'=>null,'unit_price'=>6000]]);
        foreach(['deadline','price_asc'] as $sort) $this->assertSame([$b->id,$a->id],$this->get(route('shop.joint_purchases_list',['sort'=>$sort]))->assertOk()->viewData('products')->pluck('id')->all());
        $this->assertSame([$a->id,$b->id],$this->get(route('shop.joint_purchases_list',['sort'=>'price_desc']))->assertOk()->viewData('products')->pluck('id')->all());
    }
    public function test_selected_shop_shipping_policy_matches_product_cart_checkout_and_order_snapshot(): void
    {
        Mail::fake();
        $shop=$this->shop('SHIPPING1101');$listing=$this->product($shop,'SHIPPING1101');
        $policy=\App\Models\ChannelDeliveryCharge::create(['vendor_id'=>$shop->vendor_id,'status'=>1,'name'=>'Shop 선택 배송','shipping_type'=>'fixed','payment_type'=>'prepaid','base_fee'=>4500,'fixed_fee'=>4500]);
        $listing->update(['shipping_policy_id'=>$policy->id]);$this->enter($shop);
        $this->get(route('shop.product_details',$listing->id))->assertOk()->assertSee('4,500원')->assertSee('Shop 선택 배송');
        $this->post(route('front.shop.cart.add'),['shop_product_id'=>$listing->id,'qty'=>1])->assertRedirect();
        $this->get(route('front.shop.cart.index'))->assertOk()->assertSee('Shop 선택 배송')->assertSee('14,500원');
        $page=$this->get(route('front.shop.order.form'))->assertOk()->assertSee('Shop 선택 배송')->assertSee('14,500원');
        $token=$page->viewData('checkoutToken');
        $this->post(route('front.shop.order.checkout'),$this->payload($token))->assertRedirect(route('front.shop.order.complete'));
        $order=Order::latest('id')->firstOrFail();
        $this->assertEquals(14500,$order->grand_total);
        $item=$order->orders_products->first();$this->assertEquals(4500,$item->shipping_amount_snapshot);
        $policy->update(['base_fee'=>5000,'fixed_fee'=>5000]);
        $this->assertEquals(4500,$item->fresh()->shipping_amount_snapshot);
    }
}
