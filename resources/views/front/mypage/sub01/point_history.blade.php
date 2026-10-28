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
                                                        <input class="member-date-control" type="date" aria-label="등록 시작일" name="start_date" value="{{ $filters['start_date'] ?? '' }}">
                                                        <span>~</span>
                                                        <input class="member-date-control" type="date" aria-label="등록 종료일" name="end_date" value="{{ $filters['end_date'] ?? '' }}">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>Shop 채널</span></th>
                                                <td>
                                                    <input type="text" aria-label="Shop 채널" name="shop_channel" value="{{ $filters['shop_channel'] ?? '' }}">
                                                </td>
                                                <th class="w160"><span>채널 코드</span></th>
                                                <td>
                                                    <input type="text" aria-label="채널 코드" name="channel_code" value="{{ $filters['channel_code'] ?? '' }}">
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>포인트 구분</span></th>
                                                <td>
                                                    <select name="point_type" aria-label="포인트 구분"><option value="">전체</option><option value="earn" @selected(in_array($filters['point_type'] ?? '', ['적립','earn','in']))>적립</option><option value="use" @selected(in_array($filters['point_type'] ?? '', ['소진','use','out']))>소진</option></select>
                                                </td>
                                                <th class="w160"><span>포인트 범위</span></th>
                                                <td>
                                                    <div class="scope01">
                                                        <input type="number" aria-label="최소 포인트" name="point_min" value="{{ $filters['point_min'] ?? '' }}">
                                                        <span class="mid">~</span>
                                                        <input type="number" aria-label="최대 포인트" name="point_max" value="{{ $filters['point_max'] ?? '' }}">
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="w160"><span>상세 내용</span></th>
                                                <td colspan="3">
                                                    <input type="text" aria-label="상세 내용" name="detail" value="{{ $filters['detail'] ?? '' }}">
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="btm_btn right mt10">
                                    <button type="submit" class="btn_submit">검색</button><a href="{{ route('mypage.point.history') }}" class="col3">초기화</a>
                                </div>
                            </form>
                        </div>

                        <div class="con_w mt40"><p class="member-help-text">총 {{ $pointHistory->total() }}건</p>
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
                            
                            <div class="portal-pagination">{{ $pointHistory->links() }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- //contents -->
@endsection
