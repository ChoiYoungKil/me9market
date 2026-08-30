<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ShopChannel;
use App\Models\ShopChannelAccessOtp;
use App\Models\ShopChannelPrivateAccess;
use App\Models\Vendor;
use App\Services\ChannelPointService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopChannelAccessTest extends TestCase
{
    use RefreshDatabase;

    private function createShop(bool $isPublic = true): array
    {
        $vendor = Vendor::create([
            'name' => 'Access Vendor',
            'mobile' => '010-1000-2000',
            'email' => 'access-vendor@example.com',
            'status' => 1,
            'commission' => 0,
            'confirm' => 'Yes',
        ]);
        $admin = new Admin();
        $admin->name = 'Access Admin';
        $admin->type = 'vendor';
        $admin->vendor_id = $vendor->id;
        $admin->mobile = '01010002000';
        $admin->email = 'access-admin@example.com';
        $admin->password = bcrypt('password');
        $admin->status = 1;
        $admin->save();
        $shop = ShopChannel::create([
            'vendor_id' => $vendor->id,
            'channel_code' => $isPublic ? 'public-access' : 'private-access',
            'channel_name' => $isPublic ? 'Public Access' : 'Private Access',
            'copyright' => 'Access',
            'keywords' => [],
            'is_public' => $isPublic ? 1 : 0,
            'settlement_type' => 1,
            'settlement_rate' => 10,
            'status' => 1,
        ]);

        return [$vendor, $admin, $shop];
    }

    public function test_shop_pages_require_an_active_channel_session(): void
    {
        $this->get(route('shop.channel_main'))->assertRedirect(route('shop.gate'));
        $this->get(route('front.shop.cart.index'))->assertRedirect(route('shop.gate'));
    }

    public function test_private_channel_requires_sms_otp_and_failed_attempts_are_persisted(): void
    {
        [, , $shop] = $this->createShop(false);
        $access = ShopChannelPrivateAccess::create([
            'shop_channel_id' => $shop->id,
            'phone' => '010-1234-5678',
            'phone_normalized' => '01012345678',
            'entry_code' => 'legacy-code-is-not-an-otp',
        ]);

        $this->get(route('shop.enter', $shop->channel_code))
            ->assertRedirect(route('shop.gate', ['channel' => $shop->channel_code]));

        $this->postJson(route('shop.otp.request'), [
            'entry_code' => $shop->channel_code,
            'phone' => $access->phone,
        ])->assertOk()->assertJson(['status' => true]);

        $otp = ShopChannelAccessOtp::firstOrFail();
        $this->from(route('shop.gate'))->post(route('shop.gate.submit'), [
            'entry_code' => $shop->channel_code,
            'phone' => $access->phone,
            'otp' => '000000',
        ])->assertRedirect(route('shop.gate'))->assertSessionHasErrors('otp');
        $this->assertSame(1, $otp->fresh()->attempts);

        $this->post(route('shop.gate.submit'), [
            'entry_code' => $shop->channel_code,
            'phone' => $access->phone,
            'otp' => '123456',
        ])->assertRedirect(route('shop.channel_main'));

        $this->assertNotNull($otp->fresh()->verified_at);
        $this->get(route('shop.channel_main'))->assertOk();

        $this->from(route('shop.gate'))->post(route('shop.gate.submit'), [
            'entry_code' => $shop->channel_code,
            'phone' => $access->phone,
            'otp' => '123456',
        ])->assertRedirect(route('shop.gate'))->assertSessionHasErrors('otp');
    }

    public function test_public_channel_registration_creates_user_and_awards_first_visit_once(): void
    {
        [$vendor, $admin, $shop] = $this->createShop();
        $shop->update(['first_visit_points' => 500]);
        $points = app(ChannelPointService::class);
        $purchase = $points->requestPurchase($vendor->id, 10000, 'card', null, $shop->id, $admin->id);
        $points->approve($purchase, $admin->id);

        $this->get(route('shop.enter', $shop->channel_code))->assertRedirect(route('shop.channel_main'));
        $this->post(route('shop.register.submit'), [
            'name' => 'Registered Buyer',
            'email' => 'registered-buyer@example.com',
            'phone' => '010-5555-6666',
            'password' => 'password123',
            'terms_service' => '1',
            'terms_privacy' => '1',
            'marketing_opt_in' => '0',
        ])->assertRedirect(route('shop.channel_main'));

        $this->assertAuthenticated();
        $userId = auth()->id();
        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'email' => 'registered-buyer@example.com',
            'mobile' => '010-5555-6666',
        ]);
        $this->assertDatabaseHas('point_transactions', [
            'user_id' => $userId,
            'shop_channel_id' => $shop->id,
            'type' => ChannelPointService::TYPE_FIRST_VISIT,
            'points' => 500,
        ]);

        $this->get(route('shop.channel_main'))->assertOk();
        $this->get(route('shop.enter', $shop->channel_code))->assertRedirect(route('shop.channel_main'));
        $this->assertDatabaseCount('point_transactions', 1);
        $this->assertSame(9500, $points->balanceForVendor($vendor->id));
    }
}
