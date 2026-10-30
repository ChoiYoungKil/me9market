<?php
namespace App\Services;

use App\Models\Admin;
use App\Models\ChannelSubAccount;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MemberPortal
{
    public function shortcuts(): array
    {
        return ['profile'=>['회원정보 수정','mypage.profile'],'delivery'=>['배송지 설정','mypage.delivery'],
            'visited'=>['방문한 채널','mypage.visited_channels'],'points'=>['포인트 현황','mypage.point.status'],
            'cart'=>['장바구니','mypage.cart'],'wishlist'=>['찜한 상품','mypage.wishlist'],'orders'=>['주문목록','mypage.order.list']];
    }

    public function subAccounts(User $user)
    {
        if ((int)$user->status !== 1) return collect();
        return ChannelSubAccount::with(['admin','vendor.vendorbusinessdetails'])->where(fn($q)=>$q->where('user_id',$user->id)
            ->when($user->member_number,fn($q)=>$q->orWhere(fn($legacy)=>$legacy->whereNull('user_id')->where('member_no',$user->member_number))))
            ->activeNow()
            ->whereHas('admin',fn ($q)=>$q->where('type','subadmin')->where('status',1))->get();
    }

    public function owner(User $user): Admin
    {
        abort_unless((int)$user->status===1 && $user->vendor_id && in_array($user->type,['company','vendor'],true),403);
        return DB::transaction(function () use ($user) {
            $vendor=Vendor::whereKey($user->vendor_id)->lockForUpdate()->firstOrFail();
            abort_unless($vendor->company_terms_accepted_at,403);
            $admin=Admin::where('vendor_id',$vendor->id)->where('type','vendor')->first();
            if (! $admin) {
                abort_if(Admin::where('email',$user->email)->exists(),403);
                $admin=new Admin;
                $admin->vendor_id=$vendor->id; $admin->name=$user->name; $admin->email=$user->email;
                $admin->mobile=$user->mobile; $admin->type='vendor'; $admin->password=Hash::make(Str::random(64));
                $admin->confirm='Yes'; $admin->status=1; $admin->save();
            }
            abort_unless((int)$admin->status===1,403);
            return $admin;
        });
    }
}
