@extends('layouts.channel')

@section('page_type', 'sub')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">상품문의 관리</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>주문관리</li>
                        <li>상품문의</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <form method="GET" action="{{ route('channel.inquiries.index') }}">
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
                                            <th class="w160"><span>처리상태</span></th>
                                            <td>
                                                <select name="status" class="w160">
                                                    <option value="">전체</option>
                                                    @foreach($statusLabels as $value => $label)
                                                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <th class="w160"><span>검색어</span></th>
                                            <td>
                                                <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="제목, 내용, 이름, 이메일" class="wFull">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="btm_btn right mt10 search-actions">
                                <button type="submit" class="type2">검색</button>
                                <a href="{{ route('channel.inquiries.index') }}" class="type2 col5">초기화</a>
                            </div>
                        </form>

                        <div class="list_top1">
                            <div class="count">총 <strong>{{ $inquiries->total() }}</strong> 건</div>
                        </div>

                        @if (Session::has('success_message'))
                            <div style="margin: 10px 0; padding: 12px; border: 1px solid #bbf7d0; background: #f0fdf4; color: #166534;">
                                {{ Session::get('success_message') }}
                            </div>
                        @endif

                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="90px">
                                    <col width="120px">
                                    <col width="140px">
                                    <col width="180px">
                                    <col width="">
                                    <col width="120px">
                                    <col width="150px">
                                    <col width="90px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>번호</th>
                                        <th>상태</th>
                                        <th>Shop채널</th>
                                        <th>상품</th>
                                        <th>제목</th>
                                        <th>문의자</th>
                                        <th>등록일</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($inquiries as $inquiry)
                                        <tr>
                                            <td>{{ $inquiry->id }}</td>
                                            <td>
                                                @php
                                                    $statusColor = $inquiry->status === 'completed' ? '#16a34a' : ($inquiry->status === 'processing' ? '#f59e0b' : '#ef4444');
                                                @endphp
                                                <span style="color: {{ $statusColor }}; font-weight: 700;">{{ $statusLabels[$inquiry->status] ?? $inquiry->status }}</span>
                                            </td>
                                            <td>{{ $inquiry->shopChannel->channel_name ?? '기본 채널' }}</td>
                                            <td class="t_l">{{ $inquiry->orderProduct->product_name ?? '-' }}</td>
                                            <td class="t_l">
                                                <a href="{{ route('channel.inquiries.show', $inquiry->id) }}" class="fcol4 link">
                                                    {{ \Illuminate\Support\Str::limit($inquiry->subject, 60) }}
                                                </a>
                                            </td>
                                            <td>{{ $inquiry->name }}</td>
                                            <td>{{ $inquiry->created_at->format('Y-m-d H:i') }}</td>
                                            <td><a href="{{ route('channel.inquiries.show', $inquiry->id) }}" class="btn02">보기</a></td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="no_data">등록된 상품문의가 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div style="margin-top: 20px;">
                            {{ $inquiries->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
