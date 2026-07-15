<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('settlement_runs')) {
            Schema::table('settlement_runs', function (Blueprint $table) {
                if (!Schema::hasColumn('settlement_runs', 'invoice_sales_amount')) {
                    $table->decimal('invoice_sales_amount', 14, 2)->default(0)->after('sales_profit_amount');
                }
                if (!Schema::hasColumn('settlement_runs', 'invoice_purchase_amount')) {
                    $table->decimal('invoice_purchase_amount', 14, 2)->default(0)->after('invoice_sales_amount');
                }
                if (!Schema::hasColumn('settlement_runs', 'point_deposit_amount')) {
                    $table->decimal('point_deposit_amount', 14, 2)->default(0)->after('invoice_purchase_amount');
                }
                if (!Schema::hasColumn('settlement_runs', 'point_used_amount')) {
                    $table->decimal('point_used_amount', 14, 2)->default(0)->after('point_deposit_amount');
                }
                if (!Schema::hasColumn('settlement_runs', 'payout_amount')) {
                    $table->decimal('payout_amount', 14, 2)->default(0)->after('point_used_amount');
                }
            });
        }

        if (Schema::hasTable('settlement_items')) {
            $this->dropOrderProductUniqueIndex();

            Schema::table('settlement_items', function (Blueprint $table) {
                if (!Schema::hasColumn('settlement_items', 'settlement_role')) {
                    $table->string('settlement_role', 40)->default('seller')->after('order_product_id')->index();
                }
                if (!Schema::hasColumn('settlement_items', 'invoice_sales_amount')) {
                    $table->decimal('invoice_sales_amount', 14, 2)->default(0)->after('sales_profit_amount');
                }
                if (!Schema::hasColumn('settlement_items', 'invoice_purchase_amount')) {
                    $table->decimal('invoice_purchase_amount', 14, 2)->default(0)->after('invoice_sales_amount');
                }
                if (!Schema::hasColumn('settlement_items', 'point_deposit_amount')) {
                    $table->decimal('point_deposit_amount', 14, 2)->default(0)->after('invoice_purchase_amount');
                }
                if (!Schema::hasColumn('settlement_items', 'point_used_amount')) {
                    $table->decimal('point_used_amount', 14, 2)->default(0)->after('point_deposit_amount');
                }
                if (!Schema::hasColumn('settlement_items', 'payout_amount')) {
                    $table->decimal('payout_amount', 14, 2)->default(0)->after('point_used_amount');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('settlement_items')) {
            Schema::table('settlement_items', function (Blueprint $table) {
                foreach (['settlement_role', 'invoice_sales_amount', 'invoice_purchase_amount', 'point_deposit_amount', 'point_used_amount', 'payout_amount'] as $column) {
                    if (Schema::hasColumn('settlement_items', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }

        if (Schema::hasTable('settlement_runs')) {
            Schema::table('settlement_runs', function (Blueprint $table) {
                foreach (['invoice_sales_amount', 'invoice_purchase_amount', 'point_deposit_amount', 'point_used_amount', 'payout_amount'] as $column) {
                    if (Schema::hasColumn('settlement_runs', $column)) {
                        $table->dropColumn($column);
                    }
                }
            });
        }
    }

    private function dropOrderProductUniqueIndex(): void
    {
        if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        $database = DB::getDatabaseName();
        $index = DB::table('information_schema.statistics')
            ->where('table_schema', $database)
            ->where('table_name', 'settlement_items')
            ->where('index_name', 'settlement_items_order_product_id_unique')
            ->exists();

        if ($index) {
            Schema::table('settlement_items', function (Blueprint $table) {
                $table->dropUnique('settlement_items_order_product_id_unique');
                $table->index('order_product_id');
            });
        }
    }
};
