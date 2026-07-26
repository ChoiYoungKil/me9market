<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderItemStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        // Note: Check DatabaseSeeder.php!
        $orderItemStatusRecords = [
            [
                'id'     => 1,
                'name'   => 'Pending',
                'status' => 1
            ],
            [
                'id'     => 2,
                'name'   => 'In Progress',
                'status' => 1
            ],
            [
                'id'     => 3,
                'name'   => 'Shipped',
                'status' => 1
            ],
            [
                'id'     => 4,
                'name'   => 'Delivered',
                'status' => 1
            ]
        ];

        // Note: Check DatabaseSeeder.php!
        foreach ($orderItemStatusRecords as $record) {
            \Illuminate\Support\Facades\DB::table('order_item_statuses')->updateOrInsert(
                ['id' => $record['id']],
                $record
            );
        }
    }
}
