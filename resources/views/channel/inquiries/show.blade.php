@extends('layouts.channel')

@section('page_type', 'sub')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">상품문의 상세</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>주문관리</li>
                        <li>상품문의 상세</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        @if (Session::has('success_message'))
                            <div style="margin-bottom: 12px; padding: 12px; border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534;">
                                {{ Session::get('success_message') }}
                            </div>
                        @endif

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
                                        <th><span>처리상태</span></th>
                                        <td>{{ $statusLabels[$inquiry->status] ?? $inquiry->status }}</td>
                                        <th><span>등록일</span></th>
                                        <td>{{ $inquiry->created_at->format('Y-m-d H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <th><span>Shop채널</span></th>
                                        <td>{{ $inquiry->shopChannel->channel_name ?? '기본 채널' }}</td>
                                        <th><span>주문번호</span></th>
                                        <td>Me9-{{ str_pad($inquiry->order_id, 8, '0', STR_PAD_LEFT) }}</td>
                                    </tr>
                                    <tr>
                                        <th><span>상품명</span></th>
                                        <td>{{ $inquiry->orderProduct->product_name ?? '-' }}</td>
                                        <th><span>옵션</span></th>
                                        <td>{{ $inquiry->orderProduct ? trim(($inquiry->orderProduct->product_color ?: '-') . ' / ' . ($inquiry->orderProduct->product_size ?: '-')) : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th><span>문의자</span></th>
                                        <td>{{ $inquiry->name }}</td>
                                        <th><span>연락처</span></th>
                                        <td>{{ $inquiry->phone ?: '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th><span>이메일</span></th>
                                        <td colspan="3">{{ $inquiry->email }}</td>
                                    </tr>
                                    <tr>
                                        <th><span>제목</span></th>
                                        <td colspan="3">{{ $inquiry->subject }}</td>
                                    </tr>
                                    <tr>
                                        <th><span>문의내용</span></th>
                                        <td colspan="3" style="white-space: pre-line; line-height: 1.7;">{{ $inquiry->message }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <form method="POST" action="{{ route('channel.inquiries.reply', $inquiry->id) }}">
                            @csrf
                            <div class="tb01 mt20">
                                <table>
                                    <colgroup>
                                        <col width="160px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th><span>답변상태</span></th>
                                            <td>
                                                <select name="status" required>
                                                    <option value="processing" @selected(old('status', $inquiry->status) === 'processing')>처리중</option>
                                                    <option value="completed" @selected(old('status', $inquiry->status === 'pending' ? 'completed' : $inquiry->status) === 'completed')>답변완료</option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><span>답변내용</span></th>
                                            <td>
                                                <textarea name="admin_reply" required style="width: 100%; min-height: 180px; padding: 12px; border: 1px solid #ddd; box-sizing: border-box;">{{ old('admin_reply', $inquiry->admin_reply) }}</textarea>
                                                @error('admin_reply')
                                                    <p style="margin-top: 6px; color: #ef4444;">{{ $message }}</p>
                                                @enderror
                                            </td>
                                        </tr>
                                        @if($inquiry->replied_at)
                                            <tr>
                                                <th><span>최근 답변일</span></th>
                                                <td>{{ $inquiry->replied_at->format('Y-m-d H:i') }}</td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                            <div class="btm_btn right mt20">
                                <button type="submit" style="height: 40px; padding: 0 22px; border: 0; background: #3470f7; color: #fff; font-weight: 700; cursor: pointer;">답변저장</button>
                                <a href="{{ route('channel.inquiries.index') }}">목록</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
