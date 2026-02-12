{{-- 이 페이지(뷰)는 Admin/NewsletterController.php 컨트롤러의 subscribers() 메서드에서 렌더링됩니다. --}}
@extends('admin.layout.layout')


@section('content')
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Subscribers</h4>


                            {{-- 구독자(`newsletter_subscribers` 데이터베이스 테이블)를 Excel 파일 버튼으로 내보내기 --}}
                            <a href="{{ url('admin/export-subscribers') }}" style="max-width: 100px; float: right"
                                class="btn btn-block btn-primary">Export</a>



                            {{-- 유효성 검사 오류 표시:
                            https://laravel.com/docs/9.x/validation#quick-displaying-the-validation-errors 및
                            https://laravel.com/docs/9.x/blade#validation-errors --}}
                            {{-- 세션에 항목이 존재하는지 확인(has() 메서드 사용):
                            https://laravel.com/docs/9.x/session#determining-if-an-item-exists-in-the-session --}}
                            {{-- 관리자 비밀번호 업데이트 성공 시 Bootstrap 성공 메시지: --}}
                            @if (Session::has('success_message')) <!-- AdminController.php, updateAdminPassword() 메서드 확인 -->
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>Success:</strong> {{ Session::get('success_message') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif


                            <div class="table-responsive pt-3">
                                {{-- DataTable --}}
                                <table id="subscribers" class="table table-bordered"> {{-- DataTable에 여기 id 사용 --}}
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Email</th>
                                            <th>Subscribed on</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>


                                        @foreach ($subscribers as $subscriber)
                                            <tr>
                                                <td>{{ $subscriber['id'] }}</td>
                                                <td>{{ $subscriber['email'] }}</td>
                                                <td>
                                                    {{ date("F j, Y, g:i a", strtotime($subscriber['created_at'])) }} {{--
                                                    https://stackoverflow.com/questions/2487921/convert-a-date-format-in-php
                                                    --}} {{--
                                                    https://www.php.net/manual/en/function.date.php#:~:text=date(%22-,F%20j%2C%20Y%2C%20g%3Ai%20a,-%22)%3B%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20//%20March
                                                    --}}
                                                </td>
                                                <td>
                                                    @if ($subscriber['status'] == 1)
                                                        <a class="updateSubscriberStatus" id="subscriber-{{ $subscriber['id'] }}"
                                                            subscriber_id="{{ $subscriber['id'] }}" href="javascript:void(0)"> {{--
                                                            HTML 사용자 정의 속성 사용. admin/js/custom.js 확인 --}}
                                                            <i style="font-size: 25px" class="mdi mdi-bookmark-check"
                                                                status="Active"></i> {{-- Skydash 관리자 패널 템플릿의 아이콘 --}}
                                                        </a>
                                                    @else {{-- 관리자 상태가 비활성인 경우 --}}
                                                        <a class="updateSubscriberStatus" id="subscriber-{{ $subscriber['id'] }}"
                                                            subscriber_id="{{ $subscriber['id'] }}" href="javascript:void(0)"> {{--
                                                            HTML 사용자 정의 속성 사용. admin/js/custom.js 확인 --}}
                                                            <i style="font-size: 25px" class="mdi mdi-bookmark-outline"
                                                                status="Inactive"></i> {{-- Skydash 관리자 패널 템플릿의 아이콘 --}}
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    {{-- <a href="{{ url('admin/edit-shipping-charges/' . $shipping['id']) }}">
                                                        --}}
                                                        {{-- <i style="font-size: 25px" class="mdi mdi-pencil-box"></i> --}}
                                                        {{-- Icons from Skydash Admin Panel Template --}}
                                                        {{-- </a> --}}

                                                    {{-- Confirm Deletion JS alert and Sweet Alert --}}
                                                    {{-- <a title="Shipping" class="confirmDelete"
                                                        href="{{ url('admin/delete-shipping/' . $shipping['id']) }}"> --}}
                                                        {{-- <i style="font-size: 25px" class="mdi mdi-file-excel-box"></i> --}}
                                                        {{-- Icons from Skydash Admin Panel Template --}}
                                                        {{-- </a> --}}

                                                    <a href="JavaScript:void(0)" class="confirmDelete" module="subscriber"
                                                        moduleid="{{ $subscriber['id'] }}">
                                                        <i style="font-size: 25px" class="mdi mdi-file-excel-box"></i> {{--
                                                        Skydash 관리자 패널 템플릿의 아이콘 --}}
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach


                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- content-wrapper ends -->
        <!-- partial:../../partials/_footer.html -->
        <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
                <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2022. All rights
                    reserved.</span>
            </div>
        </footer>
        <!-- partial -->
    </div>
@endsection