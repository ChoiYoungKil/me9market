<?php

namespace Tests\Feature;

use App\Models\{Admin,ShopChannel,Vendor};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ShopChannelConfigurationTest extends TestCase
{
    use RefreshDatabase;
    private array $files=[];

    protected function tearDown(): void
    {
        foreach($this->files as $path)if(is_file($path))unlink($path);
        parent::tearDown();
    }

    private function owner(): array
    {
        $v=Vendor::create(['name'=>'채널78 회원사','mobile'=>'01000000000','email'=>'vendor78@example.test','status'=>1,'commission'=>0]);
        $a=Admin::forceCreate(['vendor_id'=>$v->id,'name'=>'관리자78','email'=>'admin78@example.test','mobile'=>'01000000000','type'=>'vendor','status'=>1,'confirm'=>'Yes','password'=>bcrypt('test78')]);
        $this->actingAs($a,'admin');return[$v,$a];
    }

    private function data(array $extra=[]): array
    {
        return $extra+['channel_code'=>'qa-channel-78','channel_name'=>'채널78','copyright'=>'검수','keywords'=>['검수키워드'],
            'status'=>0,'is_public'=>1,'use_period_type'=>0,'use_logo'=>0,'use_banner'=>0,'use_og'=>0,'use_own_pg'=>0,'use_admin'=>0,'use_purchase_sms'=>0];
    }

    public function test_period_hours_and_keyword_and_six_banner_validation_prevent_partial_registration(): void
    {
        $this->owner();
        $this->post('/channel/shop/register',$this->data(['use_period_type'=>1,'start_date'=>'2026-10-31','start_hour'=>20,'end_date'=>'2026-10-31','end_hour'=>19]))->assertSessionHasErrors('end_date');
        $this->post('/channel/shop/register',$this->data(['keywords'=>[]]))->assertSessionHasErrors('keywords');
        $this->post('/channel/shop/register',$this->data(['use_banner'=>1,'banner_files'=>array_map(fn($n)=>UploadedFile::fake()->image('banner'.$n.'.png'),range(1,6))]))->assertSessionHasErrors('banner_files');
        $this->assertSame(0,ShopChannel::count());
    }

    public function test_images_keywords_monitoring_and_messages_are_saved_and_retained_on_edit(): void
    {
        $this->owner();
        $payload=$this->data(['use_logo'=>1,'logo_image'=>UploadedFile::fake()->image('logo.png'),'use_banner'=>1,
            'banner_files'=>[UploadedFile::fake()->image('banner.png')],'use_og'=>1,'og_title'=>'OG78','og_description'=>'설명78','og_image'=>UploadedFile::fake()->image('og.png'),
            'use_admin'=>1,'admin_name'=>'검수 모니터','admin_login_id'=>'monitor78','admin_password'=>'ReviewMonitor78!','settlement_type'=>2,'settlement_rate_amount'=>1000,
            'use_purchase_sms'=>1,'purchase_sms_templates'=>['purchase'=>'{{구매자}} {{구매상품}}']]);
        $this->post('/channel/shop/register',$payload)->assertRedirect(route('channel.shop_list'));
        $shop=ShopChannel::firstOrFail();foreach([$shop->logo_image,$shop->og_image,...$shop->banner_images] as $path){$this->files[]=public_path($path);$this->assertFileExists(public_path($path));}
        $this->assertSame(['검수키워드'],$shop->keywords);$this->assertTrue(password_verify('ReviewMonitor78!',$shop->admin_password));
        $this->assertEquals(1000,$shop->settlement_rate);$this->assertSame('{{구매자}} {{구매상품}}',$shop->purchase_sms_templates['purchase']);
        unset($payload['logo_image'],$payload['og_image'],$payload['banner_files'],$payload['admin_password'],$payload['channel_code']);
        $payload['existing_banners']=$shop->banner_images;
        $this->post('/channel/shop/info-update/'.$shop->id,$payload)->assertRedirect(route('channel.shop_info',['id'=>$shop->id]));
        $this->assertSame($shop->logo_image,$shop->fresh()->logo_image);$this->assertTrue(password_verify('ReviewMonitor78!',$shop->fresh()->admin_password));
    }

    public function test_private_excel_preview_and_save_preserve_phone_zeroes_and_disable_member_only(): void
    {
        $this->owner();
        $book=new \PhpOffice\PhpSpreadsheet\Spreadsheet();$book->getActiveSheet()->fromArray([['휴대폰번호','입장코드'],['010-0000-7901','TEST79A'],['010-0000-7902','TEST79B']]);
        $path=tempnam(sys_get_temp_dir(),'shop79');$this->files[]=$path;(new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($book))->save($path);
        $upload=new UploadedFile($path,'private.xlsx','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',null,true);
        $this->postJson('/channel/shop/private-access-preview',['private_access_file'=>$upload])->assertOk()->assertJsonPath('rows.0.phone_normalized','01000007901')->assertJsonCount(2,'rows');
        $this->post('/channel/shop/register',$this->data(['is_public'=>0,'is_member_only'=>1,'private_access_file'=>$upload]))->assertRedirect(route('channel.shop_list'));
        $shop=ShopChannel::firstOrFail();$this->assertEquals(0,$shop->is_member_only);$this->assertCount(2,$shop->privateAccesses);
        $id=$shop->privateAccesses->first()->id;
        $this->post('/channel/shop/info-update/'.$shop->id,$this->data(['is_public'=>0,'private_access_rows'=>'010-0000-7901,TEST79A']))->assertRedirect();
        $this->assertSame([$id],$shop->privateAccesses()->pluck('id')->all());
    }

    public function test_bad_private_import_and_oversized_sms_never_create_a_channel(): void
    {
        $this->owner();
        $this->post('/channel/shop/register',$this->data(['is_public'=>0,'private_access_rows'=>'invalid-phone,CODE']))->assertSessionHasErrors('private_access_rows');
        $this->post('/channel/shop/register',$this->data(['use_purchase_sms'=>1,'purchase_sms_templates'=>['purchase'=>str_repeat('가',301)]]))->assertSessionHasErrors('purchase_sms_templates.purchase');
        $this->assertSame(0,ShopChannel::count());
    }

    public function test_own_pg_requires_registered_vendor_settings_and_ignores_forged_keys(): void
    {
        [$vendor]=$this->owner();
        $this->post('/channel/shop/register',$this->data(['use_own_pg'=>1,'pg_provider'=>'toss','pg_secret_key'=>'forged']))->assertSessionHasErrors('use_own_pg');
        $vendor->fill(['use_own_pg'=>1,'pg_provider'=>'toss','pg_merchant_id'=>'registered-mid','pg_site_code'=>'registered-site','pg_client_key'=>'registered-client','pg_secret_key'=>'registered-secret'])->save();
        $this->post('/channel/shop/register',$this->data(['use_own_pg'=>1,'pg_secret_key'=>'forged']))->assertRedirect(route('channel.shop_list'));
        $this->assertSame('registered-secret',ShopChannel::first()->pg_secret_key);
    }

    public function test_shop_keyword_search_handles_unicode_json_and_copy_keeps_products_separate(): void
    {
        [$vendor]=$this->owner();
        $this->post('/channel/shop/register',$this->data())->assertRedirect();$shop=ShopChannel::firstOrFail();
        $product=\App\Models\Product::forceCreate(['section_id'=>1,'category_id'=>1,'brand_id'=>1,'vendor_id'=>$vendor->id,'admin_id'=>1,'admin_type'=>'vendor',
            'product_name'=>'복사 제외 상품','product_code'=>'COPY75','product_color'=>'Blue','product_price'=>100,'product_discount'=>0,'product_weight'=>1,'is_featured'=>'No','is_bestseller'=>'No','status'=>1]);
        \App\Models\ShopChannelProduct::create(['shop_channel_id'=>$shop->id,'product_id'=>$product->id,'product_type'=>'own','status'=>1,'product_price'=>100,'selling_price'=>120,'profit'=>20]);
        $this->get('/channel/shop/list?search_keyword='.urlencode('검수'))->assertOk()->assertViewHas('shops',fn($shops)=>$shops->total()===1);
        $this->post('/channel/shop/copy/'.$shop->id)->assertRedirect();$copy=ShopChannel::latest('id')->first();
        $this->assertNotSame($shop->channel_code,$copy->channel_code);$this->assertEquals(0,$copy->status);$this->assertSame(0,$copy->shopChannelProducts()->count());
        $this->assertSame(1,$shop->shopChannelProducts()->count());
    }

    public function test_detail_shows_saved_configuration_without_exposing_credentials_and_keeps_tab_context(): void
    {
        $this->owner();
        $this->post('/channel/shop/register',$this->data(['is_public'=>0,'private_access_rows'=>'010-0000-7901,TEST79A',
            'use_admin'=>1,'admin_name'=>'상세 모니터','admin_login_id'=>'detail90','admin_password'=>'ReviewMonitor90!',
            'settlement_type'=>2,'settlement_rate_amount'=>1000,'use_purchase_sms'=>1,
            'purchase_sms_templates'=>['purchase'=>'{{구매자}} 구매 완료','cancel'=>'취소 완료']]))->assertRedirect();
        $shop=ShopChannel::firstOrFail();
        $this->get('/channel/shop/info?id='.$shop->id)->assertOk()->assertSee('접속가능인원 1명')
            ->assertSee('010-0000-7901')->assertSee('{{구매자}} 구매 완료')->assertSee('1,000 원 / 판매 개당')
            ->assertSee(route('channel.shop_product01',['shop_id'=>$shop->id]),false)
            ->assertSee(route('channel.shop_community',['shop_id'=>$shop->id]),false)
            ->assertSee(route('channel.shop.open',$shop->id),false)
            ->assertDontSee('ReviewMonitor90!')->assertDontSee($shop->admin_password);
    }

    public function test_private_entry_code_with_comma_or_quote_survives_edit_round_trip(): void
    {
        $this->owner();
        $code='CODE,79"A';
        $import=app(\App\Services\PrivateAccessImport::class);
        $rows=[['phone'=>'010-0000-7901','entry_code'=>$code,'user_id'=>null]];
        $this->post('/channel/shop/register',$this->data(['is_public'=>0,'private_access_rows'=>$import->text($rows)]))->assertRedirect();
        $shop=ShopChannel::firstOrFail();
        $this->assertSame($code,$shop->privateAccesses->first()->entry_code);
        $saved=$import->text($shop->privateAccesses->map->only(['phone','entry_code','user_id'])->all());
        $this->post('/channel/shop/info-update/'.$shop->id,$this->data(['is_public'=>0,'private_access_rows'=>$saved]))->assertRedirect();
        $this->assertSame($code,$shop->privateAccesses()->first()->entry_code);
    }

    public function test_edit_keeps_channel_identity_and_rolls_back_invalid_or_unauthorized_changes(): void
    {
        [$vendor]=$this->owner();
        $this->post('/channel/shop/register',$this->data())->assertRedirect();
        $shop=ShopChannel::firstOrFail();
        $this->post('/channel/shop/info-update/'.$shop->id,$this->data(['channel_code'=>'forged-code','channel_name'=>'변경 금지']))
            ->assertSessionHasErrors('channel_code');
        $this->assertSame('qa-channel-78',$shop->fresh()->channel_code);
        $this->assertSame('채널78',$shop->fresh()->channel_name);
        $this->post('/channel/shop/info-update/'.$shop->id,$this->data(['keywords'=>[],'channel_name'=>'부분 저장 금지']))
            ->assertSessionHasErrors('keywords');
        $this->assertSame('채널78',$shop->fresh()->channel_name);
        $other=Vendor::create(['name'=>'외부91','email'=>'outside91@example.test','mobile'=>'01000000000','status'=>1]);
        $admin=Admin::forceCreate(['vendor_id'=>$other->id,'name'=>'다른 관리자','email'=>'outside-admin91@example.test',
            'mobile'=>'01000000000','type'=>'vendor','status'=>1,'confirm'=>'Yes','password'=>bcrypt('outside91')]);
        $this->actingAs($admin,'admin')->get('/channel/shop/info-update/'.$shop->id)->assertNotFound();
        $this->post('/channel/shop/info-update/'.$shop->id,$this->data(['channel_name'=>'외부 수정 금지']))->assertNotFound();
        $this->assertSame('채널78',$shop->fresh()->channel_name);
    }

    public function test_edit_toggles_control_live_shop_logo_and_og_and_can_restore_saved_images(): void
    {
        $this->owner();
        $payload=$this->data(['status'=>1,'use_logo'=>1,'logo_image'=>UploadedFile::fake()->image('logo91.png'),
            'use_og'=>1,'og_title'=>'공유 제목91','og_description'=>'공유 설명91','og_image'=>UploadedFile::fake()->image('og91.png')]);
        $this->post('/channel/shop/register',$payload)->assertRedirect();
        $shop=ShopChannel::firstOrFail();foreach([$shop->logo_image,$shop->og_image] as $path)$this->files[]=public_path($path);
        $this->withSession(['shop_channel_id'=>$shop->id])->get('/shop-channel/main')->assertOk()
            ->assertSee('content="공유 제목91"',false)->assertSee('src="'.asset($shop->logo_image).'"',false);
        $this->post('/channel/shop/info-update/'.$shop->id,$this->data(['status'=>1,'channel_name'=>'수정 채널91',
            'og_title'=>'공유 제목91','og_description'=>'공유 설명91']))->assertRedirect();
        $this->withSession(['shop_channel_id'=>$shop->id])->get('/shop-channel/main')->assertOk()
            ->assertSee('class="channel-name-logo">수정 채널91',false)->assertSee('content="수정 채널91"',false)
            ->assertDontSee('공유 제목91')->assertDontSee('공유 설명91')
            ->assertDontSee('src="'.asset($shop->logo_image).'"',false);
        $this->assertSame($shop->logo_image,$shop->fresh()->logo_image);
        unset($payload['logo_image'],$payload['og_image']);
        $this->post('/channel/shop/info-update/'.$shop->id,$payload)->assertRedirect();
        $this->withSession(['shop_channel_id'=>$shop->id])->get('/shop-channel/main')->assertOk()
            ->assertSee('content="공유 제목91"',false)->assertSee('src="'.asset($shop->logo_image).'"',false);
    }

    public function test_detail_and_community_never_show_another_vendors_channel(): void
    {
        [$vendor]=$this->owner();
        $other=Vendor::create(['name'=>'다른 회원사','mobile'=>'01000000000','email'=>'other90@example.test','status'=>1]);
        $shop=ShopChannel::forceCreate(['vendor_id'=>$other->id,'channel_code'=>'other90','channel_name'=>'타사 채널90','copyright'=>'','keywords'=>['타사'],'status'=>1,'is_public'=>1]);
        \App\Models\ShopChannelNotice::forceCreate(['shop_channel_id'=>$shop->id,'title'=>'외부 비공개 공지90','content'=>'표시 금지','type'=>'notice','status'=>1]);
        $this->get('/channel/shop/info?id='.$shop->id)->assertRedirect(route('channel.shop_list'));
        $this->get('/channel/shop/community?shop_id='.$shop->id)->assertRedirect(route('channel.shop_list'));
    }
}
