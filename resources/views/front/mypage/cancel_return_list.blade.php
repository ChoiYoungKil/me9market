@extends('layouts.mypage')

@section('page_type', 'sub')

@section('content')
<div id="contents">
    <div class="row">
        <div class="box box1">
            <div class="page_info" style="padding: 20px 0; border-bottom: 1px solid #eee; margin-bottom: 30px;">
                <h2 class="ttl" style="font-size: 24px; font-weight: 700; color: #111;">취소/반품/교환 내역</h2>
                <ul class="dep" style="display: flex; gap: 8px; font-size: 14px; color: #777; list-style: none; padding: 0; margin: 10px 0 0 0;">
                    <li>HOME</li>
                    <li>&gt;</li>
                    <li>마이페이지</li>
                    <li>&gt;</li>
                    <li>취소/반품/교환 내역</li>
                </ul>
            </div>

            <div class="conbx">
                <div class="tab_bx1" style="margin-bottom: 25px;">
                    <ul style="display: flex; border-bottom: 1px solid #ddd; padding: 0; margin: 0; list-style: none;">
                        <li style="margin-right: 5px;">
                            <a href="{{ route('mypage.order.cancel_return_list', ['type' => 'all']) }}" 
                               style="display: block; padding: 12px 24px; font-weight: 600; text-decoration: none; border: 1px solid #ddd; border-bottom: none; border-radius: 8px 8px 0 0; {{ $filterType == 'all' ? 'background: #fff; color: #6366f1; border-color: #ddd; border-bottom: 2px solid #fff; margin-bottom: -1px;' : 'background: #f8fafc; color: #64748b; border-color: transparent;' }}">전체보기</a>
                        </li>
                        <li style="margin-right: 5px;">
                            <a href="{{ route('mypage.order.cancel_return_list', ['type' => 'cancel']) }}" 
                               style="display: block; padding: 12px 24px; font-weight: 600; text-decoration: none; border: 1px solid #ddd; border-bottom: none; border-radius: 8px 8px 0 0; {{ $filterType == 'cancel' ? 'background: #fff; color: #6366f1; border-color: #ddd; border-bottom: 2px solid #fff; margin-bottom: -1px;' : 'background: #f8fafc; color: #64748b; border-color: transparent;' }}">취소 내역</a>
                        </li>
                        <li style="margin-right: 5px;">
                            <a href="{{ route('mypage.order.cancel_return_list', ['type' => 'return']) }}" 
                               style="display: block; padding: 12px 24px; font-weight: 600; text-decoration: none; border: 1px solid #ddd; border-bottom: none; border-radius: 8px 8px 0 0; {{ $filterType == 'return' ? 'background: #fff; color: #6366f1; border-color: #ddd; border-bottom: 2px solid #fff; margin-bottom: -1px;' : 'background: #f8fafc; color: #64748b; border-color: transparent;' }}">반품 내역</a>
                        </li>
                        <li style="margin-right: 5px;">
                            <a href="{{ route('mypage.order.cancel_return_list', ['type' => 'exchange']) }}" 
                               style="display: block; padding: 12px 24px; font-weight: 600; text-decoration: none; border: 1px solid #ddd; border-bottom: none; border-radius: 8px 8px 0 0; {{ $filterType == 'exchange' ? 'background: #fff; color: #6366f1; border-color: #ddd; border-bottom: 2px solid #fff; margin-bottom: -1px;' : 'background: #f8fafc; color: #64748b; border-color: transparent;' }}">교환 내역</a>
                        </li>
                        <li style="margin-right: 5px;">
                            <a href="{{ route('mypage.order.cancel_return_list', ['type' => 'confirm']) }}" 
                               style="display: block; padding: 12px 24px; font-weight: 600; text-decoration: none; border: 1px solid #ddd; border-bottom: none; border-radius: 8px 8px 0 0; {{ $filterType == 'confirm' ? 'background: #fff; color: #6366f1; border-color: #ddd; border-bottom: 2px solid #fff; margin-bottom: -1px;' : 'background: #f8fafc; color: #64748b; border-color: transparent;' }}">구매확정 내역</a>
                        </li>
                    </ul>
                </div>

                <div class="tb01">
                    <table style="width: 100%; border-collapse: collapse; border-top: 2px solid #333;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #cbd5e1;">
                                <th style="padding: 15px; font-size: 13px; font-weight: 600; text-align: center; width: 180px;">신청/확정일자</th>
                                <th style="padding: 15px; font-size: 13px; font-weight: 600; text-align: left;">상품정보</th>
                                <th style="padding: 15px; font-size: 13px; font-weight: 600; text-align: center; width: 80px;">수량</th>
                                <th style="padding: 15px; font-size: 13px; font-weight: 600; text-align: right; width: 120px;">금액</th>
                                <th style="padding: 15px; font-size: 13px; font-weight: 600; text-align: center; width: 100px;">구분</th>
                                <th style="padding: 15px; font-size: 13px; font-weight: 600; text-align: center; width: 120px;">처리상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $item)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 20px 15px; text-align: center; font-size: 13px; color: #666; line-height: 1.5;">
                                    {{ $item['date'] }}<br>
                                    <strong style="color: #6366f1;">{{ $item['order_no'] }}</strong>
                                </td>
                                <td style="padding: 20px 15px; text-align: left;">
                                    <strong style="font-size: 15px; color: #111;">{{ $item['product_name'] }}</strong>
                                    <span style="font-size: 13px; color: #777; display: block; margin-top: 4px;">옵션: {{ $item['option'] }}</span>
                                </td>
                                <td style="padding: 20px 15px; text-align: center; font-size: 14px;">{{ $item['qty'] }}개</td>
                                <td style="padding: 20px 15px; text-align: right; font-size: 14px; font-weight: 600; color: #333;">{{ number_format($item['price']) }} 원</td>
                                <td style="padding: 20px 15px; text-align: center;">
                                    @if($item['type_raw'] == 'cancel')
                                        <span style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;">{{ $item['type'] }}</span>
                                    @elseif($item['type_raw'] == 'return')
                                        <span style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;">{{ $item['type'] }}</span>
                                    @elseif($item['type_raw'] == 'exchange')
                                        <span style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;">{{ $item['type'] }}</span>
                                    @else
                                        <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: 600;">{{ $item['type'] }}</span>
                                    @endif
                                </td>
                                <td style="padding: 20px 15px; text-align: center; font-size: 14px; font-weight: 600; color: #475569;">
                                    {{ $item['status'] }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" style="padding: 40px; text-align: center; color: #888; font-size: 14px;">접수된 내역이 존재하지 않습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
