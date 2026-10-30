<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Distributor;
use App\Models\Product;
use App\Models\Vendor;
use App\Services\ChannelOrderManagers;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChannelOrderManagerTest extends TestCase
{
    use RefreshDatabase;

    private function owner(string $key = 'one'): Admin
    {
        $vendor = Vendor::forceCreate(['name' => 'QA '.$key, 'email' => $key.'@vendor.test', 'mobile' => '01000000000', 'status' => 1]);

        return Admin::forceCreate(['name' => 'Owner '.$key, 'type' => 'vendor', 'vendor_id' => $vendor->id, 'email' => $key.'@admin.test', 'mobile' => '01000000000', 'password' => Hash::make('OwnerOnly1234'), 'status' => 1, 'confirm' => 'Yes']);
    }

    private function manager(Admin $owner, string $email = 'assigned@example.test'): Distributor
    {
        return Distributor::create(['vendor_id' => $owner->vendor_id, 'email' => $email, 'name' => 'QA manager', 'password' => Hash::make('ManagerOnly1234'), 'status' => 1, 'access_started_at' => now()->subHour(), 'access_ended_at' => now()->addHour()]);
    }

    private function payload(Admin $owner, string $email = 'new@example.test', ?Distributor $manager = null, array $extra = []): array
    {
        return array_replace(['email' => $email, 'email_check_token' => app(ChannelOrderManagers::class)->emailProof($owner, $email, $manager), 'status' => 1, 'name' => 'QA184 manager', 'access_started_at' => now()->subHour()->format('Y-m-d\TH:i'), 'access_ended_at' => now()->addHour()->format('Y-m-d\TH:i')], $extra);
    }

    public function test_email_duplicate_check_is_required_and_cannot_be_reused_after_email_change(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner, 'admin');
        $this->postJson('/channel/settings/order-manager/store', $this->payload($owner, 'new@example.test', null, ['email_check_token' => null]))->assertUnprocessable()->assertJsonValidationErrors('email_check_token');
        $result = $this->postJson('/channel/settings/order-manager/check-email', ['email' => 'NEW@example.test'])->assertOk()->assertJsonPath('available', true)->json();
        $this->postJson('/channel/settings/order-manager/store', $this->payload($owner, 'different@example.test', null, ['email_check_token' => $result['token']]))->assertUnprocessable();
        $this->post('/channel/settings/order-manager/store', $this->payload($owner, 'new@example.test', null, ['email_check_token' => $result['token'], 'dispatch_postcode' => '04524', 'dispatch_address' => '서울특별시 중구 세종대로 110', 'dispatch_address_detail' => '검수센터', 'memo' => 'QA184 memo']))->assertRedirect();
        $this->assertDatabaseHas('distributors', ['vendor_id' => $owner->vendor_id, 'email' => 'new@example.test', 'dispatch_postcode' => '04524', 'dispatch_address_detail' => '검수센터', 'memo' => 'QA184 memo']);
        $this->postJson('/channel/settings/order-manager/check-email', ['email' => 'NEW@example.test'])->assertOk()->assertJsonPath('available', false);
        $this->assertDatabaseCount('distributors', 1);
    }

    public function test_unchanged_email_does_not_need_recheck_but_changed_email_does(): void
    {
        $owner = $this->owner();
        $manager = $this->manager($owner);
        $password = $manager->password;
        $this->actingAs($owner, 'admin');
        $url = '/channel/settings/order-manager/'.$manager->id.'/update';
        $this->post($url, $this->payload($owner, $manager->email, $manager, ['email_check_token' => null, 'name' => 'Changed name', 'memo' => 'Changed memo']))->assertRedirect();
        $this->assertSame($password, $manager->fresh()->password);
        $this->postJson($url, $this->payload($owner, 'changed@example.test', $manager, ['email_check_token' => null]))->assertUnprocessable()->assertJsonValidationErrors('email_check_token');
        $this->post($url, $this->payload($owner, 'changed@example.test', $manager))->assertRedirect();
        $this->assertSame('changed@example.test', $manager->fresh()->email);
    }

    public function test_duplicate_email_and_foreign_vendor_records_are_blocked(): void
    {
        $owner = $this->owner();
        $other = $this->owner('other');
        $manager = $this->manager($other);
        $this->actingAs($owner, 'admin');
        $this->postJson('/channel/settings/order-manager/check-email', ['email' => $manager->email])->assertOk()->assertJsonPath('available', false);
        $this->postJson('/channel/settings/order-manager/store', $this->payload($owner, $manager->email))->assertUnprocessable();
        $this->postJson('/channel/settings/order-manager/check-email', ['email' => 'new@example.test', 'manager_id' => $manager->id])->assertNotFound();
        $this->get('/channel/settings/order-manager/'.$manager->id)->assertNotFound();
        $this->post('/channel/settings/order-manager/'.$manager->id.'/update', [])->assertNotFound();
        $this->post('/channel/settings/order-manager/'.$manager->id.'/portal')->assertNotFound();
        $this->get('/channel/settings/order-manager')->assertOk()->assertDontSee($manager->email);
    }

    public function test_check_proof_is_bound_to_the_owner_manager_and_expiry(): void
    {
        $owner = $this->owner();
        $other = $this->owner('other');
        $manager = $this->manager($owner);
        $payload = $this->payload($owner);
        $this->actingAs($other, 'admin')->postJson('/channel/settings/order-manager/store', $payload)->assertUnprocessable();
        $this->actingAs($owner, 'admin')->postJson('/channel/settings/order-manager/'.$manager->id.'/update', $payload)->assertUnprocessable();
        $this->travel(31)->minutes();
        $this->postJson('/channel/settings/order-manager/store', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('distributors', 1);
    }

    public function test_email_name_and_period_validation_and_invalid_types_redisplay_safely(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner, 'admin');
        foreach ([['name' => ''], ['email' => 'not-email'], ['access_ended_at' => now()->subHours(2)->toDateTimeString()], ['access_started_at' => ['invalid']], ['email_check_token' => ['invalid']]] as $invalid) {
            $this->postJson('/channel/settings/order-manager/store', $this->payload($owner, 'new@example.test', null, $invalid))->assertUnprocessable();
        }
        $this->from('/channel/settings/order-manager')->post('/channel/settings/order-manager/store', $this->payload($owner, 'new@example.test', null, ['form_key' => ['invalid'], 'email_local' => ['bad'], 'email_domain' => 'example.test']))->assertSessionHasErrors();
        $this->get('/channel/settings/order-manager')->assertOk();
        $this->assertDatabaseCount('distributors', 0);
    }

    public function test_detail_contains_dispatch_memo_real_period_and_only_own_enabled_products(): void
    {
        $owner = $this->owner();
        $other = $this->owner('other');
        $manager = $this->manager($owner);
        $manager->update(['dispatch_postcode' => '04524', 'dispatch_address' => '서울시 검수센터', 'dispatch_address_detail' => '2층', 'memo' => '<script>bad()</script>']);
        $category = Category::forceCreate(['section_id' => 1, 'parent_id' => 0, 'category_name' => 'QA 대분류', 'category_image' => '', 'url' => 'qa185']);
        $base = ['section_id' => 1, 'category_id' => $category->id, 'brand_id' => 1, 'vendor_id' => $owner->vendor_id, 'admin_id' => $owner->id, 'admin_type' => 'vendor', 'product_name' => 'Assigned product', 'product_code' => 'QA185', 'product_color' => 'Blue', 'product_price' => 10000, 'product_discount' => 0, 'product_weight' => 500, 'status' => 1, 'distributor_id' => $manager->id, 'order_manager_enabled' => true];
        Product::forceCreate($base);
        Product::forceCreate(array_replace($base, ['product_name' => 'Disabled assignment', 'product_code' => 'QA185-disabled', 'order_manager_enabled' => false]));
        Product::forceCreate(array_replace($base, ['product_name' => 'Foreign product', 'product_code' => 'QA185-foreign', 'vendor_id' => $other->vendor_id]));
        $this->actingAs($owner, 'admin')->get('/channel/settings/order-manager/'.$manager->id)->assertOk()->assertSee('서울시 검수센터 2층')->assertSee('04524')->assertSee('QA 대분류')->assertSee('Assigned product')->assertSee('발주상품정보 (1)')->assertDontSee('Foreign product')->assertDontSee('Disabled assignment')->assertDontSee('<script>bad()</script>', false);
        $this->get('/channel/settings/order-manager')->assertViewHas('managers', fn ($rows) => (int) $rows->first()->products_count === 1);
    }

    public function test_distributor_logout_does_not_log_the_channel_owner_out(): void
    {
        $owner = $this->owner();
        $manager = $this->manager($owner);
        $this->actingAs($owner, 'admin')->post('/channel/settings/order-manager/'.$manager->id.'/portal')->assertRedirect();
        $this->get('/distributor/logout')->assertRedirect(route('distributor.login'));
        $this->assertNull(session('distributor_id'));
        $this->assertAuthenticatedAs($owner, 'admin');
        $this->get('/channel/settings/order-manager')->assertOk();
    }

    public function test_inactive_or_expired_manager_cannot_open_or_keep_using_a_portal_session(): void
    {
        $owner = $this->owner();
        $manager = $this->manager($owner);
        $this->actingAs($owner, 'admin')->post('/channel/settings/order-manager/'.$manager->id.'/portal')->assertRedirect(route('distributor.orders.pending'));
        $manager->update(['access_ended_at' => now()->subSecond()]);
        $this->get('/distributor/orders/pending')->assertRedirect(route('distributor.login'));
        $this->assertNull(session('distributor_id'));
        $this->post('/channel/settings/order-manager/'.$manager->id.'/portal')->assertForbidden();
        $manager->update(['access_ended_at' => now()->addHour(), 'status' => 0]);
        $this->post('/channel/settings/order-manager/'.$manager->id.'/portal')->assertForbidden();
    }
}
