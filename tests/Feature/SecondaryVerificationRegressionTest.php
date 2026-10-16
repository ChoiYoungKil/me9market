<?php

namespace Tests\Feature;

use App\Models\ChannelDeliveryCharge;
use App\Models\PointTransaction;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ShopOrderTotals;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Regression coverage for the defects documented in the secondary verification.
class SecondaryVerificationRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_generic_registration_blocks_missing_production_consent(): void
    {
        $this->app->instance('env', 'production');
        config([
            'shop_channel.terms_url' => null,
            'shop_channel.privacy_url' => null,
            'shop_channel.third_party_url' => null,
            'shop_channel.terms_version' => null,
        ]);

        $response = $this->post('/register', [
            'name' => 'Secondary audit user',
            'email' => 'secondary-audit@example.com',
            'password' => 'SecondaryAuditPassword123!',
            'password_confirmation' => 'SecondaryAuditPassword123!',
        ]);

        $response->assertSessionHasErrors('terms_service');
        $this->assertDatabaseMissing('users', ['email' => 'secondary-audit@example.com']);
        $this->assertGuest();
    }

    public function test_point_links_and_qr_identify_channels_and_header_uses_ledger(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::create([
            'name' => 'Audit vendor', 'email' => 'audit-vendor@example.com',
            'mobile' => '01011112222', 'status' => 1,
        ]);
        $shops = [];
        foreach (['AUDIT001', 'AUDIT002'] as $code) {
            $shop = ShopChannel::create([
                'vendor_id' => $vendor->id, 'channel_code' => $code,
                'channel_name' => $code, 'status' => 1, 'is_public' => 1,
                'copyright' => 'Audit copyright', 'keywords' => [],
            ]);
            PointTransaction::create([
                'user_id' => $user->id, 'shop_channel_id' => $shop->id,
                'points' => 100, 'type' => 'earn', 'description' => 'Audit points',
            ]);
            $shops[] = $shop;
        }

        $response = $this->actingAs($user)->get('/mypage/points/status');
        $response->assertOk();
        $channels = $response->viewData('shopChannels');
        $this->assertCount(2, $channels);
        $this->assertNotSame($channels[0]['url'], $channels[1]['url']);
        foreach ($channels as $channel) {
            $this->assertStringContainsString($channel['info'], $channel['url']);
            $this->assertStringContainsString(urlencode($channel['url']), $channel['qr']);
        }
        $response->assertSee('총 포인트 (200)')->assertDontSee('123-45-67890')->assertDontSee('A채널명');
        $this->assertSame(200.0, (float) $response->viewData('channelPoints'));
    }

    public function test_member_registration_rejects_false_consent_without_policies(): void
    {
        $this->app->instance('env', 'production');
        config([
            'shop_channel.terms_url' => null,
            'shop_channel.privacy_url' => null,
            'shop_channel.third_party_url' => null,
            'shop_channel.terms_version' => null,
        ]);
        $this->postJson('/register/member', [
            'agree_terms' => '0', 'agree_privacy' => '0', 'agree_third_party' => '0',
            'register_type' => 'general', 'username' => 'auditfalse',
            'email_prefix' => 'auditfalse', 'email_domain' => 'example.com',
            'password' => 'SecondaryAuditPassword123!',
            'password_confirmation' => 'SecondaryAuditPassword123!',
        ])->assertUnprocessable()->assertJsonValidationErrors(['agree_terms', 'agree_privacy', 'agree_third_party']);
        $this->assertDatabaseMissing('users', ['username' => 'auditfalse']);
        $this->assertGuest();
    }

    public function test_saved_quantity_free_shipping_policy_is_used_by_totals(): void
    {
        $vendor = Vendor::create([
            'name' => 'Audit shipping vendor', 'email' => 'audit-shipping@example.com',
            'mobile' => '01011113333', 'status' => 1,
        ]);
        ChannelDeliveryCharge::create([
            'vendor_id' => $vendor->id, 'status' => 1, 'name' => 'Audit quantity free shipping',
            'shipping_type' => 'conditional', 'payment_type' => 'prepaid',
            'base_fee' => 3000, 'free_order_quantity' => 2,
        ]);
        $product = new Product([
            'vendor_id' => $vendor->id, 'shipping_policy_type' => 'free_conditional',
            'shipping_policy_name' => 'Audit quantity free shipping',
            'shipping_payment_type' => 'prepaid', 'shipping_base_fee' => 3000,
            'shipping_free_threshold' => 30000,
        ]);
        $totals = app(ShopOrderTotals::class)->calculate([
            ['id' => 1, 'key' => 'audit-item', 'product' => $product, 'qty' => 2, 'line_total' => 10000],
        ]);
        $this->assertSame(0.0, $totals['shipping']);
        $this->assertSame(10000.0, $totals['total']);
    }

    public function test_both_signup_paths_require_policies_and_store_consent_when_ready(): void
    {
        $this->app->instance('env', 'production');
        $base = ['name' => 'Policy user', 'email' => 'policy@example.com', 'password' => 'SecondaryPassword123!', 'password_confirmation' => 'SecondaryPassword123!'];
        $member = $base + ['register_type' => 'general', 'username' => 'policyuser', 'agree_terms' => 1, 'agree_privacy' => 1, 'agree_third_party' => 1];
        config(['shop_channel.terms_url' => null]);
        $this->postJson('/register/member', $member)->assertUnprocessable();
        $this->assertDatabaseCount('users', 0);
        config(['shop_channel.terms_url' => 'https://example.test/terms', 'shop_channel.privacy_url' => 'https://example.test/privacy',
            'shop_channel.third_party_url' => 'https://example.test/third', 'shop_channel.terms_version' => '20261016']);
        $this->postJson('/register/member', $member)->assertOk();
        $user = User::where('email', $base['email'])->firstOrFail();
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertSame('20261016', $user->terms_snapshot['version']);
        auth()->logout();
        $base['email'] = 'generic@example.com';
        $this->postJson('/register', $base)->assertUnprocessable()->assertJsonValidationErrors(['terms_service', 'terms_privacy', 'terms_third_party']);
        $this->post('/register', $base + ['terms_service' => 1, 'terms_privacy' => 1, 'terms_third_party' => 1])->assertRedirect('/dashboard');
        $generic = User::where('email', $base['email'])->firstOrFail();
        $this->assertNotNull($generic->terms_accepted_at);
        $this->assertSame('https://example.test/terms', $generic->terms_snapshot['terms_url']);
    }
}
