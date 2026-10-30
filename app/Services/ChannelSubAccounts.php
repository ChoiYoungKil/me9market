<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\ChannelSubAccount;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ChannelSubAccounts
{
    public const PERMISSIONS = ['shop' => 'Shop 채널', 'product' => '상품관리', 'joint_purchase' => '공동상품관리', 'order' => '주문관리', 'settings' => '설정관리'];

    public function proof(Admin $owner, User $member): string
    {
        return Crypt::encryptString(json_encode(['owner' => $owner->id, 'vendor' => $owner->vendor_id, 'member' => $member->id, 'expires' => now()->addMinutes(30)->timestamp]));
    }

    public function verifiedMember(Admin $owner, string $proof): User
    {
        try {
            $data = json_decode(Crypt::decryptString($proof), true, 512, JSON_THROW_ON_ERROR);
            if (($data['owner'] ?? null) !== $owner->id || (int) ($data['vendor'] ?? 0) !== (int) $owner->vendor_id || ($data['expires'] ?? 0) < now()->timestamp) {
                throw new \RuntimeException;
            }
            $member = User::findOrFail($data['member']);
            if ((int) $member->status !== 1) {
                throw new \RuntimeException;
            }

            return $member;
        } catch (\Throwable $exception) {
            throw ValidationException::withMessages(['member_token' => '회원찾기를 통해 유효한 회원을 다시 선택해 주세요.']);
        }
    }

    public function save(Request $request, Admin $owner, ?ChannelSubAccount $account = null): ChannelSubAccount
    {
        $data = $request->validate([
            'member_token' => 'required|string', 'member_id' => 'required|integer', 'status' => 'required|in:0,1',
            'started_at' => 'bail|required|string|date', 'ended_at' => 'bail|required|string|date',
            'permissions' => 'required|array|min:1', 'permissions.*' => ['string', Rule::in(array_keys(self::PERMISSIONS))],
        ], ['member_token.required' => '회원찾기로 회원을 선택해 주세요.', 'permissions.required' => '관리 권한을 하나 이상 선택해 주세요.', 'ended_at.after_or_equal' => '종료일시는 시작일시 이후로 입력해 주세요.']);
        $request->validate(['ended_at' => 'after_or_equal:started_at'], ['ended_at.after_or_equal' => '종료일시는 시작일시 이후로 입력해 주세요.']);
        $member = $this->verifiedMember($owner, $data['member_token']);
        if ($member->id !== (int) $data['member_id']) {
            throw ValidationException::withMessages(['member_id' => '검색한 회원 정보가 일치하지 않습니다. 회원찾기를 다시 진행해 주세요.']);
        }
        if ((int) $member->vendor_id === (int) $owner->vendor_id && in_array($member->type, ['vendor', 'company'], true)) {
            throw ValidationException::withMessages(['member_id' => '채널 대표 회원은 서브관리자로 중복 등록할 수 없습니다.']);
        }

        return DB::transaction(function () use ($data, $member, $owner, $account) {
            User::whereKey($member->id)->lockForUpdate()->firstOrFail();
            $duplicate = ChannelSubAccount::where('vendor_id', $owner->vendor_id)
                ->when($account, fn ($q) => $q->where('id', '!=', $account->id))
                ->where(fn ($q) => $q->where('user_id', $member->id)->orWhere(fn ($legacy) => $legacy->whereNull('user_id')->where('member_no', app(MemberRegistration::class)->memberNumber($member))))->exists();
            if ($duplicate) {
                throw ValidationException::withMessages(['member_id' => '이미 등록된 서브관리자 회원입니다. 기존 정보를 수정해 주세요.']);
            }
            $admin = $account?->admin;
            if (! $admin) {
                $admin = new Admin;
                $admin->type = 'subadmin';
                $admin->vendor_id = $owner->vendor_id;
                // Members authenticate with their existing member credentials. This internal identity has no shared password.
                $admin->email = 'sub-'.Str::uuid().'@sub.me9.invalid';
                $admin->password = Hash::make(Str::random(64));
                $admin->confirm = 'Yes';
            }
            $admin->name = $member->name;
            $admin->mobile = $member->mobile ?? '';
            $admin->status = (int) $data['status'];
            $admin->save();
            $account ??= new ChannelSubAccount;
            $account->fill(['vendor_id' => $owner->vendor_id, 'admin_id' => $admin->id, 'user_id' => $member->id, 'member_no' => app(MemberRegistration::class)->memberNumber($member),
                'started_at' => $data['started_at'], 'ended_at' => $data['ended_at'], 'permissions' => array_values(array_unique($data['permissions']))])->save();

            return $account;
        });
    }
}
