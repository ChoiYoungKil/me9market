<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ShopChannel;
use App\Models\User;
use App\Models\Vendor;
use Database\Seeders\DatabaseSeeder;
use App\Services\ShopChannelRuntime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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

    public function test_public_member_login_does_not_create_or_reset_demo_account()
    {
        $this->get('/member/login')->assertOk();
        $this->assertDatabaseMissing('users', ['email' => 'user@user.com']);

        $user = User::factory()->create([
            'email' => 'user@user.com',
            'username' => 'user@user.com',
            'password' => Hash::make('KeepThisPassword123'),
        ]);
        $password = $user->password;

        $this->get('/member/login')->assertOk();
        $this->assertSame($password, $user->fresh()->password);
    }

    public function test_subadmin_cannot_access_sensitive_admin_management()
    {
        $subadmin = new Admin;
        $subadmin->name = 'Restricted Subadmin';
        $subadmin->type = 'subadmin';
        $subadmin->vendor_id = 0;
        $subadmin->mobile = '01000000000';
        $subadmin->email = 'subadmin@example.com';
        $subadmin->password = Hash::make('StrongPassword123');
        $subadmin->confirm = 'Yes';
        $subadmin->status = 1;
        $subadmin->save();

        $this->actingAs($subadmin, 'admin')->get('/admin/admins')->assertForbidden();
        $this->actingAs($subadmin, 'admin')->get('/admin/settlements')->assertForbidden();
        $this->actingAs($subadmin, 'admin')->get('/admin/order-managers')->assertForbidden();
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

    public function test_default_database_seeder_does_not_insert_accounts_or_sample_data()
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseCount('admins', 0);
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('vendors', 0);
        $this->assertDatabaseCount('distributors', 0);
        $this->assertDatabaseCount('products', 0);
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

    public function test_core_channel_actions_do_not_use_current_url_fallbacks()
    {
        $criticalViews = [
            resource_path('views/channel/inc/snb01.blade.php'),
            resource_path('views/channel/inc/snb02.blade.php'),
            resource_path('views/channel/inc/snb03.blade.php'),
            resource_path('views/channel/inc/snb04.blade.php'),
            resource_path('views/channel/sub01/community_view.blade.php'),
            resource_path('views/channel/sub02/product_request.blade.php'),
        ];

        $violations = [];
        foreach ($criticalViews as $view) {
            $contents = file_get_contents($view);
            if (str_contains($contents, 'url()->current()')) {
                $violations[] = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $view);
            }
        }

        $this->assertSame([], $violations, 'Core channel action views still contain current URL fallbacks.');
    }
}
