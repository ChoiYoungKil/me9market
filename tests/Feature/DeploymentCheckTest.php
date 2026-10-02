<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class DeploymentCheckTest extends TestCase
{
    use RefreshDatabase;

    private function configureRestrictedRelease(): void
    {
        $this->app->instance('env', 'production');
        config([
            'app.debug' => false,
            'app.url' => 'https://shop.example.com',
            'session.secure' => true,
            'session.http_only' => true,
            'session.driver' => 'file',
            'cache.default' => 'file',
            'queue.default' => 'database',
            'storyboard.enabled' => false,
            'shop_channel.payment_driver' => 'disabled',
            'shop_channel.terms_url' => null,
            'shop_channel.privacy_url' => null,
        ]);
    }

    public function test_restricted_release_check_is_read_only_and_does_not_expose_secrets(): void
    {
        $this->configureRestrictedRelease();
        config(['services.sms.password' => 'must-not-appear-in-output']);
        $this->assertSame(0, Artisan::call('deployment:check', ['--restricted' => true]));
        $output = Artisan::output();
        $this->assertStringContainsString('Restricted release only', $output);
        $this->assertStringNotContainsString('must-not-appear-in-output', $output);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_full_release_cannot_pass_without_live_checkout(): void
    {
        $this->configureRestrictedRelease();
        $this->assertSame(1, Artisan::call('deployment:check'));
        $output = Artisan::output();
        $this->assertStringContainsString('Live checkout implemented and accepted', $output);
        $this->assertStringContainsString('FAIL', $output);
    }

    public function test_insecure_environment_fails_even_for_restricted_release(): void
    {
        $this->configureRestrictedRelease();
        config(['app.debug' => true, 'session.secure' => false, 'shop_channel.payment_driver' => 'mock']);
        $this->assertSame(1, Artisan::call('deployment:check', ['--restricted' => true]));
        $this->assertStringContainsString('FAIL', Artisan::output());
    }
}
