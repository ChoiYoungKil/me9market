<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorsBusinessDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MemberRegistrationWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    private function profile(): array
    {
        return ['name'=>'검수 회원', 'gender'=>'Male', 'birth_date'=>'1990-01-01', 'mobile_1'=>'010', 'mobile_2'=>'0000', 'mobile_3'=>'2222',
            'zipcode'=>'04524', 'address1'=>'서울특별시 중구 세종대로 110', 'address2'=>'검수용',
            'agree_terms'=>1, 'agree_privacy'=>1, 'agree_third_party'=>1];
    }

    private function company(): array
    {
        return ['shop_name'=>'검수 회원사', 'shop_business_type'=>'개인사업자', 'business_license_1'=>'000', 'business_license_2'=>'00', 'business_license_3'=>'00000',
            'mobile_1'=>'010', 'mobile_2'=>'0000', 'mobile_3'=>'2222', 'email_1'=>'review', 'email_2'=>'example.test',
            'zipcode'=>'04524', 'address1'=>'서울특별시 중구 세종대로 110', 'address2'=>'검수용',
            'bank_name'=>'국민은행', 'account_number'=>'00000000000000', 'account_holder_name'=>'검수용', 'agree1'=>1,
            'bank_copy_image'=>UploadedFile::fake()->image('bank-test.png')];
    }

    private function seller(): array
    {
        return ['action'=>'save', 'agree1'=>1, 'bank_name'=>'국민은행', 'account_number'=>'00000000000000', 'account_holder_name'=>'검수용'];
    }

    private function memberWithCompany(): User
    {
        $user = User::factory()->create(['type'=>'general', 'profile_completed_at'=>now()]);
        $this->actingAs($user)->postJson('/register/step2/update', $this->company())->assertOk();
        return $user->fresh();
    }

    public function test_profile_requires_terms_and_uses_server_member_number_and_identity(): void
    {
        $member = User::factory()->create();
        $other = User::factory()->create();
        $payload = $this->profile();
        unset($payload['agree_privacy']);
        $this->actingAs($member)->postJson('/register/step1/update', $payload)->assertUnprocessable()->assertJsonValidationErrors('agree_privacy');
        $this->assertNull($member->fresh()->profile_completed_at);
        $this->postJson('/register/step1/update', $this->profile()+['user_id'=>$other->id, 'member_number'=>'FORGED'])
            ->assertOk()->assertJson(['status'=>'success']);
        $this->assertNotNull($member->fresh()->profile_completed_at);
        $this->assertNotSame('FORGED', $member->member_number);
        $this->assertSame('1990-01-01', $member->birth_date->format('Y-m-d'));
        $this->assertNotSame('검수 회원', $other->fresh()->name);
    }

    public function test_company_rejects_bypassed_profile_false_consent_and_executable_upload(): void
    {
        $member = User::factory()->create();
        $this->actingAs($member)->postJson('/register/step2/update', $this->company())->assertUnprocessable()->assertJsonValidationErrors('profile');
        $member->profile_completed_at = now(); $member->save();
        $payload = $this->company(); $payload['agree1'] = 0;
        $this->postJson('/register/step2/update', $payload)->assertUnprocessable()->assertJsonValidationErrors('agree1');
        $payload['agree1'] = 1; $payload['bank_copy_image'] = UploadedFile::fake()->createWithContent('payload.php', '<?php echo 1;');
        $this->postJson('/register/step2/update', $payload)->assertUnprocessable()->assertJsonValidationErrors('bank_copy_image');
        $this->assertDatabaseCount('vendors', 0);
    }

    public function test_seller_draft_is_separate_from_request_and_request_requires_document(): void
    {
        $member = $this->memberWithCompany();
        $this->postJson('/register/step3/update', $this->seller())->assertOk()->assertJson(['requested'=>false]);
        $this->assertSame('company', $member->fresh()->type);
        $this->assertNull(Vendor::find($member->vendor_id)->seller_requested_at);
        $payload = $this->seller(); $payload['action'] = 'request';
        $this->postJson('/register/step3/update', $payload)->assertUnprocessable()->assertJsonValidationErrors('address_proof_image');
        $this->assertNull(Vendor::find($member->vendor_id)->seller_requested_at);
        $payload['address_proof_image'] = UploadedFile::fake()->image('license-test.png');
        $this->postJson('/register/step3/update', $payload)->assertOk()->assertJson(['requested'=>true,'redirect_url'=>route('front.member.register.complete')]);
        $vendor = Vendor::find($member->vendor_id);
        $this->assertNotNull($vendor->seller_requested_at);
        $this->assertSame(0, (int)$vendor->status);
        $this->assertSame('No', $vendor->confirm);
        $this->get('/register/complete')->assertOk()->assertSee('가입 완료.')->assertSee('심사 대기');
    }

    public function test_resaving_company_preserves_seller_documents_and_request_status(): void
    {
        $member = $this->memberWithCompany();
        $payload = $this->seller(); $payload['action']='request'; $payload['address_proof_image']=UploadedFile::fake()->image('license.png');
        $this->postJson('/register/step3/update',$payload)->assertOk();
        $before = VendorsBusinessDetail::where('vendor_id',$member->vendor_id)->first();
        $payload = $this->company(); unset($payload['bank_copy_image']); $payload['shop_name']='수정된 회원사';
        $this->postJson('/register/step2/update',$payload)->assertOk();
        $after = $before->fresh();
        $this->assertSame($before->address_proof_image,$after->address_proof_image);
        $this->assertSame($before->bank_copy_image,$after->bank_copy_image);
        $this->assertSame('vendor',$member->fresh()->type);
        Storage::disk('local')->assertExists($after->address_proof_image);
    }

    public function test_document_download_belongs_to_authenticated_member_only(): void
    {
        $owner = $this->memberWithCompany();
        $path = VendorsBusinessDetail::where('vendor_id',$owner->vendor_id)->value('bank_copy_image');
        Storage::disk('local')->assertExists($path);
        $this->get(route('front.member.register.document','bankbook'))->assertOk()->assertDownload('bankbook.png');
        $other = User::factory()->create();
        $this->actingAs($other)->get(route('front.member.register.document','bankbook').'?vendor_id='.$owner->vendor_id)->assertNotFound();
        auth()->logout();
        $this->get(route('front.member.register.document','bankbook'))->assertRedirect(route('front.member.login'));
    }

    public function test_password_change_rejects_same_and_wrong_confirmation_before_accepting_new_password(): void
    {
        $user=User::factory()->create(['password'=>Hash::make('ReviewMember30!')]);
        $headers=['X-Requested-With'=>'XMLHttpRequest'];
        $this->actingAs($user)->postJson(route('user.update.password'),['current_password'=>'ReviewMember30!','new_password'=>'ReviewMember30!','confirm_password'=>'ReviewMember30!'],$headers)
            ->assertJson(['type'=>'error'])->assertJsonStructure(['errors'=>['new_password']]);
        $this->postJson(route('user.update.password'),['current_password'=>'ReviewMember30!','new_password'=>'FinalReview30!','confirm_password'=>'OtherReview30!'],$headers)
            ->assertJson(['type'=>'error']);
        $this->postJson(route('user.update.password'),['current_password'=>'ReviewMember30!','new_password'=>'FinalReview30!','confirm_password'=>'FinalReview30!'],$headers)
            ->assertJson(['type'=>'success']);
        $this->assertTrue(Hash::check('FinalReview30!', $user->fresh()->password));
    }

    public function test_only_platform_admin_can_review_submitted_private_documents(): void
    {
        $member = $this->memberWithCompany();
        $url = route('admin.member.document', ['vendor'=>$member->vendor_id, 'kind'=>'bankbook']);
        $this->get($url)->assertRedirect('/admin/login');
        $admin = new \App\Models\Admin;
        $admin->name='Review Admin'; $admin->type='superadmin'; $admin->vendor_id=0;
        $admin->mobile='01000002222'; $admin->email='review-admin@example.test';
        $admin->password=Hash::make('ReviewAdmin30!'); $admin->confirm='Yes'; $admin->status=1; $admin->save();
        $this->actingAs($admin,'admin')->get($url)->assertOk()->assertDownload('bankbook.png');
        $admin->type='vendor'; $admin->vendor_id=$member->vendor_id; $admin->save();
        $this->get($url)->assertForbidden();
    }
}
