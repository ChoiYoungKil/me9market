<?php

namespace App\Services;

use App\Models\{Admin, Distributor, User, Vendor, VendorsBusinessDetail};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StoryboardAccounts
{
    /** Read existing server records only; do not provision accounts or reset passwords. */
    public function forRequest(Request $request, string $frontUrl, string $adminUrl): array
    {
        $request->validate([
            'account_search'=>'nullable|string|max:100',
            'member_id'=>'nullable|integer|min:1', 'social_id'=>'nullable|integer|min:1',
            'channel_member_id'=>'nullable|integer|min:1', 'distributor_id'=>'nullable|integer|min:1',
            'admin_id'=>'nullable|integer|min:1',
        ]);
        $users = User::query()->where('status', 1)->select(['id','name','username','email','status','type','vendor_id','member_number']);
        [$members, $member] = $this->select($request, (clone $users)->whereDoesntHave('socialAccounts'), 'member_id');
        [$socials, $social] = $this->select($request, (clone $users)->whereHas('socialAccounts')->with('socialAccounts:id,user_id,provider'), 'social_id');
        $channelQuery = (clone $users)->whereIn('type', ['company','vendor'])
            ->whereIn('vendor_id', Vendor::where('status', 1)->whereNotNull('company_terms_accepted_at')->select('id'))
            ->whereIn('vendor_id', Admin::where('type', 'vendor')->where('status', 1)->where('confirm', '!=', 'No')->select('vendor_id'));
        [$channelMembers, $channelMember] = $this->select($request, $channelQuery, 'channel_member_id', $member?->id);
        [$distributors, $distributor] = $this->select($request, Distributor::where('status', 1)
            ->select(['id','name','email','status','access_started_at','access_ended_at']), 'distributor_id');
        [$admins, $admin] = $this->select($request, Admin::whereIn('type', ['admin','superadmin'])->where('status', 1)
            ->select(['id','name','email','type','status']), 'admin_id');
        $owner = $channelMember ? Admin::where('vendor_id', $channelMember->vendor_id)->where('type', 'vendor')->where('status', 1)->where('confirm', '!=', 'No')->first(['id','name','email']) : null;
        $businessName = $channelMember ? VendorsBusinessDetail::where('vendor_id', $channelMember->vendor_id)->value('shop_name') : null;
        $cards = [
            ['member','일반 검수회원',$member,$members,'member_id',$frontUrl.'/member/login','기존 비밀번호로 로그인','현재 서버에 등록된 활성 일반 가입 회원입니다.'],
            ['social','소셜 검수회원',$social,$socials,'social_id',$frontUrl.'/member/login','소셜 인증 사용','현재 서버에 소셜 계정이 연결된 회원입니다. 연결된 제공자의 로그인 버튼을 사용하세요.'],
            ['channel','채널 관리자 (회원 연결)',$channelMember,$channelMembers,'channel_member_id',$frontUrl.'/mypage/main','해당 회원의 기존 로그인 사용','회원·회원사·채널 관리자 연결이 있는 계정입니다. 해당 회원 로그인 후 채널관리자 버튼으로 진입합니다.'],
            ['distributor','발주사',$distributor,$distributors,'distributor_id',$frontUrl.'/distributor/login','기존 비밀번호로 로그인','현재 서버에 등록된 활성 발주사 계정입니다.'],
            ['superadmin','전체 관리자',$admin,$admins,'admin_id',$adminUrl.'/admin/login','기존 비밀번호로 로그인','현재 서버에 등록된 활성 전체 관리자 계정입니다.'],
        ];
        $accounts = collect($cards)->map(function ($card) use ($owner, $businessName) {
            [$key,$title,$record,$options,$parameter,$loginUrl,$authMethod,$hint] = $card;
            $configuredPassword = $key === 'social' ? '' : (string) config('storyboard.passwords.'.$key, config('storyboard.test_password'));
            $storedHash = $record && $configuredPassword !== '' ? $record->newQuery()->whereKey($record->id)->value('password') : null;
            $passwordMatches = $storedHash && Hash::check($configuredPassword, $storedHash);
            return [
                'key'=>$key, 'title'=>$title, 'id'=>$record?->id,
                'login_id'=>$record ? ($record->username ?: $record->email) : '등록된 계정 없음',
                'name'=>$record?->name, 'email'=>$record?->email, 'member_number'=>$record?->member_number,
                'exists'=>(bool) $record, 'active'=>$record && ($record instanceof Distributor ? $record->canAccess() : (int) $record->status === 1),
                'providers'=>$key === 'social' && $record ? $record->socialAccounts->pluck('provider')->map(fn ($provider) => ['google'=>'Google','naver'=>'네이버'][$provider] ?? $provider)->implode(', ') : '',
                'vendor_id'=>$key === 'channel' ? $record?->vendor_id : null,
                'business_name'=>$key === 'channel' ? $businessName : null,
                'owner_email'=>$key === 'channel' ? $owner?->email : null,
                'options'=>$options, 'parameter'=>$parameter, 'login_url'=>$loginUrl,
                'auth_method'=>$authMethod, 'hint'=>$hint,
                'password'=>$passwordMatches ? $configuredPassword : '',
                'password_configured'=>$configuredPassword !== '', 'password_matches'=>(bool) $passwordMatches,
            ];
        });
        return compact('accounts','member','channelMember');
    }

    private function select(Request $request, Builder $query, string $parameter, ?int $preferredId = null): array
    {
        $search = trim((string) $request->input('account_search', ''));
        $options = (clone $query)->when($search !== '', fn ($q) => $q->where(fn ($fields) => $fields
            ->where('name', 'like', '%'.$search.'%')->orWhere('email', 'like', '%'.$search.'%')))
            ->orderBy('id')->limit(100)->get();
        $selected = $request->filled($parameter) ? (clone $query)->whereKey($request->integer($parameter))->firstOrFail()
            : ($options->firstWhere('id', $preferredId) ?? $options->first());
        if ($selected && ! $options->contains('id', $selected->id)) $options->prepend($selected);
        return [$options,$selected];
    }
}
