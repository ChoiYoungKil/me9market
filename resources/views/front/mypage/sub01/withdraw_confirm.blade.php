@extends('layouts.mypage')

@section('page_type', 'sub')
@section('dep1_id', '01')
@section('dep2_id', '1')
@section('dep3_id', '3')

@section('content')
    <div id="contents">
        <div id="">
            <div class="box_w">
                <div class="box box1">
                    <!-- 페이지 정보 -->
                    <div class="page_info">
                        <div class="ttl">회원 탈퇴 신청</div>
                        <ul class="dep">
                            <li>HOME</li>
                            <li>정보관리</li>
                            <li>회원 탈퇴 신청</li>
                        </ul>
                    </div>

                    <div class="ttl01">탈퇴 정보 확인</div>

                    <div class="conbx">
                        <div class="con_w">
                            <div class="tb01">
                                <table class="two">
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>회원번호</span></th>
                                            <td>{{ $user->member_number ?? $user->id }}</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>아이디</span></th>
                                            <td>{{ $user->username ?? $user->email }}</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>이름</span></th>
                                            <td>{{ $user->name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>보유 포인트</span></th>
                                            <td>{{ number_format($pointBalance) }}point</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>판매 중 내역</span></th>
                                            <td>{{ number_format($sellingCount) }}건</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <form action="{{ route('mypage.withdraw.submit') }}" method="POST" id="withdrawForm">
                    @csrf
                    <div class="box box2">
                        <div class="ttl01">탈퇴하시는 이유는 무엇인가요?</div>

                        <div class="conbx">
                            <div class="con_w">
                                <div class="tb01 type2">
                                    <table class="two">
                                        <tbody class="textL">
                                            <tr>
                                                <td>
                                                    <ul class="chk01 disb">
                                                        @foreach (config('array.withdraw_reasons') as $key => $reason)
                                                            @php
                                                                $isOther = $key === 'reason_other';
                                                            @endphp
                                                            <li @if ($isOther) class="dotTop" @endif>
                                                                <input type="radio" name="reason" value="{{ $key }}"
                                                                    id="radio1_{{ $loop->iteration }}"
                                                                    @checked(old('reason', 'reason_1') === $key)>
                                                                <label @if ($isOther) class="w100p" @endif
                                                                    for="radio1_{{ $loop->iteration }}">
                                                                    {{ $reason }}
                                                                </label>
                                                                    @if ($isOther)
                                                                        <textarea class="mt5" name="reason_detail" aria-label="기타 탈퇴 사유" maxlength="2000" placeholder="사유를 입력해 주세요.">{{ old('reason_detail') }}</textarea>
                                                                    @endif
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
                    </div>

                    <div class="box box3">
                        <div class="ttl01">비밀번호 입력</div>

                        <div class="conbx">
                            <div class="con_w">
                                <div class="tb01 type2">
                                    <table class="two">
                                        <tbody class="textL">
                                            <tr>
                                                <th class="w160"><span>비밀번호</span></th>
                                                <td>
                                                    <input class="w200" type="password" name="password" aria-label="비밀번호" autocomplete="current-password" required="required">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>비밀번호 확인</span></th>
                                                <td>
                                                    <input class="w200" type="password" name="password_confirmation" aria-label="비밀번호 확인"
                                                        autocomplete="current-password" required="required">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- 하단버튼 -->
                        <div class="btm_btn right mt10">
                            <div class="page_bx1"></div>
                            <button type="button" class="pop_btn btn_submit" data-pop="pop1_1">회원 탈퇴 신청하기</button>
                        </div>
                    </div>
                </form>

                <!-- 회원 탈퇴 신청하기 팝업 -->
                <div class="popup_bx" data-id="pop1_1">
                    <div class="pop_w">
                        <div class="pop_inner">
                            <div class="pop_con w560">
                                <button type="button" class="close_btn close1" aria-label="닫기">닫기</button>
                                <div class="conbx">
                                    <div class="con_w">
                                        <div class="imp_bx01 bN">
                                            <div class="txt2 mt0">탈퇴 처리 시 현재 보유 포인트는 모두 삭제 됩니다. <br>내 상품을 다른 회원사에서 판매 중일
                                                때는 탈퇴가 거부 됩니다.</div>
                                        </div>
                                    </div>
                                </div>

                                <!-- 하단버튼 -->
                                <div class="btm_btn mt10">
                                    <button type="submit" form="withdrawForm" class="btn_submit">확인</button>
                                    <a href="{{ url()->current() }}" class="col5 close_btn">취소</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- //contents -->

    <script type="text/javascript">
        $("#withdrawForm input[name=password_confirmation], #withdrawForm input[name=password]").on('input', function () {
            document.querySelector('#withdrawForm input[name=password_confirmation]').setCustomValidity('');
        });
        /* 팝업 */
        $(".pop_btn").click(function () {
            var form = document.getElementById('withdrawForm');
            var other = form.querySelector('input[name="reason"][value="reason_other"]');
            var detail = form.querySelector('textarea[name="reason_detail"]');
            detail.required = other.checked;
            var pwConf = form.querySelector('input[name="password_confirmation"]');
            pwConf.setCustomValidity(pwConf.value !== form.querySelector('input[name="password"]').value ? '비밀번호 확인이 일치하지 않습니다.' : '');
            if (!form.reportValidity()) return false;

            var popId = $(this).attr("data-pop");
            $(".popup_bx[data-id='" + popId + "']").stop().fadeIn(300);
            $(".popup_bx[data-id='" + popId + "']").scrollTop(0);

            return false;
        });
        $(".popup_bx .close_btn").click(function () {
            $(this).parents(".popup_bx").stop().fadeOut(300);

            return false;
        });
    </script>
@endsection
