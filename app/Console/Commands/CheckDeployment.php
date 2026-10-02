<?php

namespace App\Console\Commands;

use App\Services\ShopChannelRuntime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CheckDeployment extends Command
{
    protected $signature = 'deployment:check {--restricted : Check a release that keeps checkout disabled}';

    protected $description = 'Read-only production configuration and schema checks; never sends messages or changes data';

    public function handle(ShopChannelRuntime $runtime): int
    {
        $checks = [];
        $check = function (string $name, bool $passed) use (&$checks): void {
            $checks[] = [$passed ? 'PASS' : 'FAIL', $name];
        };
        $check('APP_ENV is production', app()->environment('production'));
        $check('APP_DEBUG is false', config('app.debug') === false);
        $check('APP_URL uses HTTPS', filter_var(config('app.url'), FILTER_VALIDATE_URL) !== false
            && parse_url(config('app.url'), PHP_URL_SCHEME) === 'https');
        try {
            app('encrypter');
            $check('Application encryption key is valid', true);
        } catch (\Throwable) {
            $check('Application encryption key is valid', false);
        }
        $check('Secure, HTTP-only session cookie', config('session.secure') === true && config('session.http_only') === true);
        $check('Persistent server-side sessions', in_array(config('session.driver'), ['file', 'database', 'redis', 'memcached', 'dynamodb'], true));
        $check('Persistent cache', ! in_array(config('cache.default'), [null, 'array', 'null'], true));
        $check('Asynchronous queue configured', ! in_array(config('queue.default'), [null, 'sync', 'null'], true));
        $check('Storyboard testbed is disabled and not routed', ! config('storyboard.enabled')
            && app('router')->getRoutes()->getByName('admin.storyboard_testbed') === null);
        $check('Shop payment driver is disabled', config('shop_channel.payment_driver') === 'disabled' && ! $runtime->canCheckout());

        foreach (['storage', 'storage/framework/views', 'storage/framework/sessions', 'storage/framework/cache/data', 'storage/logs', 'bootstrap/cache'] as $directory) {
            $check('Writable '.$directory, is_dir(base_path($directory)) && is_writable(base_path($directory)));
        }
        foreach (['shop/css/runtime.css', 'shop/js/runtime.js', 'shop/js/rating.js', 'shop/images/common/logo2.png', 'master_assets/css/font/NanumGothic-Regular.ttf', 'mypage_assets/css/font/NanumGothic-Bold.ttf', 'build/manifest.json'] as $asset) {
            $check('Readable public/'.$asset, is_file(public_path($asset)) && is_readable(public_path($asset)));
        }
        $check('Public storage points to the configured disk', realpath(public_path('storage')) !== false
            && realpath(public_path('storage')) === realpath(config('filesystems.disks.public.root')));

        try {
            DB::connection()->getPdo();
            $check('Database connection', true);
            $migrator = app('migrator');
            $repository = $migrator->getRepository();
            $pending = ! $repository->repositoryExists() || array_diff(
                array_keys($migrator->getMigrationFiles(database_path('migrations'))),
                $repository->getRan()
            );
            $check('No pending migrations', ! $pending);
            foreach ([
                'users' => ['terms_accepted_at', 'marketing_opt_in', 'terms_snapshot', 'notification_opt_in'],
                'orders' => ['buyer_name', 'buyer_mobile', 'delivery_memo', 'order_confirmed_at'],
                'orders_products' => ['settlement_policy_snapshot', 'payment_gateway_type', 'shipping_amount_snapshot', 'point_usage_snapshot', 'used_point_amount', 'paid_line_total_snapshot', 'financial_reversed_at', 'refund_status', 'refund_cash_amount', 'stock_deducted_qty', 'stock_attribute_id', 'attribute_stock_deducted_qty'],
                'point_transactions' => ['reference_key'],
                'products_attributes' => ['price_adjustment'],
                'distributors' => ['access_started_at', 'access_ended_at'],
            ] as $table => $columns) {
                $check('Required columns in '.$table, Schema::hasColumns($table, $columns));
            }
            if (config('session.driver') === 'database') {
                $check('Session table exists', Schema::connection(config('session.connection'))->hasTable(config('session.table')));
            }
            if (config('queue.default') === 'database') {
                $check('Queue table exists', Schema::connection(config('queue.connections.database.connection'))->hasTable(config('queue.connections.database.table')));
            }
            if (config('cache.default') === 'database') {
                $check('Cache table exists', Schema::connection(config('cache.stores.database.connection'))->hasTable(config('cache.stores.database.table')));
            }
        } catch (\Throwable) {
            // Exception messages can contain credentials or connection strings.
            $check('Database/schema inspection completed', false);
        }

        if (! $this->option('restricted')) {
            $check('Live checkout implemented and accepted', false);
            $check('Published signup policies configured', $runtime->canRegister());
            $check('Live SMS provider configured', config('services.sms.driver') !== 'log'
                && filled(config('services.sms.authorization')) && filled(config('services.sms.sender_id')));
            $check('Live mail transport configured', ! in_array(config('mail.default'), [null, 'log', 'array'], true));
        }

        $this->table(['Result', 'Check'], $checks);
        $this->warn('Read-only checks do not verify external delivery, worker liveness, backups, or live payment acceptance.');
        if ($this->option('restricted')) {
            $this->warn('Restricted release only: checkout stays disabled. Missing policies block signup; log SMS cannot send production OTP.');
        }
        $this->line('Verify the web/PHP-FPM environment separately. Never run the test suite against the production database.');

        return collect($checks)->contains(fn ($row) => $row[0] === 'FAIL') ? self::FAILURE : self::SUCCESS;
    }
}
