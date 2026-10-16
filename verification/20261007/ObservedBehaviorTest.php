<?php

namespace Tests\Feature;

use App\Models\PointTransaction;
use App\Models\ChannelDeliveryCharge;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Audit probes assert the observed defects; passing is evidence of reproduction.
class ObservedBehaviorTest extends TestCase
{
    use RefreshDatabase;

    public function test_generic_registration_accepts_missing_production_consent(): void
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

        $response->assertRedirect('/dashboard');
        $user = User::where('email', 'secondary-audit@example.com')->firstOrFail();
        $this->assertNull($user->terms_accepted_at);
        $this->assertNull($user->terms_snapshot);
        $this->assertAuthenticated();
    }

    public function test_point_channel_links_all_resolve_to_current_channel(): void
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
        $this->assertSame($channels[0]['url'], $channels[1]['url']);
        $this->assertSame(route('shop.channel_main'), $channels[0]['url']);
        foreach ($shops as $shop) {
            $this->assertStringNotContainsString($shop->channel_code, $channels[0]['url']);
        }
        $response->assertSee('총 포인트 (0)');
        $this->assertSame(200.0, (float) $response->viewData('channelPoints'));
        $response->assertSee('123-45-67890');
    }

    public function test_member_registration_accepts_false_consent_without_policies(): void
    {
        $this->app->instance('env', 'production');
        config([
            'shop_channel.terms_url' => null,
            'shop_channel.privacy_url' => null,
            'shop_channel.third_party_url' => null,
            'shop_channel.terms_version' => null,
        ]);
        $this->post('/register/member', [
            'agree_terms' => '0', 'agree_privacy' => '0', 'agree_third_party' => '0',
            'register_type' => 'general', 'username' => 'auditfalse',
            'email_prefix' => 'auditfalse', 'email_domain' => 'example.com',
            'password' => 'SecondaryAuditPassword123!',
            'password_confirmation' => 'SecondaryAuditPassword123!',
        ])->assertOk()->assertJson(['status' => 'success']);
        $user = User::where('username', 'auditfalse')->firstOrFail();
        $this->assertNull($user->terms_accepted_at);
        $this->assertNull($user->terms_snapshot);
        $this->assertAuthenticated();
    }

    public function test_saved_quantity_free_shipping_policy_is_not_used_by_totals(): void
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
        $totals = app(\App\Services\ShopOrderTotals::class)->calculate([
            ['id' => 1, 'key' => 'audit-item', 'product' => $product, 'qty' => 2, 'line_total' => 10000],
        ]);
        $this->assertSame(3000.0, $totals['shipping']);
        $this->assertSame(13000.0, $totals['total']);
    }
}
