<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;

class AdminLoginTest extends TestCase
{
    public function test_admin_login_success(): void
    {
        // 1. admin@admin.com 이 존재하고 활성 상태인지 확인
        $admin = Admin::where('email', 'admin@admin.com')->first();
        $this->assertNotNull($admin, 'Admin account does not exist in DB');
        $this->assertEquals(1, $admin->status, 'Admin account is inactive');

        // 2. /admin/login POST 요청
        $response = $this->post('/admin/login', [
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
