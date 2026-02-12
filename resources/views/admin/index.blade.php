@extends('layouts.admin')

@section('page_type', 'main')

@section('content')
    <!-- 오른쪽 박스 -->
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

        <div class="row col2-1">
            <div class="box box2">
                <div class="con_bx">
                    <div class="con_w">
                        <div class="chart_w">
                            <canvas id="myChart1" width="200" height="200"></canvas>
                        </div>
                        <div class="txt_bx">
                            <strong>당월 매출 현황 Top 20</strong>
                            <p>수집기간 : 2025-01-01 ~ 2025-12-31</p>
                        </div>
                    </div>
                    <div class="con_w">
                        <div class="chart_w">
                            <canvas id="myChart2" width="200" height="200"></canvas>
                        </div>
                        <div class="txt_bx">
                            <strong>당월 판매 카테고리 Top 20</strong>
                            <p>수집기간 : 2025-01-01 ~ 2025-12-31</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box box3">
                <div class="ttl01">포인트 구매현황</div>
                <div class="tb01">
                    <table>
                        <colgroup>
                            <col width="13%">
                            <col width="13%">
                            <col width="">
                            <col width="18%">
                            <col width="18%">
                            <col width="13%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>포인트</th>
                                <th>판매채널</th>
                                <th>구매형태</th>
                                <th>구매정보</th>
                                <th>상태</th>
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
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="row col1-2 mr0">
            <div class="box box4">
                <div class="ttl01">회원가입현황</div>
                <div class="tb01">
                    <table>
                        <colgroup>
                            <col width="29%">
                            <col width="">
                            <col width="29%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>판매채널</th>
                                <th>아이디</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>0000-00-00</td>
                                <td>-</td>
                                <td>-</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="box box5">
                <div class="ttl01">신고현황</div>
                <div class="tb01">
                    <table>
                        <colgroup>
                            <col width="23%">
                            <col width="23%">
                            <col width="">
                            <col width="23%">
                        </colgroup>
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>신고자</th>
                                <th>판매채널</th>
                                <th>신고내용</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>0000-00-00</td>
                                <td>-</td>
                                <td>-</td>
                                <td>-</td>
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

        const ctx2 = document.getElementById('myChart2').getContext('2d');
        const myChart2 = new Chart(ctx2, {
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
    </script>
@endpush