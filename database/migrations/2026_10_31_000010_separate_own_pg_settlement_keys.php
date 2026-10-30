<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settlement_runs')->where('payment_gateway_type', 'own_pg')->where('settlement_key', 'not like', '%:pg:own_pg')->orderBy('id')->chunkById(100, function ($runs) {
            foreach ($runs as $run) {
                $key = $run->settlement_key.':pg:own_pg';
                if (! DB::table('settlement_runs')->where('settlement_key', $key)->exists()) {
                    DB::table('settlement_runs')->where('id', $run->id)->update(['settlement_key' => $key]);
                }
            }
        });
    }

    public function down(): void
    { /* Preserve unique financial record identities on rollback. */
    }
};
