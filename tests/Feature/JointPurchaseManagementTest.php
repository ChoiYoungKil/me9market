<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Distributor;
use App\Models\JointPurchase;
use App\Models\Section;
use App\Models\ShopCancelRefundPolicy;
use App\Models\Vendor;
use App\Services\JointPurchasePricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Tests\TestCase;

class JointPurchaseManagementTest extends TestCase
{
    use RefreshDatabase;

    private function setupForm(): array
    {
        $vendor = Vendor::forceCreate(['name' => 'Joint Review', 'email' => 'jreview@example.test', 'mobile' => '01000000000', 'status' => 1, 'confirm' => 'Yes', 'commission' => 0]);
        $admin = Admin::forceCreate(['vendor_id' => $vendor->id, 'type' => 'vendor', 'name' => 'Review', 'email' => 'jadmin@example.test', 'mobile' => '01000000000', 'password' => bcrypt('test'), 'status' => 1]);
        $section = Section::forceCreate(['name' => 'Review', 'status' => 1]);
        $major = Category::forceCreate(['section_id' => $section->id, 'parent_id' => 0, 'category_name' => '의류/패션', 'category_image' => '', 'url' => 'review-fashion', 'status' => 1]);
        $brand = Brand::forceCreate(['name' => 'Review', 'status' => 1]);
        $this->actingAs($admin, 'admin');

        return [$admin, ['purchase_code' => 'JOINT-130', 'product_name' => 'Review joint product', 'sale_name' => 'Review sale', 'sale_price' => 10000, 'base_amount' => 7000, 'brand_id' => $brand->id, 'major_category_id' => $major->id, 'tax_type' => 'taxable', 'joint_status' => 0, 'period_enabled' => 0, 'detail_display_type' => 'text', 'detail_text' => '<p><strong>Review</strong><script>bad()</script></p>', 'order_manager_enabled' => 0, 'shipping_policy_type' => 'free', 'shipping_payment_type' => 'prepaid', 'benefit_quantity' => [100, 200], 'benefit_amount' => [1000, 2000]]];
    }

    public function test_registration_saves_all_fields_without_period_and_applies_per_item_discount(): void
    {
        [, $data] = $this->setupForm();
        $this->post(route('channel.joint_purchase.store'), $data)->assertRedirect(route('channel.joint_purchase.list'))->assertSessionHasNoErrors();
        $joint = JointPurchase::firstOrFail();
        $this->assertEquals(0, $joint->status);
        $this->assertNull($joint->start_date);
        $this->assertNull($joint->end_date);
        $this->assertEquals(7000, $joint->product->product_price);
        $this->assertStringNotContainsString('<script', $joint->product->detail_text);
        $service = app(JointPurchasePricingService::class);
        $this->assertEquals(10000, $service->priceForQuantity($joint, 99)['unit_price']);
        $this->assertEquals(9000, $service->priceForQuantity($joint, 100)['unit_price']);
        $this->assertEquals(8000, $service->priceForQuantity($joint, 200)['unit_price']);
        $this->assertNull($service->activePurchaseForProduct($joint->product_id));
        $joint->update(['status' => 1]);
        $this->assertEquals($joint->id, $service->activePurchaseForProduct($joint->product_id)->id);
    }

    public function test_invalid_benefits_dates_and_uploads_never_create_product(): void
    {
        [, $data] = $this->setupForm();
        foreach ([
            [['benefit_quantity' => [100, 100]], 'benefit_quantity'],
            [['benefit_amount' => [11000, 12000]], 'benefit_amount.0'],
            [['benefit_amount' => [2000, 1000]], 'benefit_amount'],
            [['period_enabled' => 1, 'start_date' => '2026-11-02', 'end_date' => '2026-11-01'], 'end_date'],
            [['product_image' => UploadedFile::fake()->createWithContent('bad.php', '<?php echo 1;')], 'product_image'],
            [['slot_images' => array_fill(0, 5, UploadedFile::fake()->image('review.png', 10, 10))], 'slot_images'],
        ] as [$replace,$error]) {
            $this->postJson(route('channel.joint_purchase.store'), array_replace($data, $replace))->assertUnprocessable()->assertJsonValidationErrors($error);
        }
        $this->assertDatabaseCount('joint_purchases', 0);
        $this->assertDatabaseCount('products', 0);
    }

    public function test_edit_blocks_benefit_tampering_and_keeps_original_pricing_rows(): void
    {
        [, $data] = $this->setupForm();
        $this->post(route('channel.joint_purchase.store'), $data)->assertSessionHasNoErrors();
        $joint = JointPurchase::firstOrFail();
        $original = DB::table('joint_purchase_price_tiers')->where('joint_purchase_id', $joint->id)->get()->toJson();
        $this->postJson(route('channel.joint_purchase.update', $joint->id), $data)->assertUnprocessable()->assertJsonValidationErrors('benefit_quantity');
        unset($data['benefit_quantity'],$data['benefit_amount']);
        $data['sale_name'] = 'Changed sale';
        $this->post(route('channel.joint_purchase.update', $joint->id), $data)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertEquals('Changed sale', $joint->fresh()->sale_name);
        $this->assertEquals($original, DB::table('joint_purchase_price_tiers')->where('joint_purchase_id', $joint->id)->get()->toJson());
        $this->get(route('channel.joint_purchase.edit', $joint->id))->assertOk()->assertDontSee('id="benefit-add"', false)->assertSee('100개 이상일 경우 개당 1,000원 할인');
    }

    public function test_only_waiting_campaign_without_orders_can_be_removed(): void
    {
        [, $data] = $this->setupForm();
        $this->post(route('channel.joint_purchase.store'), $data);
        $joint = JointPurchase::firstOrFail();
        $joint->update(['status' => 1]);
        $this->postJson(route('channel.joint_purchase.delete', $joint->id))->assertUnprocessable();
        $joint->update(['status' => 2]);
        $this->postJson(route('channel.joint_purchase.delete', $joint->id))->assertUnprocessable();
        $joint->update(['status' => 0]);
        $this->post(route('channel.joint_purchase.delete', $joint->id))->assertRedirect();
        $this->assertSoftDeleted($joint);
        $this->assertNull(app(JointPurchasePricingService::class)->activePurchaseForProduct($joint->product_id));
        $this->get(route('channel.joint_purchase.list'))->assertOk()->assertViewHas('jointPurchases', fn ($rows) => $rows->total() === 0);
    }

    public function test_search_matches_selling_name_code_period_and_states_and_keeps_vendor_scope(): void
    {
        [$admin,$data] = $this->setupForm();
        $this->post(route('channel.joint_purchase.store'), $data);
        $joint = JointPurchase::firstOrFail();
        $this->get('/channel/joint-purchase/list?q=JOINT-130&search_type=code&states[]=0&date_from=2026-10-31&date_to=2026-11-03')->assertOk()->assertViewHas('jointPurchases', fn ($r) => $r->total() === 1);
        $this->get('/channel/joint-purchase/list?q=Review+sale')->assertOk()->assertViewHas('jointPurchases', fn ($r) => $r->total() === 1);
        $this->get('/channel/joint-purchase/list?states[]=1')->assertOk()->assertViewHas('jointPurchases', fn ($r) => $r->total() === 0);
        $admin->vendor_id = 999;
        $admin->save();
        $this->get(route('channel.joint_purchase.list'))->assertOk()->assertViewHas('jointPurchases', fn ($r) => $r->total() === 0);
        $this->get(route('channel.joint_purchase.edit', $joint->id))->assertNotFound();
        $this->postJson(route('channel.joint_purchase.update', $joint->id), $data)->assertNotFound();
        $this->postJson(route('channel.joint_purchase.delete', $joint->id))->assertNotFound();
    }

    public function test_five_listing_images_and_pc_mobile_detail_assets_survive_update(): void
    {
        [, $data] = $this->setupForm();
        $image = fn () => UploadedFile::fake()->image('joint-review.png', 30, 30);
        $data['detail_display_type'] = 'image';
        $data['product_image'] = $image();
        $data['slot_images'] = [$image(), $image(), $image(), $image()];
        $data['detail_pc_image'] = $image();
        $data['detail_mobile_image'] = $image();
        $this->post(route('channel.joint_purchase.store'), $data)->assertRedirect()->assertSessionHasNoErrors();
        $joint = JointPurchase::firstOrFail();
        $product = $joint->product;
        $files = $product->images()->pluck('image')->all();
        $details = [$product->detail_pc_image, $product->detail_mobile_image];
        try {
            $this->assertSame(5, $product->images()->count());
            foreach ($details as $file) {
                $this->assertFileExists(public_path('front/images/product_detail_images/'.$file));
            }
            foreach (['benefit_quantity', 'benefit_amount', 'product_image', 'slot_images', 'detail_pc_image', 'detail_mobile_image'] as $key) {
                unset($data[$key]);
            }
            $data['sale_name'] = 'Images retained';
            $this->post(route('channel.joint_purchase.update', $joint->id), $data)->assertSessionHasNoErrors();
            $this->assertSame(5, $product->images()->count());
            $this->assertEquals($details, [$product->fresh()->detail_pc_image, $product->fresh()->detail_mobile_image]);
            $data['detail_display_type'] = 'text';
            $data['detail_text'] = '<p>Replacement text</p>';
            $this->post(route('channel.joint_purchase.update', $joint->id), $data)->assertSessionHasNoErrors();
            $this->assertNull($product->fresh()->detail_pc_image);
            $this->assertStringContainsString('Replacement text', $product->fresh()->detail_text);
        } finally {
            foreach ($files as $file) {
                foreach (['large', 'medium', 'small'] as $size) {
                    @unlink(public_path('front/images/product_images/'.$size.'/'.$file));
                }
            }
            foreach ($details as $file) {
                @unlink(public_path('front/images/product_detail_images/'.$file));
            }
        }
    }

    public function test_foreign_refund_policy_and_order_manager_are_rejected(): void
    {
        [$admin,$data] = $this->setupForm();
        $other = Vendor::forceCreate(['name' => 'Other vendor', 'email' => 'other-joint-vendor@example.test', 'mobile' => '01000000000', 'status' => 1]);
        $manager = Distributor::forceCreate(['vendor_id' => $other->id, 'name' => 'Other', 'email' => 'other-manager@example.test', 'password' => bcrypt('test'), 'status' => 1]);
        $policy = ShopCancelRefundPolicy::forceCreate(['vendor_id' => $other->id, 'name' => 'Other policy', 'content' => 'Private policy', 'status' => 'active', 'type' => 'custom', 'product_count' => 0]);
        $this->postJson(route('channel.joint_purchase.store'), array_replace($data, ['order_manager_enabled' => 1, 'distributor_id' => $manager->id]))->assertUnprocessable()->assertJsonValidationErrors('distributor_id');
        $this->postJson(route('channel.joint_purchase.store'), array_replace($data, ['cancel_refund_policy_id' => $policy->id]))->assertUnprocessable()->assertJsonValidationErrors('cancel_refund_policy_id');
        $this->assertDatabaseCount('joint_purchases', 0);
    }

    public function test_scheduled_and_expired_campaigns_are_not_active_and_state_filters_match(): void
    {
        [, $data] = $this->setupForm();
        $this->travelTo(Carbon::parse('2026-10-31 10:00:00', 'Asia/Seoul'));
        $data['joint_status'] = 1;
        $data['period_enabled'] = 1;
        $data['start_date'] = '2026-11-01';
        $data['end_date'] = '2026-11-07';
        $this->post(route('channel.joint_purchase.store'), $data)->assertSessionHasNoErrors();
        $joint = JointPurchase::firstOrFail();
        $service = app(JointPurchasePricingService::class);
        $this->assertSame(0, JointPurchase::state($joint));
        $this->assertNull($service->activePurchaseForProduct($joint->product_id));
        $this->get('/channel/joint-purchase/list?states[]=0')->assertViewHas('jointPurchases', fn ($r) => $r->total() === 1);
        $this->travelTo(Carbon::parse('2026-11-03 10:00:00', 'Asia/Seoul'));
        $this->assertSame(1, JointPurchase::state($joint));
        $this->assertNotNull($service->activePurchaseForProduct($joint->product_id));
        $this->travelTo(Carbon::parse('2026-11-08 00:01:00', 'Asia/Seoul'));
        $this->assertSame(2, JointPurchase::state($joint));
        $this->assertNull($service->activePurchaseForProduct($joint->product_id));
        $this->travelBack();
    }

    public function test_upload_failure_rolls_back_campaign_and_discards_new_files(): void
    {
        [, $data] = $this->setupForm();
        $data['detail_display_type'] = 'image';
        $data['detail_pc_image'] = UploadedFile::fake()->image('pc-review.png', 20, 20);
        $data['detail_mobile_image'] = UploadedFile::fake()->image('mobile-review.png', 20, 20);
        $data['product_image'] = UploadedFile::fake()->image('main-review.png', 20, 20);
        $before = glob(public_path('front/images/product_detail_images/detail_*')) ?: [];
        $imageManager = Image::getFacadeRoot();
        Image::shouldReceive('make')->once()->andThrow(new \RuntimeException('Review upload failure'));
        try {
            $this->withoutExceptionHandling();
            try {
                $this->post(route('channel.joint_purchase.store'), $data);
                $this->fail('Expected image storage failure');
            } catch (\RuntimeException $error) {
                $this->assertSame('Review upload failure',$error->getMessage());
            }
            $this->assertDatabaseCount('joint_purchases',0);
            $this->assertDatabaseCount('products',0);
            $this->assertSame($before,glob(public_path('front/images/product_detail_images/detail_*')) ?: []);
        } finally {
            Image::swap($imageManager);
        }
    }
}
