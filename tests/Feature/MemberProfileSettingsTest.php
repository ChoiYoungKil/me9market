<?php
namespace Tests\Feature;

use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberProfileSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_saves_optional_consent_without_changing_required_terms(): void
    {
        $user=User::factory()->create(['terms_accepted_at'=>now()->subDay(),'marketing_opt_in'=>true]);
        $accepted=$user->terms_accepted_at;
        $this->actingAs($user)->from(route('mypage.profile'))->post(route('mypage.profile.update'),[
            'name'=>'검수 회원','gender'=>'Male','birth_date'=>'1990-01-02',
            'mobile_1'=>'010','mobile_2'=>'0000','mobile_3'=>'2222','email_1'=>'profile','email_2'=>'example.test',
            'zipcode'=>'04524','address1'=>'검수 주소','address2'=>'검수 상세','marketing_opt_in'=>'0','notification_opt_in'=>'1',
            'terms_accepted_at'=>null,
        ])->assertRedirect(route('mypage.profile'))->assertSessionHasNoErrors();
        $user->refresh();
        $this->assertFalse($user->marketing_opt_in);
        $this->assertTrue($user->notification_opt_in);
        $this->assertSame('1990-01-02',$user->birth_date->format('Y-m-d'));
        $this->assertTrue($accepted->equalTo($user->terms_accepted_at));
    }

    public function test_pg_settings_require_approval_validate_keys_and_do_not_render_saved_secrets(): void
    {
        $user=User::factory()->create();
        $this->actingAs($user)->postJson(route('mypage.pg.update'),['use_own_pg'=>1])->assertForbidden();
        $vendor=new Vendor;
        $vendor->name='PG 검수';$vendor->email='pg@example.test';$vendor->mobile='01000002222';$vendor->commission=0;$vendor->status=1;$vendor->save();
        $user->forceFill(['vendor_id'=>$vendor->id])->save();
        $base=['vendor_id'=>$vendor->id,'channel_name'=>'PG 선택 검수','copyright'=>'검수','keywords'=>['PG'],'status'=>1,'is_public'=>1];
        $platform=\App\Models\ShopChannel::forceCreate($base+['channel_code'=>'platform-profile','use_own_pg'=>false]);
        $own=\App\Models\ShopChannel::forceCreate($base+['channel_code'=>'own-profile','use_own_pg'=>true]);
        $this->postJson(route('mypage.pg.update'),['use_own_pg'=>1,'pg_provider'=>'toss'])->assertUnprocessable();
        $data=['use_own_pg'=>1,'pg_provider'=>'toss','pg_merchant_id'=>'test-merchant','pg_site_code'=>'test-site','pg_client_key'=>'test-client-key','pg_secret_key'=>'test-secret-key'];
        $this->post(route('mypage.pg.update'),$data)->assertRedirect(route('mypage.profile'));
        $this->assertTrue($vendor->fresh()->use_own_pg);
        $this->assertFalse($platform->fresh()->use_own_pg);
        $this->assertSame('test-secret-key',$own->fresh()->pg_secret_key);
        $this->assertNotSame('test-secret-key',$vendor->fresh()->getRawOriginal('pg_secret_key'));
        $this->get(route('mypage.profile'))->assertOk()->assertDontSee('test-secret-key')->assertDontSee('test-client-key');
        $this->postJson(route('mypage.pg.update'),['use_own_pg'=>1,'pg_provider'=>'kcp'])->assertUnprocessable();
        $this->assertSame('toss',$vendor->fresh()->pg_provider);
        $this->post(route('mypage.pg.update'),['use_own_pg'=>0])->assertRedirect(route('mypage.profile'));
        $this->assertFalse($vendor->fresh()->use_own_pg);
        $this->assertFalse($own->fresh()->use_own_pg);
    }
}
