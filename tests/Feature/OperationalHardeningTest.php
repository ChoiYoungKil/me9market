<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ShopChannel;
use App\Models\Vendor;
use Database\Seeders\AdminsTableSeeder;
use Database\Seeders\DefaultAccountsSeeder;
use App\Services\ShopChannelRuntime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Process\Process;
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

    public function test_storyboard_testbed_can_be_disabled_for_production()
    {
        $process = new Process(['php', 'artisan', 'route:list', '--path=storyboard-test'], base_path(), [
            'APP_ENV' => 'production',
        ]);
        $process->run();

        $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
        $this->assertStringContainsString("doesn't have any routes matching", $process->getOutput());
    }

    public function test_default_account_seeders_are_idempotent_and_keep_vendor_link()
    {
        Vendor::create([
            'name' => 'Existing Vendor',
            'mobile' => '01000000000',
            'email' => 'existing@example.com',
            'status' => 1,
            'confirm' => 'Yes',
        ]);

        $this->seed(DefaultAccountsSeeder::class);
        $this->seed(AdminsTableSeeder::class);
        $this->seed(DefaultAccountsSeeder::class);
        $this->seed(AdminsTableSeeder::class);

        $vendor = Vendor::where('email', 'john@admin.com')->first();
        $admin = Admin::where('email', 'john@admin.com')->first();

        $this->assertNotNull($vendor);
        $this->assertNotNull($admin);
        $this->assertEquals($vendor->id, $admin->vendor_id);
        $this->assertEquals(1, Vendor::where('email', 'john@admin.com')->count());
        $this->assertEquals(1, Admin::where('email', 'john@admin.com')->count());
    }

    public function test_blade_views_do_not_contain_empty_hash_links_or_actions()
    {
        $paths = [
            resource_path('views'),
            public_path('channel_assets'),
            public_path('master_assets'),
        ];
        $violations = [];

        foreach ($paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));

            foreach ($files as $file) {
                if (!$file->isFile() || !preg_match('/\.(blade\.php|php|html)$/', $file->getFilename())) {
                    continue;
                }

                $contents = file_get_contents($file->getPathname());
                preg_match_all('/\b(?:href|action)\s*=\s*([\'"])#\1/i', $contents, $matches, PREG_OFFSET_CAPTURE);

                foreach ($matches[0] as $match) {
                    $line = substr_count(substr($contents, 0, $match[1]), "\n") + 1;
                    $relativePath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $violations[] = "{$relativePath}:{$line} {$match[0]}";
                }
            }
        }

        $this->assertSame([], $violations, "Empty hash links/actions remain:\n" . implode("\n", $violations));
    }
}
