<?php

namespace App\Services;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorsBankDetail;
use App\Models\VendorsBusinessDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MemberRegistration
{
    public function memberNumber(User $user): string
    {
        if ($user->member_number) {
            return $user->member_number;
        }
        $rank = User::whereDate('created_at', $user->created_at->format('Y-m-d'))->where('id', '<=', $user->id)->count();
        return 'M9-'.$user->created_at->format('ymd').'-'.str_pad($rank, 4, '0', STR_PAD_LEFT);
    }

    public function details(User $user): array
    {
        return [
            'user' => $user,
            'memberNumber' => $this->memberNumber($user),
            'vendorDetails' => $user->vendor_id ? Vendor::find($user->vendor_id) : null,
            'businessDetails' => $user->vendor_id ? VendorsBusinessDetail::where('vendor_id', $user->vendor_id)->first() : null,
            'bankDetails' => $user->vendor_id ? VendorsBankDetail::where('vendor_id', $user->vendor_id)->first() : null,
            'isSocialMember' => \App\Models\SocialAccount::where('user_id', $user->id)->exists(),
        ];
    }

    private function phoneRules(): array
    {
        return ['mobile_1' => ['required', 'regex:/^0[0-9]{1,2}$/'], 'mobile_2' => ['required', 'regex:/^[0-9]{3,4}$/'], 'mobile_3' => ['required', 'regex:/^[0-9]{4}$/']];
    }

    private function addressRules(): array
    {
        return ['zipcode' => ['required', 'regex:/^[0-9]{5}$/'], 'address1' => 'required|string|max:255', 'address2' => 'required|string|max:255'];
    }

    private function bankRules(): array
    {
        return ['bank_name' => 'required|string|max:100', 'account_number' => ['required', 'regex:/^[0-9]{6,30}$/'], 'account_holder_name' => 'required|string|max:100'];
    }

    public function saveProfile(Request $request, User $user): void
    {
        $data = $request->validate([
            'name' => 'required|string|max:100', 'gender' => 'nullable|in:Male,Female',
            'birth_date' => 'nullable|date_format:Y-m-d|before_or_equal:today|after:1900-01-01',
            'email' => ['sometimes', 'required', 'email', 'max:150', Rule::unique('users')->ignore($user->id)],
        ] + $this->phoneRules() + $this->addressRules());
        $consent = app(RegistrationConsent::class)->capture($request, ['agree_terms', 'agree_privacy', 'agree_third_party']);
        if (isset($data['email']) && $data['email'] !== $user->email && \App\Models\SocialAccount::where('user_id', $user->id)->exists()) {
            throw ValidationException::withMessages(['email' => '소셜가입 이메일은 해당 인증 계정의 이메일을 사용합니다.']);
        }
        $user->name = $data['name'];
        $user->gender = $data['gender'] ?? $user->gender;
        $user->birth_date = $data['birth_date'] ?? $user->birth_date;
        if (isset($data['email']) && $data['email'] !== $user->email) {
            $user->email = $data['email'];
            $user->email_verified_at = null;
        }
        $user->mobile = $data['mobile_1'].'-'.$data['mobile_2'].'-'.$data['mobile_3'];
        $user->pincode = $data['zipcode'];
        $user->address = $data['address1'];
        $user->city = $data['address2'];
        $user->member_number = $this->memberNumber($user);
        $user->profile_completed_at = $user->profile_completed_at ?? now();
        $user->fill($consent)->save();
    }

    public function saveCompany(Request $request, User $user): void
    {
        if (! $user->profile_completed_at && ! $user->vendor_id) {
            throw ValidationException::withMessages(['profile' => 'Step 1 기본정보를 먼저 저장해 주세요.']);
        }
        $data = $request->validate([
            'shop_name' => 'required|string|max:255', 'shop_business_type' => 'required|string|max:50',
            'business_license_1' => ['required', 'regex:/^[0-9]{3}$/'],
            'business_license_2' => ['required', 'regex:/^[0-9]{2}$/'],
            'business_license_3' => ['required', 'regex:/^[0-9]{5}$/'],
            'email_1' => 'required|string|max:100', 'email_2' => 'required|string|max:100',
            'agree1' => 'accepted', 'bank_copy_image' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ] + $this->phoneRules() + $this->addressRules() + $this->bankRules());
        validator(['email' => $data['email_1'].'@'.$data['email_2']], ['email' => 'required|email|max:150'])->validate();
        $business = $user->vendor_id ? VendorsBusinessDetail::where('vendor_id', $user->vendor_id)->first() : null;
        if (! $request->hasFile('bank_copy_image') && ! $business?->bank_copy_image) {
            throw ValidationException::withMessages(['bank_copy_image' => '정산용 통장사본을 첨부해 주세요.']);
        }
        $newFiles = [];
        try {
            DB::transaction(function () use ($request, $user, $data, &$newFiles) {
                $vendor = $user->vendor_id ? Vendor::findOrFail($user->vendor_id) : new Vendor;
                if (! $vendor->exists) {
                    $vendor->name = $user->name;
                    $vendor->email = $user->email;
                    $vendor->mobile = $user->mobile ?: $data['mobile_1'].'-'.$data['mobile_2'].'-'.$data['mobile_3'];
                    $vendor->status = 0;
                    $vendor->confirm = 'No';
                    $vendor->commission = 0;
                }
                $vendor->company_terms_accepted_at = now();
                $vendor->save();
                $user->vendor_id = $vendor->id;
                if ($user->type !== 'vendor') { $user->type = 'company'; }
                $user->save();
                $business = VendorsBusinessDetail::where('vendor_id', $vendor->id)->first() ?? new VendorsBusinessDetail;
                $business->vendor_id = $vendor->id;
                foreach (['shop_name', 'shop_business_type', 'bank_name'] as $field) { $business->$field = $data[$field]; }
                $business->business_license_number = $data['business_license_1'].'-'.$data['business_license_2'].'-'.$data['business_license_3'];
                $business->shop_mobile = $data['mobile_1'].'-'.$data['mobile_2'].'-'.$data['mobile_3'];
                $business->shop_email = $data['email_1'].'@'.$data['email_2'];
                $business->shop_address = $data['address1'];
                $business->shop_address_detail = $data['address2'];
                $business->shop_pincode = $data['zipcode'];
                foreach (['shop_city', 'shop_state', 'shop_country', 'address_proof', 'address_proof_image', 'gst_number', 'pan_number'] as $field) {
                    $business->$field = $business->$field ?? '';
                }
                $business->bank_account_number = $data['account_number'];
                $business->bank_account_holder_name = $data['account_holder_name'];
                $this->upload($request, $business, 'bank_copy_image', $newFiles);
                $business->save();
                $this->saveBank($vendor->id, $data);
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($newFiles);
            throw $e;
        }
    }

    public function saveSeller(Request $request, User $user): bool
    {
        $business = $user->vendor_id ? VendorsBusinessDetail::where('vendor_id', $user->vendor_id)->first() : null;
        if (! $business) {
            throw ValidationException::withMessages(['company' => 'Step 2 회원사 정보를 먼저 저장해 주세요.']);
        }
        $data = $request->validate([
            'action' => 'sometimes|in:save,request', 'agree1' => 'accepted',
            'address_proof_image' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'bank_copy_image' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ] + $this->bankRules());
        $requesting = $request->input('action', 'request') === 'request';
        if ($requesting) {
            foreach (['address_proof_image' => '사업자등록증', 'bank_copy_image' => '통장사본'] as $field => $label) {
                if (! $request->hasFile($field) && ! $business->$field) {
                    throw ValidationException::withMessages([$field => $label.'을 첨부해 주세요.']);
                }
            }
        }
        $newFiles = [];
        try {
            DB::transaction(function () use ($request, $user, $business, $data, $requesting, &$newFiles) {
                foreach (['address_proof_image', 'bank_copy_image'] as $field) { $this->upload($request, $business, $field, $newFiles); }
                if ($business->address_proof_image) { $business->address_proof = 'Business License'; }
                $business->bank_name = $data['bank_name'];
                $business->bank_account_number = $data['account_number'];
                $business->bank_account_holder_name = $data['account_holder_name'];
                $business->save();
                $this->saveBank($user->vendor_id, $data);
                $vendor = Vendor::findOrFail($user->vendor_id);
                $vendor->seller_terms_accepted_at = now();
                if ($requesting) {
                    $vendor->seller_requested_at = $vendor->seller_requested_at ?? now();
                    $user->type = 'vendor';
                    $user->save();
                }
                $vendor->save();
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($newFiles);
            throw $e;
        }
        return $requesting;
    }

    private function upload(Request $request, VendorsBusinessDetail $business, string $field, array &$newFiles): void
    {
        if ($request->hasFile($field)) {
            $path = $request->file($field)->store('member-documents/'.$business->vendor_id, 'local');
            if (! $path) { throw new \RuntimeException('서류 파일을 저장하지 못했습니다.'); }
            $newFiles[] = $path;
            $business->$field = $path;
        }
    }

    private function saveBank(int $vendorId, array $data): void
    {
        $bank = VendorsBankDetail::where('vendor_id', $vendorId)->first() ?? new VendorsBankDetail;
        $bank->vendor_id = $vendorId;
        foreach (['bank_name', 'account_number', 'account_holder_name'] as $field) { $bank->$field = $data[$field]; }
        $bank->bank_ifsc_code = $bank->bank_ifsc_code ?? '';
        $bank->save();
    }

    public function document(int $vendorId, string $kind, bool $download = true)
    {
        $business = VendorsBusinessDetail::where('vendor_id', $vendorId)->first();
        $path = $business?->{ $kind === 'license' ? 'address_proof_image' : 'bank_copy_image' };
        if (! $path && $kind === 'bankbook') {
            $path = VendorsBankDetail::where('vendor_id', $vendorId)->first()?->bank_copy_image;
        }
        abort_unless(is_string($path) && $path !== '', 404);
        $name = ($kind === 'license' ? 'business-license.' : 'bankbook.').pathinfo($path, PATHINFO_EXTENSION);
        if (str_starts_with($path, 'member-documents/'.$vendorId.'/')) {
            abort_unless(Storage::disk('local')->exists($path), 404);
            $headers = ['Cache-Control'=>'private, no-store', 'X-Content-Type-Options'=>'nosniff', 'Content-Security-Policy'=>"sandbox"];
            return $download ? Storage::disk('local')->download($path, $name, $headers) : Storage::disk('local')->response($path, $name, $headers, 'inline');
        }
        abort_unless(basename($path) === $path && is_file(public_path('front/images/bank_copies/'.$path)), 404);
        $headers = ['Cache-Control'=>'private, no-store', 'X-Content-Type-Options'=>'nosniff', 'Content-Security-Policy'=>'sandbox'];
        return $download ? response()->download(public_path('front/images/bank_copies/'.$path), $name, $headers)
            : response()->file(public_path('front/images/bank_copies/'.$path), $headers);
    }
}
