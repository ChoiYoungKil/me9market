<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Vendor;
use App\Models\VendorsBusinessDetail;
use App\Models\VendorsBankDetail;
use App\Models\ShopCancelRefundPolicy;
use App\Models\ShopChannel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChannelPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_channel_portal()
    {
        $response = $this->get('/channel');

        $response->assertRedirect(route('channel.login'));
    }

    public function test_vendor_can_login_via_channel_portal()
    {
        $admin = new Admin;
        $admin->name = 'Test Vendor';
        $admin->type = 'vendor';
        $admin->vendor_id = 1;
        $admin->mobile = '01012345678';
        $admin->email = 'channel-vendor-login@example.com';
        $admin->password = bcrypt('123456');
        $admin->confirm = 'Yes';
        $admin->status = 1;
        $admin->save();

        $response = $this->post('/channel/login', [
            'email' => 'channel-vendor-login@example.com',
            'password' => '123456',
        ]);

        $response->assertRedirect(route('channel.index'));
        $this->assertAuthenticatedAs($admin, 'admin');
    }

    public function test_non_vendor_cannot_login_via_channel_portal()
    {
        $admin = new Admin;
        $admin->name = 'Test Superadmin';
        $admin->type = 'superadmin';
        $admin->vendor_id = 0;
        $admin->mobile = '01012345678';
        $admin->email = 'superadmin@example.com';
        $admin->password = bcrypt('123456');
        $admin->confirm = 'Yes';
        $admin->status = 1;
        $admin->save();

        $response = $this->post('/channel/login', [
            'email' => 'superadmin@example.com',
            'password' => '123456',
        ]);

        $response->assertRedirect(route('channel.login'));
        $this->assertGuest('admin');
    }

    public function test_authenticated_admin_can_access_channel_portal()
    {
        $admin = new Admin;
        $admin->name = 'Test Admin';
        $admin->type = 'vendor';
        $admin->vendor_id = 1;
        $admin->mobile = '01012345678';
        $admin->email = 'admin@example.com';
        $admin->password = bcrypt('password');
        $admin->status = 1;
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get('/channel');

        $response->assertStatus(200);
    }

    public function test_channel_complete_profile_screen_can_be_rendered()
    {
        $admin = new Admin;
        $admin->name = 'Test Admin';
        $admin->type = 'vendor';
        $admin->vendor_id = 1;
        $admin->mobile = '01012345678';
        $admin->email = 'admin@example.com';
        $admin->password = bcrypt('password');
        $admin->status = 1;
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->get('/channel/complete-profile');

        $response->assertStatus(200);
    }

    public function test_channel_complete_profile_submit()
    {
        $vendor = new Vendor;
        $vendor->name = 'Test Vendor';
        $vendor->mobile = '010-1234-5678';
        $vendor->email = 'vendor@example.com';
        $vendor->status = 0;
        $vendor->commission = 0;
        $vendor->confirm = 'No';
        $vendor->save();

        $admin = new Admin;
        $admin->name = 'Test Admin';
        $admin->type = 'vendor';
        $admin->vendor_id = $vendor->id;
        $admin->mobile = '01012345678';
        $admin->email = 'admin@example.com';
        $admin->password = bcrypt('password');
        $admin->status = 1;
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->post('/channel/complete-profile', [
            'shop_name' => 'Complete Shop Name',
            'shop_business_type' => '1',
            'business_license_number' => '123-45-67890',
            'shop_mobile' => '01012345678',
            'bank_name' => 'Kookmin Bank',
            'bank_account_number' => '123-456-789',
            'bank_account_holder_name' => 'Holder Name',
            'accept' => '1',
        ], [
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        $response->assertJson([
            'type' => 'success',
        ]);

        $this->assertDatabaseHas('vendors_business_details', [
            'vendor_id' => $vendor->id,
            'shop_name' => 'Complete Shop Name',
            'business_license_number' => '123-45-67890',
        ]);
    }

    public function test_certified_vendor_can_register_shop_channel_with_own_pg()
    {
        $vendor = new Vendor;
        $vendor->name = 'Certified Vendor';
        $vendor->mobile = '010-1234-5678';
        $vendor->email = 'certified-vendor@example.com';
        $vendor->status = 1;
        $vendor->commission = 0;
        $vendor->confirm = 'Yes';
        $vendor->save();

        $admin = new Admin;
        $admin->name = 'Certified Admin';
        $admin->type = 'vendor';
        $admin->vendor_id = $vendor->id;
        $admin->mobile = '01012345678';
        $admin->email = 'certified-admin@example.com';
        $admin->password = bcrypt('password');
        $admin->status = 1;
        $admin->save();

        $response = $this->actingAs($admin, 'admin')->post(route('channel.shop_register.submit'), [
            'channel_code' => 'Me9-Ver3-PG',
            'status' => 1,
            'is_public' => 1,
            'channel_name' => 'Ver3 PG Shop',
            'copyright' => 'Me9',
            'keywords' => ['ver3', 'pg'],
            'use_period_type' => 0,
            'use_logo' => 0,
            'use_banner' => 0,
            'use_og' => 0,
            'use_own_pg' => 1,
            'pg_provider' => 'toss',
            'pg_merchant_id' => 'merchant-ver3',
            'pg_site_code' => 'site-ver3',
            'pg_client_key' => 'client-ver3',
            'pg_secret_key' => 'secret-ver3',
            'use_admin' => 0,
        ]);

        $response->assertRedirect(route('channel.shop_list'));

        $shop = ShopChannel::where('channel_code', 'Me9-Ver3-PG')->firstOrFail();
        $this->assertTrue((bool) $shop->use_own_pg);
        $this->assertSame('toss', $shop->pg_provider);
        $this->assertSame('merchant-ver3', $shop->pg_merchant_id);
        $this->assertSame('site-ver3', $shop->pg_site_code);
        $this->assertSame('client-ver3', $shop->pg_client_key);
        $this->assertSame('secret-ver3', $shop->pg_secret_key);
    }

    public function test_channel_cancel_refund_policy_crud()
    {
        $vendor = new Vendor;
        $vendor->name = 'Test Vendor';
        $vendor->mobile = '010-1234-5678';
        $vendor->email = 'vendor@example.com';
        $vendor->status = 0;
        $vendor->commission = 0;
        $vendor->confirm = 'No';
        $vendor->save();

        $admin = new Admin;
        $admin->name = 'Test Admin';
        $admin->type = 'vendor';
        $admin->vendor_id = $vendor->id;
        $admin->mobile = '01012345678';
        $admin->email = 'admin@example.com';
        $admin->password = bcrypt('password');
        $admin->status = 1;
        $admin->save();

        // 1. Create (Store)
        $response = $this->actingAs($admin, 'admin')->postJson('/channel/settings/refund/store', [
            'type' => 'custom',
            'status' => 'active',
            'name' => 'Custom Refund Policy',
            'content' => 'This is custom refund policy content.',
        ]);

        $response->assertJson([
            'status' => 'success',
            'message' => '취소/환불안내가 등록되었습니다.',
        ]);

        $policyId = $response->json('policy.id');

        $this->assertDatabaseHas('shop_cancel_refund_policies', [
            'id' => $policyId,
            'vendor_id' => $vendor->id,
            'name' => 'Custom Refund Policy',
        ]);

        // 2. Read (Get)
        $response = $this->actingAs($admin, 'admin')->getJson("/channel/settings/refund/{$policyId}");

        $response->assertJson([
            'status' => 'success',
            'policy' => [
                'id' => $policyId,
                'name' => 'Custom Refund Policy',
            ],
        ]);

        // 3. Update
        $response = $this->actingAs($admin, 'admin')->postJson("/channel/settings/refund/{$policyId}/update", [
            'type' => 'custom',
            'status' => 'inactive',
            'name' => 'Updated Policy Name',
            'content' => 'Updated policy content.',
        ]);

        $response->assertJson([
            'status' => 'success',
            'message' => '취소/환불안내가 수정되었습니다.',
        ]);

        $this->assertDatabaseHas('shop_cancel_refund_policies', [
            'id' => $policyId,
            'name' => 'Updated Policy Name',
            'status' => 'inactive',
        ]);

        // 4. Copy
        $response = $this->actingAs($admin, 'admin')->postJson("/channel/settings/refund/{$policyId}/copy");

        $response->assertJson([
            'status' => 'success',
            'message' => '취소/환불안내가 복사되었습니다.',
        ]);

        $copiedPolicyId = $response->json('policy.id');

        $this->assertDatabaseHas('shop_cancel_refund_policies', [
            'id' => $copiedPolicyId,
            'name' => 'Updated Policy Name (복사본)',
            'status' => 'inactive',
        ]);

        // 5. Delete
        $response = $this->actingAs($admin, 'admin')->postJson("/channel/settings/refund/{$policyId}/delete");

        $response->assertJson([
            'status' => 'success',
            'message' => '취소/환불안내가 삭제되었습니다.',
        ]);

        $this->assertDatabaseMissing('shop_cancel_refund_policies', [
            'id' => $policyId,
        ]);
    }

    public function test_admin_can_approve_shop_channel_closure_request()
    {
        $admin = new Admin;
        $admin->name = 'Super Admin';
        $admin->type = 'superadmin';
        $admin->vendor_id = 0;
        $admin->mobile = '01012345678';
        $admin->email = 'closure-admin@example.com';
        $admin->password = bcrypt('password');
        $admin->confirm = 'Yes';
        $admin->status = 1;
        $admin->save();

        $vendor = Vendor::create([
            'name' => '운영중지 판매자',
            'mobile' => '010-9999-0000',
            'email' => 'closure-vendor@example.com',
            'status' => 1,
            'commission' => 10,
        ]);

        $shop = ShopChannel::create([
            'vendor_id' => $vendor->id,
            'channel_code' => 'close-test-shop',
            'status' => 0,
            'closure_status' => 'requested',
            'closure_requested_at' => now(),
            'is_public' => 1,
            'is_member_only' => 0,
            'channel_name' => '운영중지 요청 채널',
            'copyright' => 'Me9',
            'keywords' => [],
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('admin.shop_channel_closures.approve', $shop->id))
            ->assertRedirect();

        $this->assertDatabaseHas('shop_channels', [
            'id' => $shop->id,
            'status' => 0,
            'closure_status' => 'approved',
            'closure_reviewed_by' => $admin->id,
        ]);
    }
}
