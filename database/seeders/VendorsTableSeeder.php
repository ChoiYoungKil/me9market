<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VendorsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        // Databas Seeding
        // Note: Check DatabaseSeeder.php
        $vendorRecords = [
            [
                'id'      => 1,
                'name'    => 'Yasser Fouaad - Vendor',
                'address' => '17 El-Salam St.',
                'city'    => 'Maadi',
                'state'   => 'Cairo',
                'country' => 'Egypt',
                'pincode' => '110001',
                'mobile'  => '9700000000',
                'email'   => 'yasser@admin.com',
                'status'  => 1,
            ],
        ];

        // Note: Check DatabaseSeeder.php
        foreach ($vendorRecords as $record) {
            $email = $record['email'];
            unset($record['id']);

            \App\Models\Vendor::updateOrCreate(
                ['email' => $email],
                $record
            );
        }
    }
}
