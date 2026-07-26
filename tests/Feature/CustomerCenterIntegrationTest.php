<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Contact;
use App\Models\Faq;
use App\Models\Notice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerCenterIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): Admin
    {
        $admin = new Admin;
        $admin->name = 'Support Admin';
        $admin->type = 'superadmin';
        $admin->vendor_id = 0;
        $admin->mobile = '01011112222';
        $admin->email = 'support-admin@example.com';
        $admin->password = bcrypt('password');
        $admin->confirm = 'Yes';
        $admin->status = 1;
        $admin->save();

        return $admin;
    }

    public function test_customer_center_does_not_create_sample_data_on_front_access()
    {
        $this->get('/notice')->assertStatus(200)->assertSee('등록된 데이터가 없습니다.');
        $this->get('/faq')->assertStatus(200)->assertSee('검색 결과가 없습니다.');

        $this->assertDatabaseCount('notices', 0);
        $this->assertDatabaseCount('faqs', 0);
    }

    public function test_front_notice_uses_admin_notice_data_only()
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->post('/admin/add-edit-notice', [
            'title' => '전체관리자 등록 공지',
            'content' => 'RF-01 고객센터 공지 연동 내용',
            'is_important' => 1,
            'status' => 1,
        ])->assertRedirect('admin/notices');

        Notice::create([
            'title' => '비노출 공지',
            'content' => '사용자에게 보이면 안 되는 내용',
            'status' => 0,
        ]);

        $notice = Notice::where('title', '전체관리자 등록 공지')->firstOrFail();

        $this->get('/notice')
            ->assertStatus(200)
            ->assertSee('전체관리자 등록 공지')
            ->assertDontSee('비노출 공지');

        $this->get("/notice/view/{$notice->id}")
            ->assertStatus(200)
            ->assertSee('RF-01 고객센터 공지 연동 내용');
    }

    public function test_front_faq_uses_admin_faq_data_only()
    {
        $admin = $this->admin();

        $this->actingAs($admin, 'admin')->post('/admin/add-edit-faq', [
            'category' => '주문/배송',
            'question' => '전체관리자 등록 FAQ',
            'answer' => 'RF-01 고객센터 FAQ 연동 답변',
            'order' => 1,
            'status' => 1,
        ])->assertRedirect('admin/faqs');

        Faq::create([
            'category' => '기타',
            'question' => '비노출 FAQ',
            'answer' => '사용자에게 보이면 안 되는 답변',
            'status' => 0,
        ]);

        $this->get('/faq')
            ->assertStatus(200)
            ->assertSee('전체관리자 등록 FAQ')
            ->assertSee('RF-01 고객센터 FAQ 연동 답변')
            ->assertDontSee('비노출 FAQ');
    }

    public function test_front_contact_submission_is_managed_from_admin_contacts()
    {
        $admin = $this->admin();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['captcha_code' => 'ABCDEFGHJK'])
            ->post('/contact', [
                'company_name' => '연동 테스트 회사',
                'manager_name' => '홍길동 팀장',
                'manager_tel_1' => '010',
                'manager_tel_2' => '1234',
                'manager_tel_3' => '5678',
                'manager_email_1' => 'partner',
                'manager_email_2' => 'example.com',
                'message' => '관리자 페이지와 연동되어야 하는 제휴 문의입니다.',
                'captcha' => 'ABCDEFGHJK',
                'agree_terms' => 'on',
                'agree_privacy' => 'on',
            ])
            ->assertRedirect();

        $contact = Contact::where('company', '연동 테스트 회사')->firstOrFail();
        $this->assertSame($user->id, (int) $contact->user_id);
        $this->assertSame('pending', $contact->status);

        $this->actingAs($admin, 'admin')
            ->get('/admin/contacts')
            ->assertStatus(200)
            ->assertSee('연동 테스트 회사')
            ->assertSee('partner@example.com');

        $this->actingAs($admin, 'admin')
            ->get("/admin/view-contact/{$contact->id}")
            ->assertStatus(200)
            ->assertSee('관리자 페이지와 연동되어야 하는 제휴 문의입니다.');

        $this->actingAs($admin, 'admin')
            ->post("/admin/update-contact/{$contact->id}", [
                'status' => 'completed',
                'admin_reply' => '관리자 답변 완료',
            ])
            ->assertRedirect('admin/contacts');

        $this->assertDatabaseHas('contacts', [
            'id' => $contact->id,
            'status' => 'completed',
            'admin_reply' => '관리자 답변 완료',
        ]);
        $this->assertNotNull($contact->fresh()->replied_at);
    }
}
