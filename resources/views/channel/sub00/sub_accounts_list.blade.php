@extends('layouts.channel')

@php
    $dep1_id = "00";
    $dep1_tit = "서브관리자관리";
@endphp

@section('page_type', 'sub')

@section('content')
<div id="contents">
    <div class="row">
        <div class="box box1">
            <div class="page_info">
                <div class="ttl">서브관리자 관리</div>
                <ul class="dep">
                    <li>HOME</li>
                    <li>서브관리자 관리</li>
                </ul>
            </div>
            <div class="conbx">
                <div class="con_w">
                    @if(session('flash_message_success'))
                        <div class="alert alert-success">{{ session('flash_message_success') }}</div>
                    @endif

                    <form method="GET" action="{{ route('channel.sub_accounts.list') }}">
                        <div class="tb01">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>검색어</span></th>
                                        <td>
                                            <input type="text" name="keyword" value="{{ $keyword }}" placeholder="회원번호, 이메일, 이름, 연락처">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="btm_btn right mt10 search-actions">
                            <button type="submit" class="type2">검색</button>
                            <a href="{{ route('channel.sub_accounts.list') }}" class="col5">초기화</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="box box1">
            <div class="conbx">
                <div class="con_w">
                    <div class="list_top1 btn">
                        <div class="count">총 <strong>{{ $accounts->total() }}</strong> 건</div>
                        <div class="btn_bx">
                            <a href="#" class="btn01 col5 pop_btn" data-pop="pop_sub_create">서브관리자 등록</a>
                        </div>
                    </div>
                    <div class="tb01 ovS">
                        <table>
                            <colgroup>
                                <col width="80px">
                                <col width="80px">
                                <col width="120px">
                                <col width="">
                                <col width="120px">
                                <col width="230px">
                                <col width="180px">
                                <col width="180px">
                            </colgroup>
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>상태</th>
                                    <th>회원번호</th>
                                    <th>이메일</th>
                                    <th>관리자명</th>
                                    <th>운영기간</th>
                                    <th>권한</th>
                                    <th>관리</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($accounts as $account)
                                    @php
                                        $permissions = collect($account->permissions ?? [])->map(fn ($value) => $permissionLabels[$value] ?? $value)->implode('<br>');
                                    @endphp
                                    <tr>
                                        <td>{{ $accounts->firstItem() + $loop->index }}</td>
                                        <td>{{ $account->admin?->status ? '운영' : '중지' }}</td>
                                        <td>{{ $account->member_no ?: '-' }}</td>
                                        <td class="t_l">{{ $account->admin?->email }}</td>
                                        <td>{{ $account->admin?->name }}</td>
                                        <td>
                                            {{ optional($account->started_at)->format('Y.m.d') ?: '-' }}
                                            ~
                                            {{ optional($account->ended_at)->format('Y.m.d') ?: '-' }}
                                        </td>
                                        <td>{!! $permissions ?: '-' !!}</td>
                                        <td>
                                            <a href="#" class="btn02 col5 pop_btn" data-pop="pop_sub_view_{{ $account->id }}">보기</a>
                                            <a href="#" class="btn02 col7 pop_btn" data-pop="pop_sub_edit_{{ $account->id }}">수정</a>
                                            <form method="POST" action="{{ route('channel.sub_accounts.delete', $account->id) }}" style="display:inline;" onsubmit="return confirm('서브관리자를 삭제하시겠습니까?');">
                                                @csrf
                                                <button type="submit" class="btn02">삭제</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="no_data" style="padding: 80px 0;">등록된 서브관리자가 없습니다.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="page_bx1">
                        {{ $accounts->links() }}
                    </div>

                    <div class="popup_bx" data-id="pop_sub_create">
                        <div class="pop_w">
                            <div class="pop_inner">
                                <div class="pop_con w800">
                                    <div class="close_btn close1">닫기</div>
                                    <div class="page_info type2">
                                        <div class="ttl">서브관리자 등록</div>
                                    </div>
                                    <form method="POST" action="{{ route('channel.sub_accounts.store') }}">
                                        @csrf
                                        @include('channel.sub00.inc.sub_account_form', ['account' => null])
                                        <div class="btm_btn mt10">
                                            <button type="submit">서브관리자 등록</button>
                                            <a href="#" class="col5 close_btn">닫기</a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    @foreach($accounts as $account)
                        @php
                            $permissions = collect($account->permissions ?? [])->map(fn ($value) => $permissionLabels[$value] ?? $value)->implode('<br>');
                        @endphp
                        <div class="popup_bx" data-id="pop_sub_view_{{ $account->id }}">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con w800">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">서브관리자 보기</div>
                                        </div>
                                        <div class="conbx">
                                            <div class="con_w">
                                                <div class="tb01">
                                                    <table>
                                                        <tbody class="textL">
                                                            <tr><th class="w160"><span>상태</span></th><td>{{ $account->admin?->status ? '운영' : '중지' }}</td></tr>
                                                            <tr><th class="w160"><span>회원번호</span></th><td>{{ $account->member_no ?: '-' }}</td></tr>
                                                            <tr><th class="w160"><span>이메일</span></th><td>{{ $account->admin?->email }}</td></tr>
                                                            <tr><th class="w160"><span>관리자명</span></th><td>{{ $account->admin?->name }}</td></tr>
                                                            <tr><th class="w160"><span>연락처</span></th><td>{{ $account->admin?->mobile ?: '-' }}</td></tr>
                                                            <tr><th class="w160"><span>이용기간</span></th><td>{{ optional($account->started_at)->format('Y.m.d') ?: '-' }} ~ {{ optional($account->ended_at)->format('Y.m.d') ?: '-' }}</td></tr>
                                                            <tr><th class="w160"><span>권한</span></th><td>{!! $permissions ?: '-' !!}</td></tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="btm_btn mt10">
                                            <a href="#" class="col5 close_btn">닫기</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="popup_bx" data-id="pop_sub_edit_{{ $account->id }}">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con w800">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">서브관리자 수정</div>
                                        </div>
                                        <form method="POST" action="{{ route('channel.sub_accounts.update', $account->id) }}">
                                            @csrf
                                            @include('channel.sub00.inc.sub_account_form', ['account' => $account])
                                            <div class="btm_btn mt10">
                                                <button type="submit">서브관리자 수정</button>
                                                <a href="#" class="col5 close_btn">닫기</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script type="text/javascript">
    $(".pop_btn").click(function () {
        var popId = $(this).attr("data-pop");
        $(".popup_bx[data-id='" + popId + "']").stop().fadeIn(300);
        $(".popup_bx[data-id='" + popId + "']").scrollTop(0);
        return false;
    });
    $(".popup_bx .close_btn").click(function () {
        $(this).parents(".popup_bx").stop().fadeOut(300);
        return false;
    });

    $(".datepicker").datepicker({
        dateFormat: 'yy-mm-dd',
        showOtherMonths: true,
        showMonthAfterYear: true,
        changeYear: true,
        changeMonth: true,
        yearSuffix: "년",
        monthNamesShort: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        monthNames: ['1월', '2월', '3월', '4월', '5월', '6월', '7월', '8월', '9월', '10월', '11월', '12월'],
        dayNamesMin: ['일', '월', '화', '수', '목', '금', '토'],
        dayNames: ['일요일', '월요일', '화요일', '수요일', '목요일', '금요일', '토요일'],
        minDate: "-5y",
        maxDate: "+5y"
    });
</script>
@endpush
