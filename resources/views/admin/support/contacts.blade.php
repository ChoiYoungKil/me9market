@extends('layouts.admin')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">제휴/문의 관리</div>
                    <ul class="dep">
                        <li>홈</li>
                        <li>고객센터</li>
                        <li>제휴/문의</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1">
                            <div class="count">총 <strong>{{ count($contacts) }}</strong> 개</div>
                        </div>

                        @if (Session::has('success_message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-top: 10px;">
                                <strong>성공:</strong> {{ Session::get('success_message') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="tb01 ovS">
                            <table class="table table-bordered">
                                <colgroup>
                                    <col width="50px">
                                    <col width="100px">
                                    <col width="150px">
                                    <col width="">
                                    <col width="120px">
                                    <col width="100px">
                                    <col width="150px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>번호</th>
                                        <th>이름</th>
                                        <th>이메일</th>
                                        <th>제목</th>
                                        <th>등록일</th>
                                        <th>상태</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($contacts as $contact)
                                        <tr>
                                            <td>{{ $contact->id }}</td>
                                            <td>{{ $contact->name }}</td>
                                            <td>{{ $contact->email }}</td>
                                            <td style="text-align: left;">{{ Str::limit($contact->subject, 50) }}</td>
                                            <td>{{ date('Y-m-d H:i', strtotime($contact->created_at)) }}</td>
                                            <td>
                                                @if($contact->status == 'pending')
                                                    <span style="color:red">대기중</span>
                                                @elseif($contact->status == 'processing')
                                                    <span style="color:orange">처리중</span>
                                                @else
                                                    <span style="color:green">완료</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ url('admin/view-contact/'.$contact->id) }}" class="btn02">보기</a>
                                                <a href="{{ url('admin/delete-contact/'.$contact->id) }}" class="btn02" style="color:red" onclick="return confirm('정말 삭제하시겠습니까?')">삭제</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    @if(count($contacts) == 0)
                                        <tr><td colspan="7" class="no_data">등록된 문의가 없습니다.</td></tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection