<?php

namespace App\Services;

use App\Models\{Admin, User, Vendor, VendorsBankDetail, VendorsBusinessDetail};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\ValidationException;

class ChannelInformation
{
    public function data(Admin $admin): array
    {
        $vendor = Vendor::find($admin->vendor_id);
        $details = VendorsBusinessDetail::where('vendor_id', $admin->vendor_id)->first();
        $member = User::where('vendor_id', $admin->vendor_id)->whereIn('type', ['company','vendor'])->orderBy('id')->first();
        $policyDocuments = [];
        foreach (['service','seller'] as $policy) $policyDocuments[$policy] = json_decode(file_get_contents(resource_path('policies/'.$policy.'.json')),true,512,JSON_THROW_ON_ERROR);
        $certification = (int) $vendor?->status === 1 ? 'approved' : ($vendor?->seller_requested_at ? 'pending' : 'unverified');
        return compact('admin','vendor','details','member','certification','policyDocuments') + ['activeChannels'=>\App\Models\ShopChannel::where('vendor_id',$admin->vendor_id)->where('status',1)->count()];
    }

    private function sameFile(?string $path, $uploaded): bool
    {
        if (!$path || !$uploaded) return false;
        $full = str_starts_with($path, 'member-documents/') ? Storage::disk('local')->path($path)
            : (basename($path) === $path ? public_path('front/images/bank_copies/'.$path) : null);
        return $full && is_file($full) && hash_file('sha256',$full) === hash_file('sha256',$uploaded->getRealPath());
    }

    public function update(Request $request, Admin $admin): string
    {
        $vendor = Vendor::findOrFail($admin->vendor_id);
        $details = VendorsBusinessDetail::where('vendor_id',$vendor->id)->first();
        $data = $request->validate([
            'action'=>'required|in:save,request', 'shop_name'=>'required|string|max:255',
            'shop_business_type'=>'required|in:개인판매,개인사업자,법인사업자',
            'business_license_number'=>['nullable','regex:/^[0-9]{3}-?[0-9]{2}-?[0-9]{5}$/'],
            'shop_mobile'=>['nullable','regex:/^0[0-9-]{8,14}$/'], 'shop_email'=>'nullable|email|max:150',
            'shop_pincode'=>['nullable','regex:/^[0-9]{5}$/'], 'shop_address'=>'nullable|string|max:255','shop_address_detail'=>'nullable|string|max:255',
            'bank_name'=>'nullable|string|max:100','bank_account_number'=>['nullable','regex:/^[0-9]{6,30}$/'],'bank_account_holder_name'=>'nullable|string|max:100',
            'bank_copy'=>'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120','cert_brn'=>'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'use_own_pg'=>'required|boolean','pg_provider'=>'nullable|in:inicis,kcp,toss',
            'pg_merchant_id'=>'nullable|string|max:255','pg_site_code'=>'nullable|string|max:255',
            'pg_client_key'=>'nullable|string|max:255','pg_secret_key'=>'nullable|string|max:255',
        ]);
        $requesting = $data['action'] === 'request';
        $sensitiveChange = false;
        foreach (['business_license_number','bank_name','bank_account_number','bank_account_holder_name'] as $field) {
            if (preg_replace('/[ -]/','',(string)($data[$field] ?? '')) !== preg_replace('/[ -]/','',(string)$details?->$field)) $sensitiveChange = true;
        }
        foreach (['bank_copy'=>'bank_copy_image','cert_brn'=>'address_proof_image'] as $field=>$column) {
            if ($request->hasFile($field) && !$this->sameFile($details?->$column,$request->file($field))) $sensitiveChange = true;
        }
        if ((int)$vendor->status === 1 && $sensitiveChange && !$requesting) {
            throw ValidationException::withMessages(['certification'=>'인증된 사업자·계좌·서류를 변경하려면 재인증 요청으로 제출해 주세요.']);
        }
        if ($requesting) {
            abort_unless($admin->type === 'vendor',403);
            if (!$vendor->company_terms_accepted_at || !$vendor->seller_terms_accepted_at) {
                throw ValidationException::withMessages(['terms'=>'회원 가입 단계에서 필수 약관 동의를 먼저 완료해 주세요.']);
            }
            foreach (['bank_name','bank_account_number','bank_account_holder_name','business_license_number'] as $field) {
                if (blank($data[$field] ?? null)) throw ValidationException::withMessages([$field=>'판매인증 신청 정보를 입력해 주세요.']);
            }
            foreach (['bank_copy'=>'bank_copy_image','cert_brn'=>'address_proof_image'] as $field=>$column) {
                if (!$request->hasFile($field) && !$details?->$column) throw ValidationException::withMessages([$field=>'판매인증 서류를 첨부해 주세요.']);
            }
        }
        $enabled = $request->boolean('use_own_pg');
        if ($enabled && ((int)$vendor->status !== 1 || $requesting)) throw ValidationException::withMessages(['use_own_pg'=>'판매인증 완료 상태에서 자사 PG를 설정해 주세요.']);
        $pg = [];
        if ($enabled) {
            $changed = filled($data['pg_provider'] ?? null) && $data['pg_provider'] !== $vendor->pg_provider;
            foreach (['pg_provider','pg_merchant_id','pg_site_code','pg_client_key','pg_secret_key'] as $field) {
                $pg[$field] = filled($data[$field] ?? null) ? $data[$field] : ($changed ? null : $vendor->$field);
                if (!$pg[$field]) throw ValidationException::withMessages([$field=>'자사 PG 연결 정보를 입력해 주세요.']);
            }
        }
        $newFiles = [];
        try {
            DB::transaction(function () use ($request,$admin,$data,$vendor,$requesting,$enabled,$pg,$sensitiveChange,&$newFiles) {
                $locked = Vendor::whereKey($vendor->id)->lockForUpdate()->firstOrFail();
                if ((int)$locked->status === 1 && $sensitiveChange && !$requesting) throw ValidationException::withMessages(['certification'=>'인증 정보 변경은 재인증 요청으로 제출해 주세요.']);
                if ($enabled && (int)$locked->status !== 1) throw ValidationException::withMessages(['use_own_pg'=>'판매인증 완료 후 자사 PG를 설정해 주세요.']);
                $details = VendorsBusinessDetail::where('vendor_id',$vendor->id)->first() ?? new VendorsBusinessDetail;
                $details->vendor_id = $vendor->id;
                foreach (['shop_name','shop_business_type','business_license_number','shop_mobile','shop_email','shop_pincode','shop_address','shop_address_detail','bank_name','bank_account_number','bank_account_holder_name'] as $field) $details->$field = $data[$field] ?? '';
                foreach (['bank_copy'=>'bank_copy_image','cert_brn'=>'address_proof_image'] as $field=>$column) {
                    if ($request->hasFile($field)) {
                        $path = $request->file($field)->store('member-documents/'.$vendor->id,'local');
                        if (!$path) throw new \RuntimeException('서류 저장에 실패했습니다.');
                        $newFiles[] = $path; $details->$column = $path;
                    }
                }
                $details->save();
                $bank = VendorsBankDetail::where('vendor_id',$vendor->id)->first() ?? new VendorsBankDetail;
                $bank->vendor_id=$vendor->id; $bank->bank_name=$details->bank_name;
                $bank->account_number=$details->bank_account_number; $bank->account_holder_name=$details->bank_account_holder_name;
                $bank->bank_ifsc_code=$bank->bank_ifsc_code ?? ''; $bank->save();
                $locked->name=$data['shop_name']; $locked->use_own_pg=$enabled;
                if ($enabled) $locked->fill($pg);
                if ($requesting) {
                    $locked->seller_requested_at=now(); $locked->status=0;
                    $locked->seller_approved_at=null; $locked->seller_approved_by=null;
                }
                $locked->save();
                foreach (\App\Models\ShopChannel::where('vendor_id',$vendor->id)->get() as $shop) {
                    if (!$enabled) $shop->use_own_pg=false;
                    elseif ($shop->use_own_pg) foreach ($pg as $field=>$value) $shop->$field=$value;
                    $shop->save();
                }
            });
        } catch (\Throwable $exception) { Storage::disk('local')->delete($newFiles); throw $exception; }
        return $requesting ? 'requested' : 'saved';
    }
}
