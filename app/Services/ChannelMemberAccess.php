<?php

namespace App\Services;

use App\Models\Admin;
use App\Models\User;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ChannelMemberAccess
{
    public function administratorFor(User $user): Admin
    {
        if ((int) $user->status !== 1 || $user->temporaryPasswordIsExpired()) {
            throw ValidationException::withMessages(['login_id'=>'이용할 수 없는 회원 계정입니다.']);
        }
        $portal = app(MemberPortal::class);
        $admin = null;
        if ($user->vendor_id && in_array($user->type, ['company','vendor'], true)) {
            try {
                $admin = $portal->owner($user);
            } catch (HttpExceptionInterface $exception) {
                if ($exception->getStatusCode() !== 403) throw $exception;
            }
        }
        if (! $admin) {
            $assignments = $portal->subAccounts($user);
            if ($assignments->count() > 1) {
                throw ValidationException::withMessages(['login_id'=>'연결된 관리채널이 여러 개입니다. 회원 로그인 후 마이페이지의 서브관리 채널목록에서 선택해 주세요.']);
            }
            $admin = $assignments->first()?->admin;
        }
        if (! $admin || (int) $admin->status !== 1 || $admin->confirm === 'No') {
            $hasAssignment = \App\Models\ChannelSubAccount::where(fn($q)=>$q->where('user_id',$user->id)
                ->when($user->member_number,fn($q)=>$q->orWhere(fn($legacy)=>$legacy->whereNull('user_id')->where('member_no',$user->member_number))))->exists();
            if ($hasAssignment) {
                throw ValidationException::withMessages(['login_id'=>'서브관리자 이용기간 또는 중지 상태를 확인해 주세요.']);
            }
            throw ValidationException::withMessages(['login_id'=>'채널관리자 권한을 확인해 주세요. 회원사 가입 정보 또는 배정된 관리채널이 필요합니다.']);
        }
        return $admin;
    }
}
