<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Vendor;
use App\Models\VendorsBusinessDetail;
use App\Models\VendorsBankDetail;
use App\Models\ShopCancelRefundPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChannelPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_channel_portal()
    {
        $response = $this->get('/channel');

        $response->assertRedirect('/admin/login');
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
}
