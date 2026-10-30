<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('channel_sub_accounts', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->dateTime('started_at')->nullable()->change();
            $table->dateTime('ended_at')->nullable()->change();
            $table->unique(['vendor_id', 'user_id']);
        });
        DB::table('channel_sub_accounts')->orderBy('id')->chunkById(100, function ($accounts) {
            foreach ($accounts as $account) {
                $values = [];
                if ($account->ended_at) {
                    $values['ended_at'] = Carbon::parse($account->ended_at)->endOfDay()->format('Y-m-d H:i:s');
                }
                if ($account->member_no) {
                    $members = DB::table('users')->where('member_number', $account->member_no)->pluck('id');
                    if ($members->count() === 1 && ! DB::table('channel_sub_accounts')->where('vendor_id', $account->vendor_id)->where('user_id', $members[0])->exists()) {
                        $values['user_id'] = $members[0];
                    }
                }
                if ($values) {
                    DB::table('channel_sub_accounts')->where('id', $account->id)->update($values);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('channel_sub_accounts', function (Blueprint $table) {
            $table->dropUnique(['vendor_id', 'user_id']);
            $table->dropConstrainedForeignId('user_id');
            $table->date('started_at')->nullable()->change();
            $table->date('ended_at')->nullable()->change();
        });
    }
};
