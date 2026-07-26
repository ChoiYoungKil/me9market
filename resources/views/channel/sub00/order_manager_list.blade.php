@extends('layouts.channel')

@php
    $dep1_id = "00";
    $dep1_tit = "발주담당관리";
@endphp

@section('page_type', 'sub')

@section('content')
            <style>
                .order-manager-actions {
                    display: flex;
                    justify-content: center;
                    gap: 4px;
                    flex-wrap: wrap;
                }

                .order-manager-actions form {
                    display: inline-block;
                }

                .order-manager-actions button.btn02,
                .btm_btn button {
                    border: 0;
                    cursor: pointer;
                    font-family: inherit;
                }
            </style>
			<div id="contents">
                <div class="row">
                    <div class="box box1">
                        <div class="page_info">
                            <div class="ttl">발주담당 관리</div>
                            <ul class="dep">
                                <li>HOME</li>
                                <li>발주담당 관리</li>
                            </ul>
                        </div>
                        <div class="conbx">
                            <div class="con_w">
                                <form method="GET" action="{{ route('channel.order.manager') }}">
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
                                                    <th class="w160"><span>이메일/담당자</span></th>
                                                    <td colspan="3">
                                                        <input type="text" name="keyword" value="{{ $keyword ?? '' }}" class="wFull">
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="btm_btn right mt10 search-actions">
                                        <button type="submit" class="type2">검색</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="box box1">
                        <div class="conbx">
                            <div class="con_w">
                                @if(session('flash_message_success'))
                                    <div class="alert alert-success">{{ session('flash_message_success') }}</div>
                                @endif
                                @if($errors->any())
                                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                                @endif

                                <div class="list_top1 btn">
                                    <div class="count">총 <strong>{{ $managers->count() }}</strong> 건</div>
                                    <div class="btn_bx">
                                        <a href="#" class="btn01 col5 pop_btn" data-pop="pop_create">발주담당 등록</a>
                                    </div>
                                </div>
                                <div class="tb01 ovS">
                                    <table>
                                        <colgroup>
                                            <col width="80px">
                                            <col width="80px">
                                            <col width="">
                                            <col width="120px">
                                            <col width="230px">
                                            <col width="150px">
                                            <col width="210px">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th>No</th>
                                                <th>상태</th>
                                                <th>이메일</th>
                                                <th>발주담당자</th>
                                                <th>운영기간</th>
                                                <th>관리상품수</th>
                                                <th>관리</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($managers as $index => $manager)
                                                <tr>
                                                    <td>{{ $index + 1 }}</td>
                                                    <td>{{ (int) $manager->status === 1 ? '운영' : '중지' }}</td>
                                                    <td>{{ $manager->email }}</td>
                                                    <td>{{ $manager->name }}</td>
                                                    <td>{{ optional($manager->created_at)->format('Y.m.d H시') }} ~ 상시</td>
                                                    <td>{{ number_format($manager->products_count) }}</td>
                                                    <td>
                                                        <div class="order-manager-actions">
                                                            <a href="#" class="btn02 col5 pop_btn" data-pop="pop_view_{{ $manager->id }}">보기</a>
                                                            <a href="#" class="btn02 col7 pop_btn" data-pop="pop_edit_{{ $manager->id }}">수정</a>
                                                            <form method="POST" action="{{ route('channel.order.manager.portal', $manager->id) }}">
                                                                @csrf
                                                                <button type="submit" class="btn02">발주확인</button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="no_data" style="padding: 80px 0;">등록된 발주담당자가 없습니다.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                <div class="page_bx1">
                                    <a href="#" class="page_first">first</a>
                                    <a href="#" class="page_prev">prev</a>
                                    <a href="#" class="num on">1</a>
                                    <a href="#" class="page_next">next</a>
                                    <a href="#" class="page_last">last</a>
                                </div>

                                <div class="popup_bx" data-id="pop_create">
                                    <div class="pop_w">
                                        <div class="pop_inner">
                                            <div class="pop_con w800">
                                                <div class="close_btn close1">닫기</div>
                                                <div class="page_info type2">
                                                    <div class="ttl">발주담당자 등록</div>
                                                </div>
                                                <form method="POST" action="{{ route('channel.order.manager.store') }}">
                                                    @csrf
                                                    <div class="conbx">
                                                        <div class="con_w">
                                                            <div class="tb01">
                                                                <table>
                                                                    <colgroup>
                                                                        <col width="140px">
                                                                        <col width="">
                                                                        <col width="140px">
                                                                        <col width="">
                                                                    </colgroup>
                                                                    <tbody class="textL">
                                                                        <tr>
                                                                            <th class="w160"><span>상태</span></th>
                                                                            <td colspan="3">
                                                                                <ul class="chk01">
                                                                                    <li>
                                                                                        <input type="radio" name="status" id="create_status_1" value="1" checked>
                                                                                        <label for="create_status_1">사용</label>
                                                                                    </li>
                                                                                    <li>
                                                                                        <input type="radio" name="status" id="create_status_0" value="0">
                                                                                        <label for="create_status_0">중지</label>
                                                                                    </li>
                                                                                </ul>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="w160"><span>이메일<em>필수</em></span></th>
                                                                            <td colspan="3">
                                                                                <input type="email" name="email" value="{{ old('email') }}" class="wFull" required>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="w160"><span>발주담당자<em>필수</em></span></th>
                                                                            <td colspan="3">
                                                                                <input type="text" name="name" value="{{ old('name') }}" class="wFull" required>
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="w160"><span>연락처</span></th>
                                                                            <td colspan="3">
                                                                                <input type="text" name="phone" value="{{ old('phone') }}" class="w300">
                                                                            </td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="w160"><span>비밀번호</span></th>
                                                                            <td colspan="3">
                                                                                <input type="password" name="password" value="" class="w300" placeholder="미입력 시 123456">
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="btm_btn mt10">
                                                        <button type="submit">발주담당 등록</button>
                                                        <a href="#" class="col5 close_btn">닫기</a>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @foreach($managers as $manager)
                                    <div class="popup_bx" data-id="pop_view_{{ $manager->id }}">
                                        <div class="pop_w">
                                            <div class="pop_inner">
                                                <div class="pop_con w800">
                                                    <div class="close_btn close1">닫기</div>
                                                    <div class="page_info type2">
                                                        <div class="ttl">발주담당 상세보기</div>
                                                    </div>
                                                    <div class="conbx">
                                                        <div class="con_w">
                                                            <div class="ttl01">발주담당상태</div>
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
                                                                            <th class="w160"><span>상태</span></th>
                                                                            <td>{{ (int) $manager->status === 1 ? '운영' : '중지' }}</td>
                                                                            <th class="w160"><span>관리상품수</span></th>
                                                                            <td>{{ number_format($manager->products_count) }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="w160"><span>운영기간</span></th>
                                                                            <td colspan="3">{{ optional($manager->created_at)->format('Y.m.d H시') }} ~ 상시</td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="con_w">
                                                            <div class="ttl01">발주담당정보</div>
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
                                                                            <th class="w160"><span>이메일</span></th>
                                                                            <td colspan="3">{{ $manager->email }}</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th class="w160"><span>담당자 명</span></th>
                                                                            <td>{{ $manager->name }}</td>
                                                                            <th class="w160"><span>연락처</span></th>
                                                                            <td>{{ $manager->phone ?: '-' }}</td>
                                                                        </tr>
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

                                    <div class="popup_bx" data-id="pop_edit_{{ $manager->id }}">
                                        <div class="pop_w">
                                            <div class="pop_inner">
                                                <div class="pop_con w800">
                                                    <div class="close_btn close1">닫기</div>
                                                    <div class="page_info type2">
                                                        <div class="ttl">발주담당자 수정</div>
                                                    </div>
                                                    <form method="POST" action="{{ route('channel.order.manager.update', $manager->id) }}">
                                                        @csrf
                                                        <div class="conbx">
                                                            <div class="con_w">
                                                                <div class="tb01">
                                                                    <table>
                                                                        <colgroup>
                                                                            <col width="140px">
                                                                            <col width="">
                                                                            <col width="140px">
                                                                            <col width="">
                                                                        </colgroup>
                                                                        <tbody class="textL">
                                                                            <tr>
                                                                                <th class="w160"><span>상태</span></th>
                                                                                <td colspan="3">
                                                                                    <ul class="chk01">
                                                                                        <li>
                                                                                            <input type="radio" name="status" id="edit_status_{{ $manager->id }}_1" value="1" @checked((int) $manager->status === 1)>
                                                                                            <label for="edit_status_{{ $manager->id }}_1">사용</label>
                                                                                        </li>
                                                                                        <li>
                                                                                            <input type="radio" name="status" id="edit_status_{{ $manager->id }}_0" value="0" @checked((int) $manager->status === 0)>
                                                                                            <label for="edit_status_{{ $manager->id }}_0">중지</label>
                                                                                        </li>
                                                                                    </ul>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th class="w160"><span>이메일<em>필수</em></span></th>
                                                                                <td colspan="3">
                                                                                    <input type="email" name="email" value="{{ old('email', $manager->email) }}" class="wFull" required>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th class="w160"><span>발주담당자<em>필수</em></span></th>
                                                                                <td colspan="3">
                                                                                    <input type="text" name="name" value="{{ old('name', $manager->name) }}" class="wFull" required>
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th class="w160"><span>연락처</span></th>
                                                                                <td colspan="3">
                                                                                    <input type="text" name="phone" value="{{ old('phone', $manager->phone) }}" class="w300">
                                                                                </td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th class="w160"><span>비밀번호 변경</span></th>
                                                                                <td colspan="3">
                                                                                    <input type="password" name="password" value="" class="w300" placeholder="변경할 때만 입력">
                                                                                </td>
                                                                            </tr>
                                                                        </tbody>
                                                                    </table>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="btm_btn mt10">
                                                            <button type="submit">발주담당 수정</button>
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
            $(".pop_btn").click(function(){
                var popId = $(this).attr("data-pop");
                $(".popup_bx[data-id='"+popId+"']").stop().fadeIn(300);
                $(".popup_bx[data-id='"+popId+"']").scrollTop(0);

                return false;
            });
            $(".popup_bx .close_btn").click(function(){
                $(this).parents(".popup_bx").stop().fadeOut(300);

                return false;
            });
        </script>
@endpush
