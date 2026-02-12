@extends('admin.layout.layout')


@section('content')
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Categories</h4>




                            <a href="{{ url('admin/add-edit-category') }}"
                                style="max-width: 150px; float: right; display: inline-block"
                                class="btn btn-block btn-primary">Add Category</a>

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
                                <table id="categories" class="table table-bordered"> {{-- DataTable에 여기 id 사용 --}}
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Category Name</th>
                                            <th>Parent Category</th> {{-- 관계를 통해 --}}
                                            <th>Parent Section</th> {{-- 관계를 통해 --}}
                                            <th>URL</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($categories as $category)
                                            {{-- @php echo '
                                            <pre>', var_dump($category['parent_category']), '</pre>'; @endphp --}}
                                            @if (isset($category['parent_category']['category_name']) && !empty($category['parent_category']['category_name']))
                                                @php $parent_category = $category['parent_category']['category_name']; @endphp
                                            @else
                                                @php $parent_category = 'Root'; @endphp
                                            @endif
                                            <tr>
                                                <td>{{ $category['id'] }}</td>
                                                <td>{{ $category['category_name'] }}</td>
                                                <td>{{ $parent_category }}</td> {{-- 관계를 통해 --}}
                                                <td>{{ $category['section']['name'] }}</td> {{-- 관계를 통해 --}}
                                                <td>{{ $category['url'] }}</td>
                                                <td>
                                                    @if ($category['status'] == 1)
                                                        <a class="updateCategoryStatus" id="category-{{ $category['id'] }}"
                                                            category_id="{{ $category['id'] }}" href="javascript:void(0)"> {{-- HTML
                                                            사용자 정의 속성 사용. admin/js/custom.js 확인 --}}
                                                            <i style="font-size: 25px" class="mdi mdi-bookmark-check"
                                                                status="Active"></i> {{-- Skydash 관리자 패널 템플릿의 아이콘 --}}
                                                        </a>
                                                    @else {{-- 관리자 상태가 비활성인 경우 --}}
                                                        <a class="updateCategoryStatus" id="category-{{ $category['id'] }}"
                                                            category_id="{{ $category['id'] }}" href="javascript:void(0)"> {{-- HTML
                                                            사용자 정의 속성 사용. admin/js/custom.js 확인 --}}
                                                            <i style="font-size: 25px" class="mdi mdi-bookmark-outline"
                                                                status="Inactive"></i> {{-- Skydash 관리자 패널 템플릿의 아이콘 --}}
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ url('admin/add-edit-category/' . $category['id']) }}">
                                                        <i style="font-size: 25px" class="mdi mdi-pencil-box"></i> {{-- Skydash
                                                        관리자 패널 템플릿의 아이콘 --}}
                                                    </a>

                                                    {{-- 삭제 확인 JS 경고 및 Sweet Alert --}}
                                                    {{-- <a title="Category" class="confirmDelete"
                                                        href="{{ url('admin/delete-category/' . $category['id']) }}"> --}}
                                                        {{-- <i style="font-size: 25px" class="mdi mdi-file-excel-box"></i> --}}
                                                        {{-- Skydash 관리자 패널 템플릿의 아이콘 --}}
                                                        {{-- </a> --}}
                                                    <a href="JavaScript:void(0)" class="confirmDelete" module="category"
                                                        moduleid="{{ $category['id'] }}"> {{-- admin/js/custom.js 및 web.php
                                                        (라우트) 확인 --}}
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