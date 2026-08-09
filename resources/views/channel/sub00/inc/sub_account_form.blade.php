@php
    $account = $account ?? null;
    $admin = $account?->admin;
    $selectedPermissions = old('permissions', $account->permissions ?? ['shop', 'product', 'order']);
@endphp

<div class="conbx">
    <div class="con_w">
        <div class="ttl01">회원정보</div>
        <div class="tb01">
            <table>
                <colgroup>
                    <col width="160px">
                    <col width="">
                    <col width="160px">
                    <col width="">
                </colgroup>
                <tbody class="textL">
                    <tr>
                        <th class="w160"><span>상태</span></th>
                        <td colspan="3">
                            <ul class="chk01">
                                <li>
                                    <input type="radio" name="status" id="sub_status_{{ $account->id ?? 'new' }}_1" value="1" {{ old('status', $admin->status ?? 1) == 1 ? 'checked' : '' }}>
                                    <label for="sub_status_{{ $account->id ?? 'new' }}_1">운영</label>
                                </li>
                                <li>
                                    <input type="radio" name="status" id="sub_status_{{ $account->id ?? 'new' }}_0" value="0" {{ old('status', $admin->status ?? 1) == 0 ? 'checked' : '' }}>
                                    <label for="sub_status_{{ $account->id ?? 'new' }}_0">중지</label>
                                </li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th class="w160"><span>회원번호</span></th>
                        <td><input type="text" name="member_no" value="{{ old('member_no', $account->member_no ?? '') }}"></td>
                        <th class="w160"><span>이메일<em>필수</em></span></th>
                        <td><input type="email" name="email" value="{{ old('email', $admin->email ?? '') }}" required="required"></td>
                    </tr>
                    <tr>
                        <th class="w160"><span>관리자명<em>필수</em></span></th>
                        <td><input type="text" name="name" value="{{ old('name', $admin->name ?? '') }}" required="required"></td>
                        <th class="w160"><span>연락처</span></th>
                        <td><input type="text" name="mobile" value="{{ old('mobile', $admin->mobile ?? '') }}"></td>
                    </tr>
                    <tr>
                        <th class="w160"><span>비밀번호{{ $account ? '' : ' 필수' }}</span></th>
                        <td colspan="3">
                            <input type="password" name="password" {{ $account ? '' : 'required=required' }}>
                            @if($account)
                                <span class="fs fcol6">미입력 시 기존 비밀번호 유지</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="con_w">
        <div class="ttl01">서브관리정보</div>
        <div class="tb01">
            <table>
                <colgroup>
                    <col width="160px">
                    <col width="">
                    <col width="160px">
                    <col width="">
                </colgroup>
                <tbody class="textL">
                    <tr>
                        <th class="w160"><span>이용기간</span></th>
                        <td colspan="3">
                            <input class="datepicker w160" type="text" name="started_at" value="{{ old('started_at', optional($account?->started_at)->format('Y-m-d')) }}" readonly="">
                            &nbsp;&nbsp;~&nbsp;&nbsp;
                            <input class="datepicker w160" type="text" name="ended_at" value="{{ old('ended_at', optional($account?->ended_at)->format('Y-m-d')) }}" readonly="">
                        </td>
                    </tr>
                    <tr>
                        <th class="w160"><span>권한 선택</span></th>
                        <td colspan="3">
                            <ul class="chk02">
                                @foreach($permissionLabels as $value => $label)
                                    <li>
                                        <input type="checkbox" name="permissions[]" id="sub_permission_{{ $account->id ?? 'new' }}_{{ $value }}" value="{{ $value }}" {{ in_array($value, $selectedPermissions, true) ? 'checked' : '' }}>
                                        <label for="sub_permission_{{ $account->id ?? 'new' }}_{{ $value }}">{{ $label }}</label>
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
