<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorsBankDetailsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        
        // Note: Check DatabaseSeeder.php
        $vendorsBankDetailsRecords = [
            [
                'id'                  => 1,
                'vendor_id'           => 1,
                'account_holder_name' => 'John Cena',
                'bank_name'           => 'ICICI',
                'account_number'      => '021546545454541545454',
                'bank_ifsc_code'      => '36546655',
            ],
        ];
        // Note: Check DatabaseSeeder.php
        foreach ($vendorsBankDetailsRecords as $record) {
            \Illuminate\Support\Facades\DB::table('vendors_bank_details')->updateOrInsert(
                ['id' => $record['id']],
                $record
            );
        }
    }
}
