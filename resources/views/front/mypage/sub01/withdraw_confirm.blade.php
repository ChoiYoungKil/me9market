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

                    @if(Session::has('error_message'))
                        <div class="alert alert-danger" style="color:red; margin-bottom:10px;">
                            <strong>Error:</strong> {{ Session::get('error_message') }}
                        </div>
                    @endif
                    @if(Session::has('success_message'))
                        <div class="alert alert-success" style="color:green; margin-bottom:10px;">
                            <strong>Success:</strong> {{ Session::get('success_message') }}
                        </div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger" style="color:red; margin-bottom:10px;">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

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
                                            <td>{{ number_format($user->point ?? 0) }}point</td>
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
                                                                    @if ($loop->first) checked="" @endif>
                                                                <label @if ($isOther) class="w100p" @endif
                                                                    for="radio1_{{ $loop->iteration }}">
                                                                    {{ $reason }}
                                                                    @if ($isOther)
                                                                        <textarea class="mt5" name="reason_detail"></textarea>
                                                                    @endif
                                                                </label>
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
                                                    <input class="w200" type="password" name="password" required="required">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>비밀번호 확인</span></th>
                                                <td>
                                                    <input class="w200" type="password" name="password_confirmation"
                                                        required="required">
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
                            <a href="javascript:void(0);" class="pop_btn" data-pop="pop1_1">회원 탈퇴 신청하기</a>
                        </div>
                    </div>
                </form>

                <!-- 회원 탈퇴 신청하기 팝업 -->
                <div class="popup_bx" data-id="pop1_1">
                    <div class="pop_w">
                        <div class="pop_inner">
                            <div class="pop_con w560">
                                <div class="close_btn close1">닫기</div>
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
                                    <a href="javascript:;"
                                        onclick="document.getElementById('withdrawForm').submit();">확인</a>
                                    <a href="javascript:void(0);" class="col5 close_btn">닫기</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- //contents -->

    <script type="text/javascript">
        /* 팝업 */
        $(".pop_btn").click(function () {
            // 유효성 검사 추가
            var pw = $('input[name="password"]').val();
            var pwConf = $('input[name="password_confirmation"]').val();

            if (!pw) {
                alert('비밀번호를 입력해주세요.');
                $('input[name="password"]').focus();
                return false;
            }

            if (pw !== pwConf) {
                alert('비밀번호가 일치하지 않습니다.');
                $('input[name="password_confirmation"]').focus();
                return false;
            }

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