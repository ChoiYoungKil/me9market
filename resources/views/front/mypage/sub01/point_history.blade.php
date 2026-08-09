@extends('layouts.mypage')

@section('page_type', 'sub')
@section('dep1_id', '01')
@section('dep2_id', '3')
@section('dep3_id', '2')

@section('content')
    <div id="contents">
        <div id="">
            <div class="box_w">
                <div class="box box1">
                    <!-- 페이지 정보 -->
                    <div class="page_info">
                        <div class="ttl">포인트 이력</div>
                        <ul class="dep">
                            <li>HOME</li>
                            <li>포인트관리</li>
                            <li>포인트 이력</li>
                        </ul>
                    </div>

                    <div class="conbx">
                        <div class="con_w">
                            <form action="{{ route('mypage.point.history') }}" method="get">
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
                                                <th class="w160"><span>등록 기간</span></th>
                                                <td colspan="3">
                                                    <div class="date_bx">
                                                        <input class="datepicker" type="text" name="start_date" value="{{ $filters['start_date'] ?? '' }}" readonly="">
                                                        <span>~</span>
                                                        <input class="datepicker" type="text" name="end_date" value="{{ $filters['end_date'] ?? '' }}" readonly="">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>Shop 채널</span></th>
                                                <td>
                                                    <input type="text" name="shop_channel" value="{{ $filters['shop_channel'] ?? '' }}">
                                                </td>
                                                <th class="w160"><span>채널 코드</span></th>
                                                <td>
                                                    <input type="text" name="channel_code" value="{{ $filters['channel_code'] ?? '' }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>포인트 구분</span></th>
                                                <td>
                                                    <input type="text" name="point_type" value="{{ $filters['point_type'] ?? '' }}" placeholder="적립 또는 소진">
                                                </td>
                                                <th class="w160"><span>포인트 범위</span></th>
                                                <td>
                                                    <div class="scope01">
                                                        <input type="text" name="point_min" value="{{ $filters['point_min'] ?? '' }}">
                                                        <span class="mid">~</span>
                                                        <input type="text" name="point_max" value="{{ $filters['point_max'] ?? '' }}">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>상세 내용</span></th>
                                                <td colspan="3">
                                                    <input type="text" name="detail" value="{{ $filters['detail'] ?? '' }}">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="btm_btn right mt10">
                                    <button type="submit" class="btn01" style="background-color: #000; border-color: #000; color:#fff; border:0; cursor:pointer;">검색</button>
                                </div>
                            </form>
                        </div>

                        <div class="con_w mt40">
                            <div class="tb01 type2">
                                <table>
                                    <colgroup>
                                        <col style="width: 60px;">
                                        <col style="width: 120px;">
                                        <col style="width: 150px;">
                                        <col style="width: 150px;">
                                        <col style="width: 100px;">
                                        <col style="width: 120px;">
                                        <col>
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th style="text-align: center;">No</th>
                                            <th style="text-align: center;">등록일</th>
                                            <th style="text-align: center;">Shop 채널</th>
                                            <th style="text-align: center;">Shop 채널 코드</th>
                                            <th style="text-align: center;">구분</th>
                                            <th style="text-align: center;">포인트</th>
                                            <th style="text-align: center;">상세 내용</th>
                                        </tr>
                                    </thead>
                                    <tbody style="text-align: center;">
                                        @forelse($pointHistory as $history)
                                            <tr>
                                                <td>{{ $history['no'] }}</td>
                                                <td>{{ $history['created_at'] }}</td>
                                                <td>{{ $history['channel_name'] }}</td>
                                                <td>{{ $history['channel_code'] }}</td>
                                                <td>{{ $history['type'] }}</td>
                                                <td style="color: {{ $history['points'] > 0 ? '#4caf50' : '#f44336' }}; font-weight: bold;">
                                                    {{ $history['points'] > 0 ? '+' : '' }}{{ number_format($history['points']) }}
                                                </td>
                                                <td style="text-align: left; padding-left: 20px;">{{ $history['description'] }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="no_data" style="padding: 100px 0; color: #aaaaaa;">포인트 이력 내역이 없습니다.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="page_bx1 text-center mt30">
                                <a href="javascript:void(0);" class="page_first"></a>
                                <a href="javascript:void(0);" class="page_prev"></a>
                                <a href="javascript:void(0);" class="num on">1</a>
                                <a href="javascript:void(0);" class="num">2</a>
                                <a href="javascript:void(0);" class="num">3</a>
                                <a href="javascript:void(0);" class="num">4</a>
                                <a href="javascript:void(0);" class="num">5</a>
                                <a href="javascript:void(0);" class="num">6</a>
                                <a href="javascript:void(0);" class="num">7</a>
                                <a href="javascript:void(0);" class="num">8</a>
                                <a href="javascript:void(0);" class="num">9</a>
                                <a href="javascript:void(0);" class="num">10</a>
                                <a href="javascript:void(0);" class="page_next"></a>
                                <a href="javascript:void(0);" class="page_last"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- //contents -->
@endsection

@push('scripts')
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            /* 달력 */
            $(".datepicker").datepicker({
                dateFormat: 'yy-mm-dd', //달력 날짜 형태
                showOtherMonths: true, //빈 공간에 현재월의 앞뒤월의 날짜를 표시
                showMonthAfterYear: true, // 월- 년 순서가아닌 년도 - 월 순서
                changeYear: true, //option값 년 선택 가능
                changeMonth: true, //option값  월 선택 가능                      
                yearSuffix: "년", //달력의 년도 부분 뒤 텍스트
                monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'], //달력의 월 부분 텍스트
                monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'], //달력의 월 부분 Tooltip
                dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'], //달력의 요일 텍스트
                dayNames: ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'], //달력의 요일 Tooltip
                minDate: "-5y", //최소 선택일자(-1D:하루전, -1M:한달전, -1Y:일년전)
                maxDate: "+5y", //최대 선택일자(+1D:하루후, -1M:한달후, -1Y:일년후)  
            });
        });
    </script>
@endpush
