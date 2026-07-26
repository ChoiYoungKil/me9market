<?php

namespace Tests\Feature;

use App\Models\ShopChannel;
use App\Services\ShopChannelRuntime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationalHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_data_is_not_created_when_disabled()
    {
        config(['shop_channel.seed_demo_data' => false]);

        $this->assertNull(app(ShopChannelRuntime::class)->seedDemoDataIfAllowed());
        $this->assertDatabaseCount('shop_channels', 0);
    }

    public function test_demo_credentials_are_hidden_when_disabled()
    {
        config([
            'shop_channel.seed_demo_data' => false,
            'shop_channel.show_demo_credentials' => false,
        ]);

        ShopChannel::create([
            'vendor_id' => 1,
            'channel_code' => 'live-shop',
            'channel_name' => 'Live Shop',
            'copyright' => 'Me9',
            'keywords' => '[]',
            'status' => 1,
        ]);

        $this->get('/admin/login')
            ->assertStatus(200)
            ->assertDontSee('admin@admin.com')
            ->assertDontSee('123456');

        $this->get('/channel/login')
            ->assertStatus(200)
            ->assertDontSee('john@admin.com')
            ->assertDontSee('123456');

        $this->get('/member/login')
            ->assertStatus(200)
            ->assertDontSee('user@user.com')
            ->assertDontSee('123456');

        $this->get('/nonmember/order/check')
            ->assertStatus(200)
            ->assertDontSee('Me9-Shop-0032022')
            ->assertDontSee('010-1234-5678');
    }
}
