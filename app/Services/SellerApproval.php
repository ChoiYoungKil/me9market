<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorsBusinessDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SellerApproval
{
    public function issues(User $user, ?Vendor $vendor, ?VendorsBusinessDetail $business): array
    {
        $issues = [];
        if ((int) $user->status !== 1) { $issues[] = '비활성 회원은 승인할 수 없습니다.'; }
        if (! $user->profile_completed_at) { $issues[] = '기본정보 저장이 완료되지 않았습니다.'; }
        if (! $vendor?->company_terms_accepted_at || ! $business) { $issues[] = '회원사 정보 저장이 완료되지 않았습니다.'; }
        if (! $vendor?->seller_requested_at || ! $vendor?->seller_terms_accepted_at) { $issues[] = '판매 권한 인증 요청이 접수되지 않았습니다.'; }
        foreach (['address_proof_image' => '사업자등록증', 'bank_copy_image' => '통장사본'] as $field => $label) {
            $path = $business?->$field;
            $exists = $path && $vendor && (str_starts_with($path, 'member-documents/'.$vendor->id.'/')
                ? Storage::disk('local')->exists($path)
                : (basename($path) === $path && is_file(public_path('front/images/bank_copies/'.$path))));
            if (! $exists) { $issues[] = $label.' 파일을 확인할 수 없습니다.'; }
        }
        if (! $business?->bank_name || ! $business?->bank_account_number || ! $business?->bank_account_holder_name) {
            $issues[] = '정산 계좌 정보가 누락되었습니다.';
        }
        return $issues;
    }

    public function approve(User $user, Admin $reviewer): Vendor
    {
        abort_unless(in_array($reviewer->type, ['superadmin', 'admin'], true) && (int) $reviewer->status === 1, 403);
        return DB::transaction(function () use ($user, $reviewer) {
            $user = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $vendor = $user->vendor_id ? Vendor::whereKey($user->vendor_id)->lockForUpdate()->first() : null;
            $business = $vendor ? VendorsBusinessDetail::where('vendor_id', $vendor->id)->first() : null;
            if ($issues = $this->issues($user, $vendor, $business)) {
                throw ValidationException::withMessages(['seller' => $issues]);
            }
            $vendor->status = 1;
            $vendor->confirm = 'Yes';
            if (! $vendor->seller_approved_at) {
                $vendor->seller_approved_at = now();
                $vendor->seller_approved_by = $reviewer->id;
            }
            $vendor->save();
            $user->type = 'vendor';
            $user->save();
            // 이미 연결된 판매자 관리자 계정이 있는 경우에만 상태를 동기화한다.
            Admin::where('vendor_id', $vendor->id)->where('type', 'vendor')->update(['status' => 1, 'confirm' => 'Yes']);
            return $vendor;
        });
    }
}
