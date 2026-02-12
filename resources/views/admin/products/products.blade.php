@extends('admin.layout.layout')


@section('content')
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Products</h4>



                            
                            <a href="{{ url('admin/add-edit-product') }}" style="max-width: 150px; float: right; display: inline-block" class="btn btn-block btn-primary">Add Product</a>

                            {{-- 유효성 검사 오류 표시: https://laravel.com/docs/9.x/validation#quick-displaying-the-validation-errors 및 https://laravel.com/docs/9.x/blade#validation-errors --}}
                            {{-- 세션에 항목이 존재하는지 확인(has() 메서드 사용): https://laravel.com/docs/9.x/session#determining-if-an-item-exists-in-the-session --}}
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
                                <table id="products" class="table table-bordered"> {{-- DataTable에 여기 id 사용 --}}
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Product Name</th>
                                            <th>Product Code</th>
                                            <th>Product Color</th>
                                            <th>Product Image</th>
                                            <th>Category</th> {{-- 관계를 통해 --}}
                                            <th>Section</th>  {{-- 관계를 통해 --}}
                                            <th>Added by</th> {{-- 관계를 통해 --}}
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($products as $product)
                                            <tr>
                                                <td>{{ $product['id'] }}</td>
                                                <td>{{ $product['product_name'] }}</td>
                                                <td>{{ $product['product_code'] }}</td>
                                                <td>{{ $product['product_color'] }}</td>
                                                <td>
                                                    @if (!empty($product['product_image']))
                                                        <img style="width:120px; height:100px" src="{{ asset('front/images/product_images/small/' . $product['product_image']) }}"> {{-- 'small' 폴더에서 'small' 이미지 크기 표시 --}}
                                                    @else
                                                        <img style="width:120px; height:100px" src="{{ asset('front/images/product_images/small/no-image.png') }}"> {{-- 'no-image' 더미 이미지 표시: 예를 들어 'images' 열이 있는 테이블(존재할 수도 있고 존재하지 않을 수도 있음)이 있는 경우 이미지가 없을 때 '더미 이미지'를 사용합니다. 예: https://dummyimage.com/  --}}
                                                    @endif
                                                </td>
                                                <td>{{ $product['category']['category_name'] }}</td> {{-- 관계를 통해 --}}
                                                <td>{{ $product['section']['name'] }}</td> {{-- 관계를 통해 --}}
                                                <td>
                                                    @if ($product['admin_type'] == 'vendor')
                                                        <a target="_blank" href="{{ url('admin/view-vendor-details/' . $product['admin_id']) }}">{{ ucfirst($product['admin_type']) }}</a>
                                                    @else
                                                        {{ ucfirst($product['admin_type']) }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($product['status'] == 1)
                                                        <a class="updateProductStatus" id="product-{{ $product['id'] }}" product_id="{{ $product['id'] }}" href="javascript:void(0)"> {{-- HTML 사용자 정의 속성 사용. admin/js/custom.js 확인 --}}
                                                            <i style="font-size: 25px" class="mdi mdi-bookmark-check" status="Active"></i> {{-- Skydash 관리자 패널 템플릿의 아이콘 --}}
                                                        </a>
                                                    @else {{-- 관리자 상태가 비활성인 경우 --}}
                                                        <a class="updateProductStatus" id="product-{{ $product['id'] }}" product_id="{{ $product['id'] }}" href="javascript:void(0)"> {{-- HTML 사용자 정의 속성 사용. admin/js/custom.js 확인 --}}
                                                            <i style="font-size: 25px" class="mdi mdi-bookmark-outline" status="Inactive"></i> {{-- Skydash 관리자 패널 템플릿의 아이콘 --}}
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a title="Edit Product" href="{{ url('admin/add-edit-product/' . $product['id']) }}">
                                                        <i style="font-size: 25px" class="mdi mdi-pencil-box"></i> {{-- Skydash 관리자 패널 템플릿의 아이콘 --}}
                                                    </a>
                                                    <a title="Add Attributes" href="{{ url('admin/add-edit-attributes/' . $product['id']) }}">
                                                        <i style="font-size: 25px" class="mdi mdi-plus-box"></i> {{-- Skydash 관리자 패널 템플릿의 아이콘 --}}
                                                    </a>
                                                    <a title="Add Multiple Images" href="{{ url('admin/add-images/' . $product['id']) }}">
                                                        <i style="font-size: 25px" class="mdi mdi-library-plus"></i> {{-- Skydash 관리자 패널 템플릿의 아이콘 --}}
                                                    </a>

                                                    {{-- 삭제 확인 JS 경고 및 Sweet Alert --}}
                                                    {{-- <a title="Product" class="confirmDelete" href="{{ url('admin/delete-product/' . $product['id']) }}"> --}}
                                                        {{-- <i style="font-size: 25px" class="mdi mdi-file-excel-box"></i> --}} {{-- Skydash 관리자 패널 템플릿의 아이콘 --}}
                                                    {{-- </a> --}}
                                                    <a href="JavaScript:void(0)" class="confirmDelete" module="product" moduleid="{{ $product['id'] }}"> {{-- admin/js/custom.js 및 web.php (라우트) 확인 --}}
                                                        <i style="font-size: 25px" class="mdi mdi-file-excel-box"></i> {{-- Skydash 관리자 패널 템플릿의 아이콘 --}}
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
                <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2022. All rights reserved.</span>
            </div>
        </footer>
        <!-- partial -->
    </div>
@endsection