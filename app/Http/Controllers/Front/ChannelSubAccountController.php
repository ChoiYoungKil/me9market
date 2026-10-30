<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\ChannelSubAccount;
use App\Models\User;
use App\Services\ChannelSubAccounts;
use App\Services\MemberRegistration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChannelSubAccountController extends Controller
{
    public function index(Request $request, ChannelSubAccounts $service)
    {
        $owner = Auth::guard('admin')->user();
        $data = $request->validate(['keyword' => 'nullable|string|max:100']);
        $keyword = trim($data['keyword'] ?? '');
        $accounts = ChannelSubAccount::with(['admin', 'member'])->where('vendor_id', $owner->vendor_id)
            ->when($keyword !== '', fn ($q) => $q->where(fn ($q) => $q->where('member_no', 'like', '%'.$keyword.'%')
                ->orWhereHas('member', fn ($q) => $q->where('name', 'like', '%'.$keyword.'%')->orWhere('username', 'like', '%'.$keyword.'%')->orWhere('email', 'like', '%'.$keyword.'%'))
                ->orWhereHas('admin', fn ($q) => $q->where('name', 'like', '%'.$keyword.'%')->orWhere('email', 'like', '%'.$keyword.'%')->orWhere('mobile', 'like', '%'.$keyword.'%'))))
            ->latest('id')->paginate(20)->withQueryString();
        $permissionLabels = ChannelSubAccounts::PERMISSIONS;
        $oldSelectedMember = null;
        if (is_string(old('member_token')) && old('member_token') !== '') {
            try {
                $oldSelectedMember = $service->verifiedMember($owner, old('member_token'));
            } catch (ValidationException $e) {
            }
        }

        $activeForm = is_string(old('form_key')) && preg_match('/^(create|edit-\d+)$/', old('form_key')) ? old('form_key') : '';

        return view('channel.sub00.sub_accounts_list', compact('accounts', 'keyword', 'permissionLabels', 'service', 'owner', 'oldSelectedMember', 'activeForm'));
    }

    public function lookup(Request $request, ChannelSubAccounts $service)
    {
        $data = $request->validate(['search_type' => 'required|in:member_no,login_id,name', 'keyword' => 'required|string|max:100']);
        $keyword = trim($data['keyword']);
        $members = User::where('status', 1)
            ->where(function ($q) use ($data, $keyword) {
                if ($data['search_type'] === 'member_no') {
                    $q->where('member_number', $keyword);
                } elseif ($data['search_type'] === 'name') {
                    $q->where('name', $keyword);
                } else {
                    $q->where('username', $keyword)->orWhere('email', $keyword);
                }
            })->limit(10)->get();
        if ($members->isEmpty() && $data['search_type'] === 'member_no' && preg_match('/^M9-(\d{6})-(\d{4,})$/', $keyword, $parts)) {
            $date = \DateTimeImmutable::createFromFormat('!ymd', $parts[1]);
            if ($date && $date->format('ymd') === $parts[1] && (int) $parts[2] > 0) {
                $legacy = User::whereDate('created_at', $date->format('Y-m-d'))->orderBy('id')->skip((int) $parts[2] - 1)->first();
                if ($legacy && (int) $legacy->status === 1 && ! $legacy->member_number && app(MemberRegistration::class)->memberNumber($legacy) === $keyword) {
                    $members = collect([$legacy]);
                }
            }
        }
        $owner = Auth::guard('admin')->user();

        return response()->json(['members' => $members->map(fn ($member) => [
            'id' => $member->id, 'member_no' => app(MemberRegistration::class)->memberNumber($member), 'login_id' => $member->username ?: $member->email,
            'name' => $member->name, 'token' => $service->proof($owner, $member),
        ]), 'message' => $members->isEmpty() ? '일치하는 회원이 없습니다. 회원번호 또는 아이디를 확인해 주세요.' : null]);
    }

    public function store(Request $request, ChannelSubAccounts $service)
    {
        $service->save($request, Auth::guard('admin')->user());

        return redirect()->route('channel.sub_accounts.list')->with('flash_message_success', '서브관리자가 등록되었습니다.');
    }

    public function update(Request $request, int $id, ChannelSubAccounts $service)
    {
        $owner = Auth::guard('admin')->user();
        $account = ChannelSubAccount::with('admin')->where('vendor_id', $owner->vendor_id)->findOrFail($id);
        $service->save($request, $owner, $account);

        return redirect()->route('channel.sub_accounts.list')->with('flash_message_success', '서브관리자가 수정되었습니다.');
    }

    public function destroy(int $id)
    {
        $account = ChannelSubAccount::with('admin')->where('vendor_id', Auth::guard('admin')->user()->vendor_id)->findOrFail($id);
        DB::transaction(function () use ($account) {
            $admin = $account->admin;
            $account->delete();
            if ($admin?->type === 'subadmin') {
                $admin->delete();
            }
        });

        return redirect()->route('channel.sub_accounts.list')->with('flash_message_success', '서브관리자가 삭제되었습니다.');
    }
}
