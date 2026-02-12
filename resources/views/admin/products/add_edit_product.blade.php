@extends('admin.layout.layout')


@section('content')
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-md-12 grid-margin">
                    <div class="row">
                        <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                            <h4 class="card-title">Products</h4>
                        </div>
                        <div class="col-12 col-xl-4">
                            <div class="justify-content-end d-flex">
                                <div class="dropdown flex-md-grow-1 flex-xl-grow-0">
                                    <button class="btn btn-sm btn-light bg-white dropdown-toggle" type="button"
                                        id="dropdownMenuDate2" data-toggle="dropdown" aria-haspopup="true"
                                        aria-expanded="true">
                                        <i class="mdi mdi-calendar"></i> Today (10 Jan 2021)
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuDate2">
                                        <a class="dropdown-item" href="#">January - March</a>
                                        <a class="dropdown-item" href="#">March - June</a>
                                        <a class="dropdown-item" href="#">June - August</a>
                                        <a class="dropdown-item" href="#">August - November</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">{{ $title }}</h4>


                            {{-- 현재 비밀번호가 틀리거나 새 비밀번호와 확인 비밀번호가 일치하지 않는 경우의 Bootstrap 오류 코드: --}}
                            {{-- 세션에 항목이 존재하는지 확인(has() 메서드 사용):
                            https://laravel.com/docs/9.x/session#determining-if-an-item-exists-in-the-session --}}
                            @if (Session::has('error_message')) <!-- AdminController.php, updateAdminPassword() 메서드 확인 -->
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <strong>Error:</strong> {{ Session::get('error_message') }}
                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif



                            {{-- 라라벨 유효성 검사 오류 표시:
                            https://laravel.com/docs/9.x/validation#quick-displaying-the-validation-errors --}}
                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">


                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach

                                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                            @endif



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






                            <form class="forms-sample" @if (empty($product['id']))
                            action="{{ url('admin/add-edit-product') }}" @else
                                action="{{ url('admin/add-edit-product/' . $product['id']) }}" @endif method="post"
                                enctype="multipart/form-data">
                                <!-- id가 라우트에서 전달되지 않으면 '새 제품 추가'를 의미하지만, id가 라우트에서 전달되면 '제품 편집'을 의미합니다 -->
                                <!-- 파일(이미지) 업로드를 허용하려면 enctype="multipart/form-data" 사용 -->
                                @csrf



                                <div class="form-group">
                                    <label for="category_id">Select Category</label>
                                    {{-- <input type="text" class="form-control" id="category_id"
                                        placeholder="Enter Category Name" name="category_id" @if (!empty($product['name']))
                                        value="{{ $product['category_id'] }}" @else value="{{ old('category_id') }}" @endif>
                                    --}} {{-- 양식 다시 채우기 (old() 메서드 사용):
                                    https://laravel.com/docs/9.x/validation#repopulating-forms --}}
                                    <select name="category_id" id="category_id" class="form-control text-dark">
                                        <option value="">Select Category</option>
                                        @foreach ($categories as $section) {{-- $categories는 관련된 '부모' 카테고리(있는 경우) 및 하위 카테고리
                                            또는 '자식' 카테고리(있는 경우)가 있는 모든 섹션입니다. --}} {{-- ProductsController.php 확인 --}}
                                            <optgroup label="{{ $section['name'] }}"> {{-- 섹션 --}}
                                                @foreach ($section['categories'] as $category) {{-- 부모 카테고리 --}} {{--
                                                    ProductsController.php 확인 --}}
                                                    <option value="{{ $category['id'] }}" @if (!empty($product['category_id'] == $category['id'])) selected @endif>
                                                        {{ $category['category_name'] }}</option> {{-- 부모 카테고리 --}}
                                                    @foreach ($category['sub_categories'] as $subcategory) {{-- 하위 카테고리 또는 자식 카테고리
                                                        --}} {{-- ProductsController.php 확인 --}}
                                                        <option value="{{ $subcategory['id'] }}" @if (!empty($product['category_id'] == $subcategory['id'])) selected @endif>
                                                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--&nbsp;{{ $subcategory['category_name'] }}
                                                        </option> {{-- 하위 카테고리 또는 자식 카테고리 --}}
                                                    @endforeach
                                                @endforeach
                                            </optgroup>
                                        @endforeach
                                        {{-- <option value="{{ $category['id'] }}" @if (!empty($product['category_id']) &&
                                            $product['category_id']==$category['id']) selected @endif>{{ $category['name']
                                            }}</option> --}}
                                    </select>
                                </div>



                                {{-- 제품의 선택된 카테고리에 따라 제품의 관련 필터 <select> 박스 포함 --}}
                                    <div class="loadFilters">
                                        @include('admin.filters.category_filters')
                                    </div>



                                    <div class="form-group">
                                        <label for="brand_id">Select Brand</label>
                                        <select name="brand_id" id="brand_id" class="form-control text-dark">
                                            <option value="">Select Brand</option>
                                            @foreach ($brands as $brand)
                                                <option value="{{ $brand['id'] }}" @if (!empty($product['brand_id'] == $brand['id'])) selected @endif>
                                                    {{ $brand['name'] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="product_name">Product Name</label>
                                        <input type="text" class="form-control" id="product_name"
                                            placeholder="Enter Product Name" name="product_name" @if (!empty($product['product_name'])) value="{{ $product['product_name'] }}" @else
                                            value="{{ old('product_name') }}" @endif> {{-- 양식 다시 채우기 (old() 메서드 사용):
                                        https://laravel.com/docs/9.x/validation#repopulating-forms --}}
                                    </div>
                                    <div class="form-group">
                                        <label for="product_code">Product Code</label>
                                        <input type="text" class="form-control" id="product_code" placeholder="Enter Code"
                                            name="product_code" @if (!empty($product['product_code']))
                                            value="{{ $product['product_code'] }}" @else value="{{ old('product_code') }}"
                                            @endif> {{-- 양식 다시 채우기 (old() 메서드 사용):
                                        https://laravel.com/docs/9.x/validation#repopulating-forms --}}
                                    </div>
                                    <div class="form-group">
                                        <label for="product_color">Product Color</label>
                                        <input type="text" class="form-control" id="product_color"
                                            placeholder="Enter Product Color" name="product_color" @if (!empty($product['product_color'])) value="{{ $product['product_color'] }}"
                                            @else value="{{ old('product_color') }}" @endif> {{-- 양식 다시 채우기 (old() 메서드 사용):
                                        https://laravel.com/docs/9.x/validation#repopulating-forms --}}
                                    </div>
                                    <div class="form-group">
                                        <label for="product_price">Product Price</label>
                                        <input type="text" class="form-control" id="product_price"
                                            placeholder="Enter Product Price" name="product_price" @if (!empty($product['product_price'])) value="{{ $product['product_price'] }}"
                                            @else value="{{ old('product_price') }}" @endif> {{-- 양식 다시 채우기 (old() 메서드 사용):
                                        https://laravel.com/docs/9.x/validation#repopulating-forms --}}
                                    </div>
                                    <div class="form-group">
                                        <label for="product_discount">Product Discount (%)</label>
                                        <input type="text" class="form-control" id="product_discount"
                                            placeholder="Enter Product Discount" name="product_discount" @if (!empty($product['product_discount']))
                                            value="{{ $product['product_discount'] }}" @else
                                            value="{{ old('product_discount') }}" @endif> {{-- 양식 다시 채우기 (old() 메서드 사용):
                                        https://laravel.com/docs/9.x/validation#repopulating-forms --}}
                                    </div>
                                    <div class="form-group">
                                        <label for="product_weight">Product Weight (%)</label>
                                        <input type="text" class="form-control" id="product_weight"
                                            placeholder="Enter Product Weight" name="product_weight" @if (!empty($product['product_weight'])) value="{{ $product['product_weight'] }}"
                                            @else value="{{ old('product_weight') }}" @endif> {{-- 양식 다시 채우기 (old() 메서드 사용):
                                        https://laravel.com/docs/9.x/validation#repopulating-forms --}}
                                    </div>



                                    {{-- Managing Product Colors (in front/products/detail.blade.php) --}}
                                    <div class="form-group">
                                        <label for="group_code">Group Code</label>
                                        <input type="text" class="form-control" id="group_code"
                                            placeholder="Enter Group Code" name="group_code" @if (!empty($product['group_code'])) value="{{ $product['group_code'] }}" @else
                                            value="{{ old('group_code') }}" @endif> {{-- 양식 다시 채우기 (old() 메서드 사용):
                                        https://laravel.com/docs/9.x/validation#repopulating-forms --}}
                                    </div>



                                    <div class="form-group">
                                        <label for="product_image">Product Image (Recommended Size: 1000x1000)</label> {{--
                                        중요 참고 사항: 제품 이미지에는 3가지 크기가 있습니다: 관리자는 1000*1000(큰 크기, 'large' 폴더에 저장)인 권장 크기로 이미지를
                                        업로드하지만, 그 후 'Intervention' 패키지를 사용하여 두 가지 다른 크기, 즉 500*500(중간 크기, 'medium' 폴더에 저장)과
                                        250*250(작은 크기, 'small' 폴더에 저장)을 얻습니다. --}}
                                        <input type="file" class="form-control" id="product_image" name="product_image">
                                        {{-- Show the admin image if exists --}}




                                        {{-- Show the product image, if any (if exits) --}}
                                        @if (!empty($product['product_image']))
                                            <a target="_blank"
                                                href="{{ url('front/images/product_images/large/' . $product['product_image']) }}">View
                                                Product Image</a>&nbsp;|&nbsp; {{-- 'large' 폴더의 'large' 이미지 표시 --}}
                                            <a href="JavaScript:void(0)" class="confirmDelete" module="product-image"
                                                moduleid="{{ $product['id'] }}">Delete Product Image</a> {{-- 서버(파일 시스템) 및
                                            데이터베이스 모두에서 제품 이미지 삭제 --}} {{-- admin/js/custom.js 및 web.php (라우트) 확인 --}}
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="product_video">Product Video (Recommended Size: Less than 2 MB)</label>
                                        {{-- 중요 참고 사항: 기본 php.ini 파일 업로드 최대 파일 크기는 2MB입니다(더 큰 크기의 파일을 업로드하면 업로드되지 않습니다!).
                                        phpinfo() 메서드를 사용하여 upload_max_filesize를 확인하세요. --}}
                                        <input type="file" class="form-control" id="product_video" name="product_video">
                                        {{-- Show the admin image if exists --}}




                                        {{-- Show the product video, if any (if exits) --}}
                                        @if (!empty($product['product_video']))
                                            <a target="_blank"
                                                href="{{ url('front/videos/product_videos/' . $product['product_video']) }}">View
                                                Product Video</a>&nbsp;|&nbsp;
                                            <a href="JavaScript:void(0)" class="confirmDelete" module="product-video"
                                                moduleid="{{ $product['id'] }}">Delete Product Video</a> {{-- 서버(파일 시스템) 및
                                            데이터베이스 모두에서 제품 비디오 삭제 --}} {{-- admin/js/custom.js 및 web.php (라우트) 확인 --}}
                                        @endif
                                    </div>
                                    <div class="form-group">
                                        <label for="description">Product Description</label>
                                        <textarea name="description" id="description" class="form-control"
                                            rows="3">{{ $product['description'] }}</textarea>
                                    </div>
                                    <div class="form-group">
                                        <label for="meta_title">Meta Title</label>
                                        <input type="text" class="form-control" id="meta_title"
                                            placeholder="Enter Meta Title" name="meta_title" @if (!empty($product['meta_title'])) value="{{ $product['meta_title'] }}" @else
                                            value="{{ old('meta_title') }}" @endif> {{-- 양식 다시 채우기 (old() 메서드 사용):
                                        https://laravel.com/docs/9.x/validation#repopulating-forms --}}
                                    </div>
                                    <div class="form-group">
                                        <label for="meta_description">Meta Description</label>
                                        <input type="text" class="form-control" id="meta_description"
                                            placeholder="Enter Meta Description" name="meta_description" @if (!empty($product['meta_description']))
                                            value="{{ $product['meta_description'] }}" @else
                                            value="{{ old('meta_description') }}" @endif> {{-- 양식 다시 채우기 (old() 메서드 사용):
                                        https://laravel.com/docs/9.x/validation#repopulating-forms --}}
                                    </div>
                                    <div class="form-group">
                                        <label for="meta_keywords">Meta Keywords</label>
                                        <input type="text" class="form-control" id="meta_keywords"
                                            placeholder="Enter Meta Keywords" name="meta_keywords" @if (!empty($product['meta_keywords'])) value="{{ $product['meta_keywords'] }}"
                                            @else value="{{ old('meta_keywords') }}" @endif> {{-- 양식 다시 채우기 (old() 메서드 사용):
                                        https://laravel.com/docs/9.x/validation#repopulating-forms --}}
                                    </div>
                                    <div class="form-group">
                                        <label for="is_featured">Featured Item (Yes/No)</label>
                                        <input type="checkbox" name="is_featured" id="is_featured" value="Yes" @if (!empty($product['is_featured']) && $product['is_featured'] == 'Yes') checked
                                        @endif>
                                    </div>
                                    <div class="form-group">
                                        <label for="is_bestseller">Best Seller Item (Yes/No)</label> {{-- 참고: 'superadmin'만
                                        제품을 'bestseller'로 표시할 수 있으며 'vendor'는 표시할 수 없습니다. --}}
                                        <input type="checkbox" name="is_bestseller" id="is_bestseller" value="Yes" @if (!empty($product['is_bestseller']) && $product['is_bestseller'] == 'Yes') checked
                                        @endif>
                                    </div>
                                    <button type="submit" class="btn btn-primary mr-2">Submit</button>
                                    <button type="reset" class="btn btn-light">Cancel</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- content-wrapper ends -->
        @include('admin.layout.footer')
        <!-- partial -->
    </div>
@endsection