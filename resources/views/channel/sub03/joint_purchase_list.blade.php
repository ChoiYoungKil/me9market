@extends('layouts.channel')

@section('page_type', 'sub')
@php
    $dep1_id = "03";
@endphp

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">공동구매 상품 목록</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>공동구매 관리</li>
                        <li>공동구매 상품 목록</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1" style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 10px;">
                            <div class="count">총 <strong>{{ $jointPurchases->total() }}</strong> 건</div>
                            <a href="{{ route('channel.joint_purchase.create') }}" class="btn02">공동구매 상품등록</a>
                        </div>

                        @if(session('success_message'))
                            <div style="background: #e2f0d9; color: #385723; padding: 10px; margin-bottom: 15px; border-radius: 4px; font-weight: bold;">
                                {{ session('success_message') }}
                            </div>
                        @endif

                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="80px">
                                    <col width="150px">
                                    <col width="">
                                    <col width="120px">
                                    <col width="120px">
                                    <col width="180px">
                                    <col width="100px">
                                    <col width="150px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>상품코드</th>
                                        <th>상품명</th>
                                        <th>목표수량</th>
                                        <th>현재수량</th>
                                        <th>할인가격</th>
                                        <th>기간</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody class="textL">
                                    @forelse($jointPurchases as $jp)
                                        <tr>
                                            <td class="t_c">{{ $jp->id }}</td>
                                            <td class="t_c">{{ $jp->product_code ?? '-' }}</td>
                                            <td><strong>{{ $jp->product_name ?? '삭제된 상품' }}</strong></td>
                                            <td class="t_c">{{ number_format($jp->min_quantity) }}개</td>
                                            <td class="t_c">{{ number_format($jp->current_quantity) }}개</td>
                                            <td class="t_c" style="color: #d90000; font-weight: bold;">{{ number_format($jp->discount_price) }}원</td>
                                            <td class="t_c">{{ $jp->start_date }} ~ {{ $jp->end_date }}</td>
                                            <td class="t_c">
                                                <a href="{{ route('channel.order.joint_list', ['search_type' => 'product_code', 'keyword' => $jp->product_code, 'detail_open' => 1]) }}" class="btn02">주문내역</a>
                                                <a href="{{ route('channel.joint_purchase.edit', ['id' => $jp->id]) }}" class="btn02 col7">수정</a>
                                                <a href="/shop-channel/joint-purchases/{{ $jp->id }}" class="btn02 col5" target="_blank">상세화면</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="t_c" style="padding: 50px 0;">등록된 공동구매 상품이 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="btm_btn mt10" style="display: flex; justify-content: center;">
                            {{ $jointPurchases->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
