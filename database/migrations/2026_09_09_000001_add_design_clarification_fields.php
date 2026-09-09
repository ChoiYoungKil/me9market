<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('distributors')) {
            Schema::table('distributors', function (Blueprint $table) {
                if (! Schema::hasColumn('distributors', 'return_postcode')) {
                    $table->string('return_postcode', 20)->nullable()->after('phone');
                }
                if (! Schema::hasColumn('distributors', 'return_address')) {
                    $table->string('return_address')->nullable()->after('return_postcode');
                }
            });
        }
        if (Schema::hasTable('order_claims')) {
            Schema::table('order_claims', function (Blueprint $table) {
                if (! Schema::hasColumn('order_claims', 'pickup_method')) {
                    $table->string('pickup_method', 20)->nullable()->after('detail_reason');
                }
                if (! Schema::hasColumn('order_claims', 'return_address')) {
                    $table->string('return_address')->nullable()->after('pickup_method');
                }
                if (! Schema::hasColumn('order_claims', 'customer_courier_name')) {
                    $table->string('customer_courier_name', 100)->nullable()->after('return_address');
                }
                if (! Schema::hasColumn('order_claims', 'customer_tracking_number')) {
                    $table->string('customer_tracking_number', 100)->nullable()->after('customer_courier_name');
                }
                if (! Schema::hasColumn('order_claims', 'customer_shipped_at')) {
                    $table->timestamp('customer_shipped_at')->nullable()->after('customer_tracking_number');
                }
            });
        }
        if (Schema::hasTable('contacts') && ! Schema::hasColumn('contacts', 'inquiry_category')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->string('inquiry_category', 30)->nullable()->after('company');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'inquiry_category')) {
            Schema::table('contacts', fn (Blueprint $table) => $table->dropColumn('inquiry_category'));
        }
        if (Schema::hasTable('order_claims')) {
            $columns = array_values(array_filter([
                'pickup_method',
                'return_address',
                'customer_courier_name',
                'customer_tracking_number',
                'customer_shipped_at',
            ], fn ($column) => Schema::hasColumn('order_claims', $column)));
            if ($columns) {
                Schema::table('order_claims', fn (Blueprint $table) => $table->dropColumn($columns));
            }
        }
        if (Schema::hasTable('distributors')) {
            $columns = array_values(array_filter(
                ['return_postcode', 'return_address'],
                fn ($column) => Schema::hasColumn('distributors', $column)
            ));
            if ($columns) {
                Schema::table('distributors', fn (Blueprint $table) => $table->dropColumn($columns));
            }
        }
    }
};
