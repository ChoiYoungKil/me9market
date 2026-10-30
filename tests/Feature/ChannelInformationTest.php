<?php

namespace Tests\Feature;

use App\Models\{Admin, Vendor, VendorsBusinessDetail};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ChannelInformationTest extends TestCase
{
    use RefreshDatabase;

    private function owner(bool $approved=true): array
    {
        Storage::fake('local');
        $vendor=Vendor::forceCreate(['name'=>'검수회원사72','email'=>'company72@example.test','mobile'=>'01000000000','commission'=>7,
            'status'=>$approved?1:0,'company_terms_accepted_at'=>now()->subDays(2),'seller_terms_accepted_at'=>now()->subDays(2),
            'seller_approved_at'=>$approved?now()->subDay():null]);
        $admin=Admin::forceCreate(['vendor_id'=>$vendor->id,'name'=>'관리자72','email'=>'admin72@example.test','mobile'=>'01000000000',
            'type'=>'vendor','status'=>1,'confirm'=>'Yes','password'=>bcrypt('test72')]);
        $details=VendorsBusinessDetail::forceCreate(['vendor_id'=>$vendor->id,'shop_name'=>'검수회원사72','shop_business_type'=>'개인사업자',
            'business_license_number'=>'000-00-00000','shop_mobile'=>'010-0000-0000','shop_email'=>'company72@example.test',
            'bank_name'=>'검수은행','bank_account_number'=>'0000000000','bank_account_holder_name'=>'검수예금주']);
        return [$vendor,$admin,$details];
    }

    private function data(): array
    {
        return ['action'=>'save','shop_name'=>'검수회원사72','shop_business_type'=>'개인사업자','business_license_number'=>'000-00-00000',
            'shop_mobile'=>'010-0000-0000','shop_email'=>'company72@example.test','bank_name'=>'검수은행','bank_account_number'=>'0000000000',
            'bank_account_holder_name'=>'검수예금주','use_own_pg'=>'0'];
    }

    public function test_information_uses_real_approval_and_fee_and_locked_agreements(): void
    {
        [$vendor,$admin]=$this->owner();
        $this->actingAs($admin,'admin')->get('/channel/settings/info')->assertOk()->assertSee('수수료 7%')
            ->assertSee($vendor->seller_approved_at->format('Y-m-d'))->assertDontSee('2024-01-01')
            ->assertSee('type="checkbox" disabled',false)->assertSee('info-policy-body',false)->assertSee('판매회원 서비스 이용약관');
    }

    public function test_private_upload_view_download_and_recertification_never_auto_approves(): void
    {
        [$vendor,$admin,$details]=$this->owner(false);
        $payload=$this->data()+['bank_copy'=>UploadedFile::fake()->image('bank.png'),'cert_brn'=>UploadedFile::fake()->image('license.png')];
        $payload['action']='request';
        $this->actingAs($admin,'admin')->post('/channel/settings/info/update',$payload)->assertRedirect(route('channel.info.management'));
        $details->refresh(); Storage::disk('local')->assertExists($details->bank_copy_image);
        Storage::disk('local')->assertExists($details->address_proof_image);
        $this->assertStringStartsWith('member-documents/'.$vendor->id.'/',$details->bank_copy_image);
        $this->assertSame(0,$vendor->fresh()->status); $this->assertNotNull($vendor->fresh()->seller_requested_at);
        $this->get('/channel/settings/documents/bankbook/view')->assertOk()->assertHeader('Cache-Control','no-store, private');
        $response=$this->get('/channel/settings/documents/license/download')->assertOk();
        $this->assertStringContainsString('attachment',$response->headers->get('Content-Disposition'));
        $this->get('/channel/settings/documents/unknown/view')->assertNotFound();
        auth('admin')->logout();$this->get('/channel/settings/documents/bankbook/view')->assertRedirect(route('channel.login'));
    }

    public function test_invalid_files_and_unrequested_approved_identity_changes_are_rejected_atomically(): void
    {
        [$vendor,$admin,$details]=$this->owner();
        $this->actingAs($admin,'admin')->post('/channel/settings/info/update',$this->data()+['cert_brn'=>UploadedFile::fake()->create('bad.php',1,'text/plain')])
            ->assertSessionHasErrors('cert_brn');
        $payload=$this->data();$payload['bank_account_number']='1111111111';$payload['shop_name']='변경되면 안 됨';
        $this->post('/channel/settings/info/update',$payload)->assertSessionHasErrors('certification');
        $this->assertSame('검수회원사72',$details->fresh()->shop_name);$this->assertSame('0000000000',$details->fresh()->bank_account_number);
        $this->assertSame(1,$vendor->fresh()->status);
    }

    public function test_unchanged_uploaded_bytes_can_be_saved_without_removing_existing_approval(): void
    {
        [$vendor,$admin,$details]=$this->owner();
        $file=UploadedFile::fake()->image('same.png');
        $path='member-documents/'.$vendor->id.'/original.png';Storage::disk('local')->put($path,file_get_contents($file->getRealPath()));
        $details->bank_copy_image=$path;$details->save();
        $this->actingAs($admin,'admin')->post('/channel/settings/info/update',$this->data()+['bank_copy'=>$file])->assertRedirect(route('channel.info.management'));
        $this->assertSame(1,$vendor->fresh()->status);$this->assertNotSame($path,$details->fresh()->bank_copy_image);
        Storage::disk('local')->assertExists($path);
    }

    public function test_pg_keys_are_preserved_and_cannot_be_reused_across_provider_changes(): void
    {
        [$vendor,$admin]=$this->owner();
        $vendor->fill(['use_own_pg'=>true,'pg_provider'=>'toss','pg_merchant_id'=>'mock-merchant','pg_site_code'=>'mock-site','pg_client_key'=>'mock-client','pg_secret_key'=>'mock-secret'])->save();
        $payload=$this->data();$payload['use_own_pg']='1';$payload['pg_provider']='toss';
        $this->actingAs($admin,'admin')->post('/channel/settings/info/update',$payload)->assertRedirect(route('channel.info.management'));
        $this->assertSame('mock-secret',$vendor->fresh()->pg_secret_key);
        $payload['pg_provider']='kcp';$this->post('/channel/settings/info/update',$payload)->assertSessionHasErrors('pg_merchant_id');
        $this->assertSame('toss',$vendor->fresh()->pg_provider);
    }

    public function test_registering_vendor_pg_does_not_switch_other_channels_to_own_pg(): void
    {
        [$vendor,$admin]=$this->owner();
        $base=['vendor_id'=>$vendor->id,'channel_name'=>'PG 선택 유지','copyright'=>'검수','keywords'=>['PG'],'status'=>1,'is_public'=>1];
        $platform=\App\Models\ShopChannel::forceCreate($base+['channel_code'=>'platform72','use_own_pg'=>false]);
        $own=\App\Models\ShopChannel::forceCreate($base+['channel_code'=>'own72','use_own_pg'=>true,'pg_secret_key'=>'old-secret']);
        $payload=array_replace($this->data(),['use_own_pg'=>'1','pg_provider'=>'toss','pg_merchant_id'=>'mock-merchant',
            'pg_site_code'=>'mock-site','pg_client_key'=>'mock-client','pg_secret_key'=>'mock-secret']);
        $this->actingAs($admin,'admin')->post('/channel/settings/info/update',$payload)->assertRedirect(route('channel.info.management'));
        $this->assertFalse($platform->fresh()->use_own_pg);
        $this->assertTrue($own->fresh()->use_own_pg);
        $this->assertSame('mock-secret',$own->fresh()->pg_secret_key);
        $this->post('/channel/settings/info/update',$this->data())->assertRedirect();
        $this->assertFalse($own->fresh()->use_own_pg);
    }

    public function test_recertification_with_changed_documents_returns_to_pending_without_granting_rights(): void
    {
        [$vendor,$admin]=$this->owner();
        $payload=$this->data()+['bank_copy'=>UploadedFile::fake()->image('changed-bank.png'),'cert_brn'=>UploadedFile::fake()->image('changed-license.png')];
        $payload['action']='request';$payload['bank_account_number']='1111111111';
        $this->actingAs($admin,'admin')->post('/channel/settings/info/update',$payload)->assertRedirect(route('channel.info.management'));
        $this->assertSame(0,$vendor->fresh()->status);
        $this->assertNull($vendor->fresh()->seller_approved_at);
        $this->assertNotNull($vendor->fresh()->seller_requested_at);
        $this->get('/channel/settings/info')->assertOk()->assertSee('data-certification="pending"',false)->assertDontSee('data-certification="approved"',false);
    }

    public function test_all_eight_supplied_policies_have_readable_routes_and_arbitrary_paths_are_rejected(): void
    {
        foreach(['service','seller','shop','guest-consent','guest-notice','seller-provision','privacy','member-marketing'] as $slug) {
            $this->get('/policies/'.$slug)->assertOk()->assertSee('2026-10-26')->assertSee('미확정 항목 포함');
        }
        $this->get('/policies/missing')->assertNotFound();
    }
}
