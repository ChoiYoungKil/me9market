<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_success(): void
    {
        // 1. 테스트가 seed 데이터에 의존하지 않도록 관리자 계정을 준비
        $admin = Admin::where('email', 'admin@admin.com')->first();
        if (!$admin) {
            $admin = new Admin();
            $admin->name = 'Test Admin';
            $admin->type = 'admin';
            $admin->vendor_id = 0;
            $admin->mobile = '01000000000';
            $admin->email = 'admin@admin.com';
            $admin->password = Hash::make('123456');
            $admin->confirm = 'Yes';
            $admin->status = 1;
            $admin->save();
        }

        $this->assertNotNull($admin, 'Admin account does not exist in DB');
        $this->assertEquals(1, $admin->status, 'Admin account is inactive');

        // 2. /admin/login POST 요청
        $token = 'admin-login-test-token';
        $response = $this->withSession(['_token' => $token])->post('/admin/login', [
            '_token' => $token,
            'email' => 'admin@admin.com',
            'password' => '123456',
        ]);

        // 3. 리다이렉션 검증 (일반적으로 /admin/dashboard 로 리다이렉트됨)
        $response->assertRedirect('/admin/dashboard');

        // 4. 세션 인증 검증
        $this->assertTrue(auth('admin')->check(), 'Admin guard is not authenticated');

        // 5. 대시보드 페이지 접근 검증
        $dashboardResponse = $this->actingAs($admin, 'admin')->get('/admin/dashboard');
        $dashboardResponse->assertStatus(200);
    }
}
