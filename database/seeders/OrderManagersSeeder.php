<?php

namespace Database\Seeders;

use App\Models\Distributor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class OrderManagersSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('distributors')) {
            return;
        }

        $accounts = [
            [
                'name' => '주식회사 메인공급처',
                'email' => 'partner@main.com',
                'phone' => '010-2222-3333',
                'password' => '123456',
                'status' => 1,
            ],
        ];

        foreach ($accounts as $account) {
            Distributor::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'phone' => $account['phone'],
                    'password' => Hash::make($account['password']),
                    'status' => $account['status'],
                ]
            );
        }
    }
}
