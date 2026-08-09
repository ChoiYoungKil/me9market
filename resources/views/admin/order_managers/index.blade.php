@extends('layouts.admin')

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

        .order-manager-actions button,
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
                    <div class="ttl">발주사 관리</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>전체관리자</li>
                        <li>발주사 관리</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        @if(session('success_message'))
                            <div style="background:#e8f5e9; color:#1b5e20; padding:12px; margin-bottom:15px; border-radius:4px;">
                                {{ session('success_message') }}
                            </div>
                        @endif
                        @if($errors->any())
                            <div style="background:#ffebee; color:#b71c1c; padding:12px; margin-bottom:15px; border-radius:4px;">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form method="GET" action="{{ route('admin.order_managers.index') }}" class="tb01" style="margin-bottom:15px;">
                            <table>
                                <colgroup>
                                    <col width="160px">
                                    <col width="">
                                    <col width="160px">
                                    <col width="">
                                    <col width="120px">
                                </colgroup>
                                <tbody class="textL">
                                    <tr>
                                        <th class="w160"><span>검색어</span></th>
                                        <td>
                                            <input type="text" name="keyword" value="{{ $keyword }}" class="wFull" placeholder="이메일 / 담당자 / 연락처">
                                        </td>
                                        <th class="w160"><span>상태</span></th>
                                        <td>
                                            <select name="status" class="w160">
                                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>전체</option>
                                                <option value="1" {{ $status === '1' ? 'selected' : '' }}>운영</option>
                                                <option value="0" {{ $status === '0' ? 'selected' : '' }}>중지</option>
                                            </select>
                                        </td>
                                        <td class="t_c">
                                            <button type="submit" class="btn02 col5">검색</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </form>

                        <div class="list_top1 list-top-split">
                            <div class="count">총 <strong>{{ number_format($managers->total()) }}</strong> 건</div>
                            <div class="right_bx list-top-actions">
                                <div class="r_btn_w">
                                    <a href="#" class="btn02 col5 pop_btn" data-pop="pop_create">발주사 등록</a>
                                </div>
                            </div>
                        </div>

                        <div class="tb01 ovS">
                            <table>
                                <colgroup>
                                    <col width="80px">
                                    <col width="90px">
                                    <col width="220px">
                                    <col width="">
                                    <col width="140px">
                                    <col width="130px">
                                    <col width="130px">
                                    <col width="160px">
                                    <col width="380px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>상태</th>
                                        <th>발주사 로그인 정보</th>
                                        <th>발주사/담당자</th>
                                        <th>연락처</th>
                                        <th>관리상품수</th>
                                        <th>발주품목수</th>
                                        <th>등록일</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($managers as $manager)
                                        <tr>
                                            <td>{{ $managers->firstItem() + $loop->index }}</td>
                                            <td>{{ (int) $manager->status === 1 ? '운영' : '중지' }}</td>
                                            <td class="textL">
                                                <div>URL: {{ route('distributor.login') }}</div>
                                                <div>ID: {{ $manager->email }}</div>
                                                <div>PW: 등록/수정 또는 초기화로 관리</div>
                                            </td>
                                            <td>{{ $manager->name }}</td>
                                            <td>{{ $manager->phone ?: '-' }}</td>
                                            <td class="t_r">{{ number_format($manager->products_count) }}</td>
                                            <td class="t_r">{{ number_format($manager->orders_products_count) }}</td>
                                            <td>{{ optional($manager->created_at)->format('Y-m-d H:i') }}</td>
                                            <td class="t_c">
                                                <div class="order-manager-actions">
                                                    <a href="#" class="btn02 col5 pop_btn" data-pop="pop_edit_{{ $manager->id }}">수정</a>
                                                    <form method="POST" action="{{ route('admin.order_managers.reset_password', $manager->id) }}" onsubmit="return confirm('비밀번호를 123456으로 초기화하시겠습니까?');">
                                                        @csrf
                                                        <button type="submit" class="btn02 col5">PW초기화</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.order_managers.portal', $manager->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="destination" value="pending">
                                                        <button type="submit" class="btn02">발주대기</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.order_managers.portal', $manager->id) }}">
                                                        @csrf
                                                        <input type="hidden" name="destination" value="completed">
                                                        <button type="submit" class="btn02">발주완료</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('admin.order_managers.destroy', $manager->id) }}" onsubmit="return confirm('발주사를 삭제하면 연결된 상품/주문 배정이 해제됩니다. 삭제하시겠습니까?');">
                                                        @csrf
                                                        <button type="submit" class="btn02 col7">삭제</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="no_data">등록된 발주사가 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="page_bx1">
                            {{ $managers->links() }}
                        </div>

                        <div class="popup_bx" data-id="pop_create">
                            <div class="pop_w">
                                <div class="pop_inner">
                                    <div class="pop_con w800">
                                        <div class="close_btn close1">닫기</div>
                                        <div class="page_info type2">
                                            <div class="ttl">발주사 등록</div>
                                        </div>
                                        <form method="POST" action="{{ route('admin.order_managers.store') }}">
                                            @csrf
                                            <div class="conbx">
                                                <div class="con_w">
                                                    <div class="tb01">
                                                        <table>
                                                            <colgroup>
                                                                <col width="160px">
                                                                <col width="">
                                                            </colgroup>
                                                            <tbody class="textL">
                                                                <tr>
                                                                    <th class="w160"><span>상태</span></th>
                                                                    <td>
                                                                        <ul class="chk01">
                                                                            <li>
                                                                                <input type="radio" name="status" id="create_status_1" value="1" checked>
                                                                                <label for="create_status_1">운영</label>
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
                                                                    <td><input type="email" name="email" value="{{ old('email') }}" class="wFull" required></td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>발주사/담당자<em>필수</em></span></th>
                                                                    <td><input type="text" name="name" value="{{ old('name') }}" class="wFull" required></td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>연락처</span></th>
                                                                    <td><input type="text" name="phone" value="{{ old('phone') }}" class="w300"></td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>비밀번호</span></th>
                                                                    <td><input type="password" name="password" value="" class="w300" placeholder="미입력 시 123456"></td>
                                                                </tr>
                                                                <tr>
                                                                    <th class="w160"><span>발주사 로그인 URL</span></th>
                                                                    <td>{{ route('distributor.login') }}</td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="btm_btn mt10">
                                                <button type="submit">발주사 등록</button>
                                                <a href="#" class="col5 close_btn">닫기</a>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @foreach($managers as $manager)
                            <div class="popup_bx" data-id="pop_edit_{{ $manager->id }}">
                                <div class="pop_w">
                                    <div class="pop_inner">
                                        <div class="pop_con w800">
                                            <div class="close_btn close1">닫기</div>
                                            <div class="page_info type2">
                                                <div class="ttl">발주사 수정</div>
                                            </div>
                                            <form method="POST" action="{{ route('admin.order_managers.update', $manager->id) }}">
                                                @csrf
                                                <div class="conbx">
                                                    <div class="con_w">
                                                        <div class="tb01">
                                                            <table>
                                                                <colgroup>
                                                                    <col width="160px">
                                                                    <col width="">
                                                                </colgroup>
                                                                <tbody class="textL">
                                                                    <tr>
                                                                        <th class="w160"><span>상태</span></th>
                                                                        <td>
                                                                            <ul class="chk01">
                                                                                <li>
                                                                                    <input type="radio" name="status" id="edit_status_{{ $manager->id }}_1" value="1" @checked((int) $manager->status === 1)>
                                                                                    <label for="edit_status_{{ $manager->id }}_1">운영</label>
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
                                                                        <td><input type="email" name="email" value="{{ old('email', $manager->email) }}" class="wFull" required></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>발주사/담당자<em>필수</em></span></th>
                                                                        <td><input type="text" name="name" value="{{ old('name', $manager->name) }}" class="wFull" required></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>연락처</span></th>
                                                                        <td><input type="text" name="phone" value="{{ old('phone', $manager->phone) }}" class="w300"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>비밀번호 변경</span></th>
                                                                        <td><input type="password" name="password" value="" class="w300" placeholder="변경할 때만 입력"></td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>발주사 로그인 URL</span></th>
                                                                        <td>{{ route('distributor.login') }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>로그인 ID</span></th>
                                                                        <td>{{ $manager->email }}</td>
                                                                    </tr>
                                                                    <tr>
                                                                        <th class="w160"><span>비밀번호 관리</span></th>
                                                                        <td>비밀번호는 보안상 표시되지 않습니다. 변경할 비밀번호를 입력하거나 목록의 PW초기화 버튼을 사용해 주세요.</td>
                                                                    </tr>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="btm_btn mt10">
                                                    <button type="submit">발주사 수정</button>
                                                    <a href="#" class="col5 close_btn">닫기</a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        <script>
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
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
