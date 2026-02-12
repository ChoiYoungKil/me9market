@extends('layouts.mypage')

@section('page_type', 'main')

@section('content')
    <div id="dashboard">
        <div class="box_w">
            <div class="box box1">
                <ul class="order_list01">
                    <li class="icon0">
                        <div class="txt_w">
                            <div class="txt1">전체</div>
                            <div class="txt2"><strong>16</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon1">
                        <div class="txt_w">
                            <div class="txt1">결제완료</div>
                            <div class="txt2"><strong>5</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon2">
                        <div class="txt_w">
                            <div class="txt1">배송대기중</div>
                            <div class="txt2"><strong>1</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon3">
                        <div class="txt_w">
                            <div class="txt1">배송중</div>
                            <div class="txt2"><strong>0</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon4">
                        <div class="txt_w">
                            <div class="txt1">구매확정</div>
                            <div class="txt2"><strong>5</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon5">
                        <div class="txt_w">
                            <div class="txt1">취소요청</div>
                            <div class="txt2"><strong>2</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon6">
                        <div class="txt_w">
                            <div class="txt1">반품신청</div>
                            <div class="txt2"><strong>3</strong> 건</div>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="box box2">
                <div class="ttl01 brb">대시보드 1</div>
            </div>
            <div class="box box3 col2">
                <div class="ttl01 brb">대시보드 2</div>
            </div>
            <div class="box box4 col2 mr0">
                <div class="ttl01 brb">대시보드 1</div>
            </div>
            <div class="box box5 col3">
                <div class="ttl01 brb">대시보드 3</div>
            </div>
            <div class="box box6 col3">
                <div class="ttl01 brb">대시보드 3</div>
            </div>
            <div class="box box7 col3 mr0">
                <div class="ttl01 brb">대시보드 3</div>
            </div>
        </div>
    </div>
@endsection