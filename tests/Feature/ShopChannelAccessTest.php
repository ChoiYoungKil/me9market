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
        $this->get(route('front.shop.order.confirm'))->assertRedirect(route('shop.gate'));
        $this->get(route('front.shop.cancel.details'))->assertRedirect(route('shop.gate'));
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

    public function test_using_the_latest_otp_does_not_reactivate_an_older_code(): void
    {
        config(['services.sms.driver' => 'log']);
        [, , $shop] = $this->createShop(false);
        $access = ShopChannelPrivateAccess::create([
            'shop_channel_id' => $shop->id, 'phone' => '010-1234-5678',
            'phone_normalized' => '01012345678', 'entry_code' => 'legacy',
        ]);
        $service = app(\App\Services\ShopChannelOtpService::class);
        $old = $service->request($shop->channel_code, $access->phone);
        $old->update(['sent_at' => now()->subMinutes(2)]);
        $latest = $service->request($shop->channel_code, $access->phone);
        $service->verify($shop->channel_code, $access->phone, '123456');
        $this->assertNotNull($latest->fresh()->verified_at);

        $this->from(route('shop.gate'))->post(route('shop.gate.submit'), [
            'entry_code' => $shop->channel_code, 'phone' => $access->phone, 'otp' => '123456',
        ])->assertRedirect(route('shop.gate'))->assertSessionHasErrors('otp');
        $this->assertNull($old->fresh()->verified_at);
    }

    public function test_production_does_not_claim_to_send_otp_using_the_log_driver(): void
    {
        config(['services.sms.driver' => 'log']);
        [, , $shop] = $this->createShop(false);
        ShopChannelPrivateAccess::create([
            'shop_channel_id' => $shop->id, 'phone' => '010-1234-5678',
            'phone_normalized' => '01012345678', 'entry_code' => 'legacy',
        ]);
        $this->app->instance('env', 'production');
        $this->postJson(route('shop.otp.request'), ['entry_code' => $shop->channel_code, 'phone' => '010-1234-5678'])
            ->assertUnprocessable()->assertJsonValidationErrors('phone');
        $this->assertDatabaseCount('shop_channel_access_otps', 0);
    }

    public function test_production_requires_three_approved_documents_and_preserves_consent_version(): void
    {
        [, , $shop] = $this->createShop();
        $this->app->instance('env', 'production');
        $runtime = app(\App\Services\ShopChannelRuntime::class);
        config(['shop_channel.terms_url' => 'https://example.test/terms/v1', 'shop_channel.privacy_url' => 'https://example.test/privacy/v1', 'shop_channel.third_party_url' => null, 'shop_channel.terms_version' => 'v1']);
        $this->assertFalse($runtime->canRegister());
        config(['shop_channel.third_party_url' => 'https://example.test/third-party/v1']);
        $this->assertTrue($runtime->canRegister());
        $payload = ['name' => 'Consent Buyer', 'email' => 'consent@example.com', 'phone' => '01011112222', 'password' => 'password123',
            'terms_service' => 1, 'terms_privacy' => 1, 'terms_third_party' => 1];
        $this->withSession(['shop_channel_id' => $shop->id])->postJson(route('shop.register.submit'), array_merge($payload, ['terms_third_party' => 0]))
            ->assertUnprocessable()->assertJsonValidationErrors('terms_third_party');
        $this->post(route('shop.register.submit'), $payload)->assertRedirect()->assertSessionHasNoErrors();
        $user = \App\Models\User::where('email', $payload['email'])->sole();
        config(['shop_channel.terms_version' => 'v2', 'shop_channel.terms_url' => 'https://example.test/terms/v2']);
        $this->assertSame('v1', $user->terms_snapshot['version']);
        $this->assertSame('https://example.test/terms/v1', $user->terms_snapshot['terms_url']);
        $this->assertTrue($user->terms_snapshot['terms_third_party']);
        $this->assertFalse($user->notification_opt_in);
    }

    public function test_registration_does_not_award_removed_first_visit_points(): void
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
            'terms_third_party' => '1',
            'marketing_opt_in' => '0',
        ])->assertRedirect(route('shop.channel_main'));

        $this->assertAuthenticated();
        $userId = auth()->id();
        $this->assertDatabaseHas('users', [
            'id' => $userId,
            'email' => 'registered-buyer@example.com',
            'mobile' => '010-5555-6666',
            'marketing_opt_in' => 0,
        ]);
        $this->assertNotNull(auth()->user()->terms_accepted_at);
        $this->assertTrue(auth()->user()->terms_snapshot['terms_third_party']);
        $this->assertFalse(auth()->user()->notification_opt_in);
        $this->assertDatabaseMissing('point_transactions', [
            'user_id' => $userId,
            'shop_channel_id' => $shop->id,
            'type' => ChannelPointService::TYPE_FIRST_VISIT,
            'points' => 500,
        ]);

        $this->get(route('shop.channel_main'))->assertOk();
        $this->get(route('shop.enter', $shop->channel_code))->assertRedirect(route('shop.channel_main'));
        $this->assertDatabaseCount('point_transactions', 0);
        $this->assertSame(10000, $points->balanceForVendor($vendor->id));
    }
}
