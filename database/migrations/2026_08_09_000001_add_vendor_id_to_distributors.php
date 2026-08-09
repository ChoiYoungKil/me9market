<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('distributors')) {
            return;
        }

        if (!Schema::hasColumn('distributors', 'vendor_id')) {
            Schema::table('distributors', function (Blueprint $table) {
                $table->unsignedBigInteger('vendor_id')->nullable()->after('id')->index();
            });
        }

        if (
            Schema::hasTable('products')
            && Schema::hasColumn('products', 'distributor_id')
            && Schema::hasColumn('products', 'vendor_id')
        ) {
            DB::table('products')
                ->select('distributor_id', DB::raw('MIN(vendor_id) as vendor_id'))
                ->whereNotNull('distributor_id')
                ->whereNotNull('vendor_id')
                ->groupBy('distributor_id')
                ->havingRaw('COUNT(DISTINCT vendor_id) = 1')
                ->orderBy('distributor_id')
                ->get()
                ->each(function ($row) {
                    DB::table('distributors')
                        ->where('id', $row->distributor_id)
                        ->whereNull('vendor_id')
                        ->update(['vendor_id' => $row->vendor_id]);
                });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('distributors') || !Schema::hasColumn('distributors', 'vendor_id')) {
            return;
        }

        Schema::table('distributors', function (Blueprint $table) {
            $table->dropIndex(['vendor_id']);
            $table->dropColumn('vendor_id');
        });
    }
};
