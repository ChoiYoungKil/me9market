@extends('layouts.mypage')

@section('page_type', 'main')

@section('content')
    <div id="dashboard">
        <div class="box_w">
            <div class="box box1">
                <ul class="order_list01">
                    <li class="icon0">
                        <div class="txt_w">
                            <div class="txt1">전체 주문</div>
                            <div class="txt2"><strong>{{ $ordersCount }}</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon4">
                        <div class="txt_w">
                            <div class="txt1">구매확정</div>
                            <div class="txt2"><strong>{{ $confirmedCount }}</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon5">
                        <div class="txt_w">
                            <div class="txt1">취소요청</div>
                            <div class="txt2"><strong>{{ $cancelCount }}</strong> 건</div>
                        </div>
                    </li>
                    <li class="icon6">
                        <div class="txt_w">
                            <div class="txt1">반품신청</div>
                            <div class="txt2"><strong>{{ $returnCount }}</strong> 건</div>
                        </div>
                    </li>
                </ul>
            </div>
            
            <!-- 나의 제휴 및 상품 문의 내역 -->
            <div class="box box3 col2" style="width: 100%; float: none; margin-top: 20px; box-sizing: border-box; background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; display: block;">
                <div class="ttl01 brb" style="font-size: 18px; font-weight: bold; border-bottom: 2px solid #6366f1; padding-bottom: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; width: 100%;">
                    <span>💬 나의 제휴 및 상품 문의 내역</span>
                    <a href="/contact" style="font-size: 13px; color: #6366f1; text-decoration: none; font-weight: normal;">+ 신규 제휴/문의 작성</a>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; font-size: 13px; color: #475569; font-weight: 600;">
                                <th style="padding: 12px; width: 120px;">등록일시</th>
                                <th style="padding: 12px; width: 100px;">유형</th>
                                <th style="padding: 12px;">제목 / 문의내용</th>
                                <th style="padding: 12px; text-align: center; width: 100px;">답변상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inquiries as $inq)
                            <tr style="border-bottom: 1px solid #eee;">
                                <td style="padding: 15px 12px; font-size: 13px; color: #64748b;">
                                    {{ $inq->created_at->format('Y-m-d') }}
                                </td>
                                <td style="padding: 15px 12px;">
                                    @if($inq->type == 'partnership')
                                        <span style="background: rgba(99, 102, 241, 0.1); color: #6366f1; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">제휴/문의</span>
                                    @else
                                        <span style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">상품문의</span>
                                    @endif
                                </td>
                                <td style="padding: 15px 12px; text-align: left;">
                                    <div style="font-weight: 600; color: #1e293b; font-size: 14px;">{{ $inq->subject }}</div>
                                    <div style="font-size: 13px; color: #475569; margin-top: 4px; line-height: 1.4;">{{ $inq->message }}</div>
                                    @if($inq->admin_reply)
                                        <div style="background: #ecfdf5; border-left: 3px solid #10b981; padding: 10px 15px; margin-top: 10px; font-size: 13px; color: #065f46; border-radius: 0 8px 8px 0; border-top: 1px solid #d1fae5; border-right: 1px solid #d1fae5; border-bottom: 1px solid #d1fae5;">
                                            <strong style="color: #047857;">↳ 답변:</strong> {{ $inq->admin_reply }} 
                                            @if($inq->replied_at)
                                                <span style="font-size: 11px; color: #6b7280; margin-left: 10px;">({{ $inq->replied_at->format('Y-m-d H:i') }})</span>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td style="padding: 15px 12px; text-align: center;">
                                    @if($inq->status == 'completed')
                                        <span style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">답변완료</span>
                                    @elseif($inq->status == 'processing')
                                        <span style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">처리중</span>
                                    @else
                                        <span style="background: rgba(148, 163, 184, 0.1); color: #64748b; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: bold;">접수대기</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" style="padding: 40px; text-align: center; color: #888; font-size: 13px;">등록된 문의사항이 없습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
        </div>
    </div>
@endsection