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

                    <div class="conbx">
                        <div class="con_w">
                            <div class="imp_bx01">
                                <div class="txt1">
                                    {{ date('Y년 m월 d일') }}
                                    {{ Auth::user()->name ?? '홍길동' }}({{ Auth::user()->email ?? 'id1111' }})님의 <br>
                                    회원 탈퇴 신청이 완료되었습니다. <br>

                                </div>
                                <div class="txt2">
                                    그동안 Me9 market을 이용해주셔서 감사합니다.
                                </div>
                            </div>

                            <!-- 하단버튼 -->
                            <div class="btm_btn right mt10">
                                <a href="/" class="pop_btn" data-pop="pop1_1">메인으로 이동</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- //contents -->

    <script type="text/javascript">

    </script>
@endsection