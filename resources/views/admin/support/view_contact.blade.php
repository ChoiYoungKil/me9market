@extends('layouts.admin')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">제휴/문의 상세</div>
                    <ul class="dep">
                        <li>홈</li>
                        <li>고객센터</li>
                        <li>제휴/문의</li>
                    </ul>
                </div>

                @if (Session::has('success_message'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin: 20px;">
                        <strong>성공:</strong> {{ Session::get('success_message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <style>
                    .f_ttl {
                        font-size: 20px !important;
                        font-weight: 800 !important;
                        color: #111 !important;
                        margin-bottom: 25px !important;
                        border-left: 5px solid #3470f7;
                        padding-left: 15px;
                    }

                    .tb01 table tbody th {
                        background-color: #f9f9fb;
                        width: 180px;
                        padding: 15px;
                        font-weight: 600;
                        border-bottom: 1px solid #eee;
                    }

                    .tb01 table tbody td {
                        padding: 15px;
                        border-bottom: 1px solid #eee;
                    }
                </style>

                <div class="conbx">
                    <div class="con_w">
                        <form action="{{ url('admin/update-contact/' . $contact->id) }}" method="post">
                            @csrf
                            <div id="board">
                                <div class="write02">
                                    <div class="f_bx" style="display: block;">
                                        <div class="f_w">
                                            <div class="f_ttl">문의 내용</div>
                                            <div class="tb01">
                                                <table style="width: 100%; border-top: 2px solid #333;">
                                                    <colgroup>
                                                        <col width="180px">
                                                        <col>
                                                    </colgroup>
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th>회사명</th>
                                                            <td>{{ $contact->company }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>담당자명</th>
                                                            <td>{{ $contact->name }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>연락처</th>
                                                            <td>{{ $contact->phone }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>이메일</th>
                                                            <td>{{ $contact->email }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>제목</th>
                                                            <td>{{ $contact->subject }}</td>
                                                        </tr>
                                                        <tr>
                                                            <th>문의 내용</th>
                                                            <td style="white-space: pre-wrap; line-height: 1.6;">
                                                                {{ $contact->message }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>등록일</th>
                                                            <td>{{ $contact->created_at }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="f_w" style="margin-top: 50px;">
                                            <div class="f_ttl">관리자 답변 및 상태 변경</div>
                                            <div class="tb01">
                                                <table style="width: 100%; border-top: 2px solid #333;">
                                                    <colgroup>
                                                        <col width="180px">
                                                        <col>
                                                    </colgroup>
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th>처리 상태</th>
                                                            <td>
                                                                <select name="status" class="w300"
                                                                    style="height: 40px; padding: 0 10px; border: 1px solid #ddd; width: 200px;">
                                                                    <option value="pending" {{ $contact->status == 'pending' ? 'selected' : '' }}>대기중</option>
                                                                    <option value="processing" {{ $contact->status == 'processing' ? 'selected' : '' }}>
                                                                        처리중</option>
                                                                    <option value="completed" {{ $contact->status == 'completed' ? 'selected' : '' }}>
                                                                        완료</option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th>관리자 답변</th>
                                                            <td>
                                                                <textarea name="admin_reply" rows="10"
                                                                    style="width: 100%; padding: 10px; border: 1px solid #ddd; resize: vertical;">{{ $contact->admin_reply }}</textarea>
                                                            </td>
                                                        </tr>
                                                        @if($contact->replied_at)
                                                            <tr>
                                                                <th>답변 일시</th>
                                                                <td>{{ $contact->replied_at }}</td>
                                                            </tr>
                                                        @endif
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="btm_btn center mt40" style="text-align: center; margin-top: 40px;">
                                            <a href="{{ url('admin/contacts') }}" class="btn01 col3"
                                                style="display: inline-block; padding: 0 30px; line-height: 50px; background: #666; color: #fff; margin-right: 10px; border-radius: 5px; text-decoration: none;">목록</a>
                                            <button type="submit" class="btn01 col5"
                                                style="display: inline-block; padding: 0 30px; line-height: 50px; background: #3470f7; color: #fff; border:none; cursor:pointer; border-radius: 5px;">저장</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection