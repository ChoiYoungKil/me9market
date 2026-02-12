@extends('layouts.channel')

@section('page_type', 'main')
@php
    $dep1_id = "00";
@endphp

@section('content')
    <div id="contents">
        <div class="row">
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
        </div>
        <div class="row col4">
            <div class="box box2">
                <div class="ttl01">채널관리 정보보기</div>
                <div class="tb01">
                    <table>
                        <colgroup>
                            <col width="35%">
                            <col width="">
                        </colgroup>
                        <tbody class="textL">
                            <tr>
                                <th>회원사명</th>
                                <td>-</td>
                            </tr>
                            <tr>
                                <th>판매단계</th>
                                <td>-</td>
                            </tr>
                            <tr>
                                <th>대표연락처</th>
                                <td>010-0000-0000</td>
                            </tr>
                            <tr>
                                <th>대표이메일</th>
                                <td>text1234@naver.com</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row col4">
            <div class="box box3">
                <div class="con_bx">
                    <div class="txt_bx">
                        <strong>Shop 채널 판매현황</strong>
                        <p>수집기간 : 2025-01-01 ~ 2025-12-31</p>
                    </div>
                    <div class="chart_w">
                        <canvas id="myChart1" style="width:100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row col2 mr0">
            <div class="box box4">
                <div class="con_bx">
                    <div class="txt_bx">
                        <strong>품목별 판매현황</strong>
                        <p>수집기간 : 2025-01-01 ~ 2025-12-31</p>
                    </div>
                    <div class="chart_w">
                        <canvas id="myChart2" style="width:100%;"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="row col2-1">
            <div class="box box5">
                <div class="ttl01">주문목록</div>
                <div class="tb01">
                    <table>
                        <colgroup>
                            <col width="13%">
                            <col width="13%">
                            <col width="13%">
                            <col width="">
                            <col width="">
                            <col width="13%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>주문일</th>
                                <th>주문단계</th>
                                <th>주문형태</th>
                                <th>shop채널명</th>
                                <th>주문명</th>
                                <th>주문자</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>0000-00-00</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>0000-00-00</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                            <tr>
                                <td>0000-00-00</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row col1-2 mr0">
            <div class="box box6">
                <div class="ttl01">고객 문의사항</div>
                <div class="tb01">
                    <table>
                        <colgroup>
                            <col width="80px">
                            <col width="">
                            <col width="100px">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>번호</th>
                                <th>제목</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody class="textL">
                            <tr>
                                <td class="t_c">00</td>
                                <td class="ovH">
                                    <a href="#" class="subject on fcol1">문의 공지 입니다. 문의 공지 입니다. 문의 공지 입니다. 문의 공지 입니다. 문의 공지
                                        입니다.</a>
                                </td>
                                <td class="t_c">0000-00-00</td>
                            </tr>
                            <tr>
                                <td class="t_c">00</td>
                                <td class="ovH">
                                    <a href="#" class="subject fcol1">문의 제목입니다. 문의 제목입니다. 문의 제목입니다. 문의 제목입니다. 문의 제목입니다.</a>
                                </td>
                                <td class="t_c">0000-00-00</td>
                            </tr>
                            <tr>
                                <td class="t_c">00</td>
                                <td class="ovH">
                                    <a href="#" class="subject fcol1">문의 제목입니다. 문의 제목입니다. 문의 제목입니다. 문의 제목입니다. 문의 제목입니다.</a>
                                </td>
                                <td class="t_c">0000-00-00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
        const ctx1 = document.getElementById('myChart1').getContext('2d');
        const myChart1 = new Chart(ctx1, {
            type: 'pie',
            data: {
                labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple'],
                datasets: [{
                    label: '# of Votes',
                    data: [12, 19, 3, 5, 2],
                    backgroundColor: [
                        'rgba(220, 57, 18, 1)',
                        'rgba(51, 102, 204, 1)',
                        'rgba(255, 153, 0, 1)',
                        'rgba(16, 150, 24, 1)',
                        'rgba(153, 0, 153, 1)'
                    ]
                }]
            }
        });

        const xValues = ["1월", "2월", "3월", "4월", "5월", "6월", "7월", "8월", "9월", "10월", "11월", "12월"];

        new Chart("myChart2", {
            type: "line",
            data: {
                labels: xValues,
                datasets: [{
                    label: 'Red',
                    data: [4.3, 2.5, 3.1, 4.5, 4.2, 2.5, 3.3, 4.5, 2.3, 4.2, 1.8, 2.9],
                    borderColor: "rgba(220, 57, 18, 1)",
                    fill: false
                }, {
                    label: 'Blue',
                    data: [2.2, 4.3, 1.9, 2.9, 2, 2, 3, 5, 3.5, 4.5, 4.3, 2.6],
                    borderColor: "rgba(51, 102, 204, 1)",
                    fill: false
                }, {
                    label: 'Yellow',
                    data: [2, 2, 3, 5, 2.3, 4.3, 1.9, 3, 3.5, 4.2, 3, 5],
                    borderColor: "rgba(255, 153, 0, 1)",
                    fill: false
                }]
            },
            options: {
                legend: { display: false }
            }
        });
    </script>
@endpush