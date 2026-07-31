@extends('layouts.admin')

@section('page_type', 'sub')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">{{ $settlement->shop_channel_name }} {{ $title }}</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>전체관리자</li>
                        <li>정산관리</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="btm_btn right mt10" style="margin-bottom:10px;">
                            <a href="{{ $exportRoute }}" class="btn02">엑셀출력</a>
                            <a href="javascript:window.close();" class="btn02 col5">창닫기</a>
                        </div>

                        <div class="tb01 ovS">
                            <table>
                                @if($mode === 'billing')
                                    <colgroup>
                                        <col width="150px"><col width="100px"><col width="150px"><col width="100px">
                                        <col width="130px"><col width="120px"><col width="120px"><col width="130px">
                                        <col width="130px"><col width="220px">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>등록일</th>
                                            <th>채널아이디</th>
                                            <th>주문번호</th>
                                            <th>PG구분</th>
                                            <th>상품+수수료</th>
                                            <th>배송비</th>
                                            <th>SMS 수수료</th>
                                            <th>지급포인트</th>
                                            <th>채널 청구액</th>
                                            <th>청구사유</th>
                                        </tr>
                                    </thead>
                                @else
                                    <colgroup>
                                        <col width="150px"><col width="100px"><col width="150px"><col width="100px">
                                        <col width="120px"><col width="120px"><col width="120px">
                                        <col width="120px"><col width="120px"><col width="120px"><col width="120px">
                                        <col width="130px"><col width="230px">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>등록일</th>
                                            <th>채널아이디</th>
                                            <th>주문번호</th>
                                            <th>PG구분</th>
                                            <th>자사PG 결제액</th>
                                            <th>공용PG 결제액</th>
                                            <th>자사포인트</th>
                                            <th>Me9 포인트</th>
                                            <th>상품가</th>
                                            <th>배송비</th>
                                            <th>할인금액</th>
                                            <th>채널 지급액</th>
                                            <th>지급사유</th>
                                        </tr>
                                    </thead>
                                @endif
                                <tbody>
                                    @forelse($rows as $row)
                                        <tr>
                                            @foreach($row as $index => $value)
                                                <td class="{{ is_numeric($value) ? 't_r' : '' }}">
                                                    {{ is_numeric($value) ? number_format($value) : ($value ?: '-') }}
                                                </td>
                                            @endforeach
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $mode === 'billing' ? 10 : 13 }}" class="no_data">상세 내역이 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
