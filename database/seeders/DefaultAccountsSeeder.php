<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Admin;
use App\Models\Distributor;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;

class DefaultAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. 최고 관리자 (Superadmin)
        // 기존에 admin@admin.com이 있는지 확인 후 생성/업데이트
        $admin = Admin::where('email', 'admin@admin.com')->first();
        if (!$admin) {
            Admin::create([
                'name'      => 'System Administrator',
                'type'      => 'superadmin',
                'vendor_id' => 0,
                'mobile'    => '01000000000',
                'email'     => 'admin@admin.com',
                'password'  => Hash::make('123456'), // bcrypt 패스워드 설정
                'image'     => '',
                'confirm'   => 'Yes',
                'status'    => 1,
            ]);
        } else {
            $admin->update([
                'type' => 'superadmin',
                'vendor_id' => 0,
                'password' => Hash::make('123456'),
                'confirm'   => 'Yes',
                'status'   => 1,
            ]);
        }

        // 2. 판매자 벤더 정보 생성
        $vendor = Vendor::updateOrCreate(
            ['email' => 'john@admin.com'],
            [
                'name'    => 'Default Shop Vendor',
                'address' => 'Seoul, Korea',
                'city'    => 'Seoul',
                'state'   => 'Seoul',
                'country' => 'Korea',
                'pincode' => '12345',
                'mobile'  => '01011112222',
                'confirm' => 'Yes',
                'status'  => 1,
            ]
        );

        // 3. 채널관리자 (Vendor Admin)
        // 기존에 john@admin.com이 있는지 확인 후 생성/업데이트
        $vendorAdmin = Admin::where('email', 'john@admin.com')->first();
        if (!$vendorAdmin) {
            Admin::create([
                'name'      => 'John Singh - Vendor',
                'type'      => 'vendor',
                'vendor_id' => $vendor->id,
                'mobile'    => '01011112222',
                'email'     => 'john@admin.com',
                'password'  => Hash::make('123456'),
                'image'     => '',
                'confirm'   => 'Yes',
                'status'    => 1,
            ]);
        } else {
            $vendorAdmin->update([
                'password' => Hash::make('123456'),
                'confirm'   => 'Yes',
                'status'   => 1,
                'vendor_id' => $vendor->id,
            ]);
        }

        // 4. 일반 사용자 (User / Member)
        // 기존에 user@user.com이 있는지 확인 후 생성/업데이트
        $user = User::where('email', 'user@user.com')->first();
        if (!$user) {
            User::create([
                'name'     => '일반사용자',
                'username' => 'user@user.com',
                'email'    => 'user@user.com',
                'password' => Hash::make('123456'),
                'mobile'   => '01033334444',
                'address'  => 'Seoul, Korea',
                'city'     => 'Seoul',
                'state'    => 'Seoul',
                'country'  => 'Korea',
                'pincode'  => '12345',
                'status'   => 1,
            ]);
        } else {
            $user->update([
                'username' => 'user@user.com',
                'password' => Hash::make('123456'),
                'status'   => 1,
            ]);
        }

        // 5. 발주사 기본 계정
        foreach (['partner@main.com', 'distributor@me9.com'] as $email) {
            $distributor = Distributor::where('email', $email)->first();
            if (!$distributor) {
                Distributor::create([
                    'name' => $email === 'partner@main.com' ? '메인 발주사' : '기본 발주사',
                    'email' => $email,
                    'password' => Hash::make('123456'),
                    'phone' => '01044445555',
                    'status' => 1,
                ]);
            } else {
                $distributor->update([
                    'name' => $distributor->name ?: ($email === 'partner@main.com' ? '메인 발주사' : '기본 발주사'),
                    'password' => Hash::make('123456'),
                    'phone' => $distributor->phone ?: '01044445555',
                    'status' => 1,
                ]);
            }
        }
    }
}
