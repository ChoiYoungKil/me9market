<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SocialAccount;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorsBusinessDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SellerApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();
    }

    private function admin(string $type = 'admin', int $vendorId = 0): Admin
    {
        $admin = new Admin;
        $admin->name = 'Approval Reviewer'; $admin->email = uniqid('reviewer').'@example.test';
        $admin->type = $type; $admin->vendor_id = $vendorId; $admin->status = 1;
        $admin->mobile = '01000002222'; $admin->password = Hash::make('ReviewAdmin30!');
        $admin->confirm = 'Yes'; $admin->save();
        return $admin;
    }

    private function applicant(): User
    {
        $member = User::factory()->create(['type'=>'general','status'=>1,'mobile'=>'010-0000-2222','profile_completed_at'=>now()]);
        $this->actingAs($member)->postJson('/register/step2/update', [
            'shop_name'=>'검수 회원사','shop_business_type'=>'개인사업자','business_license_1'=>'000','business_license_2'=>'00','business_license_3'=>'00000',
            'mobile_1'=>'010','mobile_2'=>'0000','mobile_3'=>'2222','email_1'=>'review','email_2'=>'example.test',
            'zipcode'=>'04524','address1'=>'서울 중구 세종대로 110','address2'=>'검수용','bank_name'=>'국민은행','account_number'=>'00000000000000',
            'account_holder_name'=>'검수용','agree1'=>1,'bank_copy_image'=>UploadedFile::fake()->image('bank.png'),
        ])->assertOk();
        $this->postJson('/register/step3/update', ['action'=>'request','agree1'=>1,'bank_name'=>'국민은행','account_number'=>'00000000000000',
            'account_holder_name'=>'검수용','address_proof_image'=>UploadedFile::fake()->image('license.png')])->assertOk();
        return $member->fresh();
    }

    public function test_platform_admin_approves_new_member_without_vendor_admin_and_member_sees_certification(): void
    {
        $member = $this->applicant(); $admin = $this->admin();
        SocialAccount::create(['user_id'=>$member->id,'provider'=>'google','provider_user_id'=>'review-social-identity']);
        $beforeUser = $member->getAttributes();
        $beforeBusiness = VendorsBusinessDetail::where('vendor_id',$member->vendor_id)->first()->getAttributes();
        $this->assertSame(0, Admin::where('vendor_id',$member->vendor_id)->count());
        $this->actingAs($admin,'admin')->get(route('admin.users'))->assertOk()->assertSee('판매자 승인 검토');
        $this->get(route('admin.users.seller_review',$member->id))->assertOk()->assertSee('심사 대기')->assertSee('사업자등록증 내려받기');
        $this->post(route('admin.users.seller_approval',$member->id),['review_confirmed'=>1])->assertRedirect(route('admin.users.seller_review',$member->id));
        $vendor = Vendor::find($member->vendor_id);
        $this->assertSame(1,(int)$vendor->status); $this->assertSame('Yes',$vendor->confirm);
        $this->assertSame($admin->id,(int)$vendor->seller_approved_by); $this->assertNotNull($vendor->seller_approved_at);
        $this->assertSame($beforeBusiness,VendorsBusinessDetail::where('vendor_id',$member->vendor_id)->first()->getAttributes());
        foreach (['email','password','username','mobile','vendor_id'] as $field) { $this->assertSame($beforeUser[$field],$member->fresh()->$field); }
        $this->assertDatabaseCount('social_accounts',1);
        $this->actingAs($member,'web')->get('/register/step3')->assertOk()->assertSee('인증완료')->assertSee('관리자 승인이 완료되었습니다.');
        $this->get('/register/complete')->assertOk()->assertSee('인증완료')->assertDontSee('심사 대기');
        Mail::assertNothingSent();
    }

    public function test_missing_confirmation_request_or_document_cannot_be_approved(): void
    {
        $member=$this->applicant(); $admin=$this->admin(); $this->actingAs($admin,'admin');
        $url=route('admin.users.seller_approval',$member->id);
        $this->postJson($url,[])->assertUnprocessable()->assertJsonValidationErrors('review_confirmed');
        $this->postJson('/admin/add-edit-user/'.$member->id, [
            'name'=>$member->name,'email'=>$member->email,'username'=>'approval-review-member',
            'mobile_1'=>'010','mobile_2'=>'0000','mobile_3'=>'2222','seller_status'=>1,
        ])->assertUnprocessable()->assertJsonValidationErrors('seller_status');
        $vendor=Vendor::find($member->vendor_id); $requested=$vendor->seller_requested_at;
        $vendor->seller_requested_at=null; $vendor->save();
        $this->postJson($url,['review_confirmed'=>1])->assertUnprocessable()->assertJsonValidationErrors('seller');
        $vendor->seller_requested_at=$requested; $vendor->save();
        Storage::disk('local')->delete(VendorsBusinessDetail::where('vendor_id',$vendor->id)->value('address_proof_image'));
        $this->postJson($url,['review_confirmed'=>1])->assertUnprocessable()->assertJsonValidationErrors('seller');
        $this->assertSame(0,(int)$vendor->fresh()->status); $this->assertNull($vendor->fresh()->seller_approved_at);
    }

    public function test_member_and_vendor_admin_cannot_approve_themselves(): void
    {
        $member=$this->applicant();
        $this->get(route('admin.users.seller_review',$member->id))->assertRedirect('/admin/login');
        $this->post(route('admin.users.seller_approval',$member->id),['review_confirmed'=>1])->assertRedirect('/admin/login');
        $vendorAdmin=$this->admin('vendor',$member->vendor_id);
        $this->actingAs($vendorAdmin,'admin')->get(route('admin.users.seller_review',$member->id))->assertForbidden();
        $this->post(route('admin.users.seller_approval',$member->id),['review_confirmed'=>1])->assertForbidden();
        $this->assertSame(0,(int)Vendor::find($member->vendor_id)->status);
    }

    public function test_duplicate_approval_preserves_original_audit_and_does_not_enable_subadmin(): void
    {
        $member=$this->applicant(); $first=$this->admin('superadmin'); $second=$this->admin();
        $vendorAdmin=$this->admin('vendor',$member->vendor_id); $vendorAdmin->status=0; $vendorAdmin->confirm='No'; $vendorAdmin->save();
        $subadmin=$this->admin('subadmin',$member->vendor_id); $subadmin->status=0; $subadmin->save();
        $url=route('admin.users.seller_approval',$member->id);
        $this->actingAs($first,'admin')->post($url,['review_confirmed'=>1])->assertRedirect();
        $approvedAt=Vendor::find($member->vendor_id)->seller_approved_at->toISOString();
        $this->travel(1)->hours();
        $this->actingAs($second,'admin')->post($url,['review_confirmed'=>1])->assertRedirect();
        $vendor=Vendor::find($member->vendor_id);
        $this->assertSame($first->id,(int)$vendor->seller_approved_by);
        $this->assertSame($approvedAt,$vendor->seller_approved_at->toISOString());
        $this->assertSame(1,(int)$vendorAdmin->fresh()->status);
        $this->assertSame(0,(int)$subadmin->fresh()->status);
    }
}
