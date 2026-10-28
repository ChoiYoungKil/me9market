<?php

namespace Tests\Feature;

use App\Models\PointTransaction;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\ShopChannelProduct;
use App\Models\User;
use App\Models\Vendor;
use App\Services\MemberWithdrawal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MemberWithdrawalTest extends TestCase
{
    use RefreshDatabase;

    private function vendor(string $name): Vendor
    {
        return Vendor::create(['name'=>$name,'email'=>$name.'@example.test','mobile'=>'01000002222','status'=>1,'commission'=>0]);
    }

    private function shop(Vendor $vendor, string $code): ShopChannel
    {
        return ShopChannel::create(['vendor_id'=>$vendor->id,'channel_code'=>$code,'channel_name'=>$code,'copyright'=>'검수','keywords'=>[],'status'=>1]);
    }

    public function test_only_active_approved_resale_by_other_company_blocks_withdrawal(): void
    {
        $vendor=$this->vendor('supplier');$other=$this->vendor('reseller');
        $user=User::factory()->create(['vendor_id'=>$vendor->id,'status'=>1,'password'=>Hash::make('ReviewWithdrawal44!')]);
        $product=Product::create(['section_id'=>1,'category_id'=>1,'brand_id'=>1,'vendor_id'=>$vendor->id,'admin_id'=>1,'admin_type'=>'vendor','product_name'=>'검수상품','product_code'=>'QA44','product_color'=>'Black','product_price'=>100,'product_discount'=>0,'product_weight'=>100,'status'=>1]);
        $own=$this->shop($vendor,'own');$reseller=$this->shop($other,'reseller');
        $listing=ShopChannelProduct::create(['shop_channel_id'=>$own->id,'product_id'=>$product->id,'product_type'=>'own','status'=>1,'approval_status'=>'approved']);
        $service=app(MemberWithdrawal::class);
        $this->assertSame(0,$service->sharedSellingCount($user));
        $listing->update(['shop_channel_id'=>$reseller->id,'product_type'=>'public']);
        $this->assertSame(1,$service->sharedSellingCount($user));
        $this->actingAs($user)->from(route('mypage.withdraw'))->post(route('mypage.withdraw.submit'),['reason'=>'reason_1','password'=>'ReviewWithdrawal44!','password_confirmation'=>'ReviewWithdrawal44!'])->assertSessionHasErrors('selling');
        $this->assertSame(1,(int)$user->fresh()->status);
        $this->assertDatabaseMissing('member_withdrawals',['user_id'=>$user->id]);
        $listing->update(['approval_status'=>'pending']);$this->assertSame(0,$service->sharedSellingCount($user));
        $listing->update(['approval_status'=>'approved']);$reseller->update(['status'=>0]);$this->assertSame(0,$service->sharedSellingCount($user));
    }

    public function test_withdrawal_expires_each_wallet_records_reason_and_logs_out_before_receipt(): void
    {
        $user=User::factory()->create(['status'=>1,'password'=>Hash::make('ReviewWithdrawal44!')]);
        foreach ([[null,800],[10,200]] as [$shop,$points]) PointTransaction::create(['user_id'=>$user->id,'shop_channel_id'=>$shop,'type'=>'earn','points'=>$points]);
        $this->actingAs($user)->post(route('mypage.withdraw.submit'),['reason'=>'reason_other','reason_detail'=>'검수 종료','password'=>'ReviewWithdrawal44!','password_confirmation'=>'ReviewWithdrawal44!'])->assertRedirect(route('mypage.withdraw.success'));
        $this->assertGuest();$this->assertSame(0,(int)$user->fresh()->status);
        $this->assertDatabaseHas('member_withdrawals',['user_id'=>$user->id,'reason'=>'reason_other','reason_detail'=>'검수 종료','expired_points'=>1000]);
        $this->assertSame(0,(int)PointTransaction::where('user_id',$user->id)->sum('points'));
        $this->assertSame(2,PointTransaction::where('user_id',$user->id)->where('type','withdraw_expire')->count());
        $this->get(route('mypage.withdraw.success'))->assertOk()->assertSee($user->name)->assertSee('회원 탈퇴 신청이 완료되었습니다.')->assertSee('로그인')->assertDontSee('로그아웃');
        $this->get(route('mypage.dashboard'))->assertRedirect(route('front.member.login'));
        $this->withSession(['member_withdrawal_receipt'=>null])->get(route('mypage.withdraw.success'))->assertNotFound();
    }

    public function test_invalid_reason_or_password_cannot_expire_points_or_close_account(): void
    {
        $user=User::factory()->create(['status'=>1,'password'=>Hash::make('ReviewWithdrawal44!')]);
        PointTransaction::create(['user_id'=>$user->id,'type'=>'earn','points'=>1000]);
        $this->actingAs($user)->from(route('mypage.withdraw'))->post(route('mypage.withdraw.submit'),['reason'=>'reason_other','password'=>'ReviewWithdrawal44!','password_confirmation'=>'ReviewWithdrawal44!'])->assertSessionHasErrors('reason_detail');
        $this->post(route('mypage.withdraw.submit'),['reason'=>'reason_1','password'=>'wrong','password_confirmation'=>'wrong'])->assertSessionHas('error_message','비밀번호가 일치하지 않습니다.');
        $this->assertSame(1,(int)$user->fresh()->status);
        $this->assertSame(1000,(int)PointTransaction::where('user_id',$user->id)->sum('points'));
        $this->assertDatabaseMissing('member_withdrawals',['user_id'=>$user->id]);
    }

    public function test_stale_session_cannot_access_member_pages_after_withdrawal(): void
    {
        $user=User::factory()->create(['status'=>1]);
        $this->actingAs($user);
        DB::table('users')->where('id',$user->id)->update(['status'=>0]);
        $this->get(route('mypage.profile'))->assertRedirect(route('front.member.login'));
        $this->assertGuest();
    }
}
