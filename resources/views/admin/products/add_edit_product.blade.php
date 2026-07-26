@extends('layouts.admin')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">{{ $title }}</div>
                    <ul class="dep">
                        <li>홈</li>
                        <li>상품 관리</li>
                        <li>상품 리스트</li>
                        <li>{{ $title }}</li>
                    </ul>
                </div>

                @if (Session::has('error_message'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin: 20px;">
                        <strong>오류:</strong> {{ Session::get('error_message') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="margin: 20px;">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

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
                    }
                </style>

                <div class="conbx">
                    <div class="con_w">
                        <form @if (empty($product['id'])) action="{{ url('admin/add-edit-product') }}" @else
                        action="{{ url('admin/add-edit-product/' . $product['id']) }}" @endif method="post"
                            enctype="multipart/form-data">
                            @csrf

                            <div id="board">
                                <div class="write02">
                                    <div class="f_bx">
                                        <!-- 기본 정보 -->
                                        <div class="f_w">
                                            <div class="f_ttl">기본 정보</div>
                                            <div class="tb01">
                                                <table class="two">
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th class="w160"><span>분류 선택</span></th>
                                                            <td>
                                                                <select name="category_id" id="category_id" class="w300">
                                                                    <option value="">분류를 선택하세요</option>
                                                                    @foreach ($categories as $section)
                                                                        <optgroup label="{{ $section['name'] }}">
                                                                            @foreach ($section['categories'] as $category)
                                                                                <option value="{{ $category['id'] }}" @if (!empty($product['category_id']) && $product['category_id'] == $category['id'])
                                                                                selected @endif>
                                                                                    {{ $category['category_name'] }}
                                                                                </option>
                                                                                @foreach ($category['sub_categories'] as $subcategory)
                                                                                    <option value="{{ $subcategory['id'] }}" @if (!empty($product['category_id']) && $product['category_id'] == $subcategory['id'])
                                                                                    selected @endif>
                                                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--
                                                                                        {{ $subcategory['category_name'] }}
                                                                                    </option>
                                                                                @endforeach
                                                                            @endforeach
                                                                        </optgroup>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <th class="w160"><span>브랜드 선택</span></th>
                                                            <td>
                                                                <select name="brand_id" id="brand_id" class="w300">
                                                                    <option value="">브랜드를 선택하세요</option>
                                                                    @foreach ($brands as $brand)
                                                                        <option value="{{ $brand['id'] }}" @if (!empty($product['brand_id']) && $product['brand_id'] == $brand['id']) selected @endif>
                                                                            {{ $brand['name'] }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        {{-- 제품의 선택된 카테고리에 따라 제품의 관련 필터 <select> 박스 포함 --}}
                                            <div class="loadFilters">
                                                @include('admin.filters.category_filters')
                                            </div>

                                            <!-- 상품 상세 정보 -->
                                            <div class="f_w mt40">
                                                <div class="f_ttl">상품 상세 정보</div>
                                                <div class="tb01">
                                                    <table class="two">
                                                        <tbody class="textL">
                                                            <tr>
                                                                <th class="w160"><span>상품명</span></th>
                                                                <td>
                                                                    <input type="text" class="w300" id="product_name"
                                                                        placeholder="상품명을 입력하세요" name="product_name" @if (!empty($product['product_name']))
                                                                        value="{{ $product['product_name'] }}" @else
                                                                        value="{{ old('product_name') }}" @endif>
                                                                </td>
                                                                <th class="w160"><span>상품코드</span></th>
                                                                <td>
                                                                    <input type="text" class="w300" id="product_code"
                                                                        placeholder="상품코드를 입력하세요" name="product_code" @if (!empty($product['product_code']))
                                                                        value="{{ $product['product_code'] }}" @else
                                                                        value="{{ old('product_code') }}" @endif>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>상품 색상</span></th>
                                                                <td>
                                                                    <input type="text" class="w300" id="product_color"
                                                                        placeholder="색상을 입력하세요" name="product_color" @if (!empty($product['product_color']))
                                                                        value="{{ $product['product_color'] }}" @else
                                                                        value="{{ old('product_color') }}" @endif>
                                                                </td>
                                                                <th class="w160"><span>그룹 코드</span></th>
                                                                <td>
                                                                    <input type="text" class="w300" id="group_code"
                                                                        placeholder="그룹 코드를 입력하세요" name="group_code" @if (!empty($product['group_code']))
                                                                        value="{{ $product['group_code'] }}" @else
                                                                        value="{{ old('group_code') }}" @endif>
                                                                    <p
                                                                        style="margin-top: 5px; font-size: 12px; color: #666;">
                                                                        ※ 같은 상품의 다른 색상을 그룹화할 때 사용합니다.</p>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- 가격 및 무게 -->
                                            <div class="f_w mt40">
                                                <div class="f_ttl">가격 및 무게</div>
                                                <div class="tb01">
                                                    <table class="two">
                                                        <tbody class="textL">
                                                            <tr>
                                                                <th class="w160"><span>상품 가격</span></th>
                                                                <td>
                                                                    <input type="text" class="w300" id="product_price"
                                                                        placeholder="가격을 입력하세요" name="product_price" @if (!empty($product['product_price']))
                                                                        value="{{ $product['product_price'] }}" @else
                                                                        value="{{ old('product_price') }}" @endif>
                                                                </td>
                                                                <th class="w160"><span>할인율 (%)</span></th>
                                                                <td>
                                                                    <input type="text" class="w300" id="product_discount"
                                                                        placeholder="할인율을 입력하세요" name="product_discount" @if (!empty($product['product_discount']))
                                                                        value="{{ $product['product_discount'] }}" @else
                                                                        value="{{ old('product_discount') }}" @endif>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>상품 무게 (g)</span></th>
                                                                <td colspan="3">
                                                                    <input type="text" class="w300" id="product_weight"
                                                                        placeholder="무게를 입력하세요" name="product_weight" @if (!empty($product['product_weight']))
                                                                        value="{{ $product['product_weight'] }}" @else
                                                                        value="{{ old('product_weight') }}" @endif>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- 이미지 및 비디오 -->
                                            <div class="f_w mt40">
                                                <div class="f_ttl">이미지 및 비디오</div>
                                                <div class="tb01">
                                                    <table class="two">
                                                        <tbody class="textL">
                                                            <tr>
                                                                <th class="w160"><span>상품 이미지</span></th>
                                                                <td colspan="3">
                                                                    <div class="fileBox">
                                                                        <input class="fileName" value=""
                                                                            placeholder="파일을 선택하세요 (권장: 1000x1000)"
                                                                            readonly>
                                                                        <label for="product_image" class="btn_file">파일
                                                                            선택</label>
                                                                        <input type="file" id="product_image"
                                                                            name="product_image" class="uploadBtn">
                                                                    </div>
                                                                    @if (!empty($product['product_image']))
                                                                        <div style="margin-top: 10px;">
                                                                            <a target="_blank"
                                                                                href="{{ url('front/images/product_images/large/' . $product['product_image']) }}">이미지
                                                                                보기</a>
                                                                            &nbsp;|&nbsp;
                                                                            <a href="JavaScript:void(0)" class="confirmDelete"
                                                                                module="product-image"
                                                                                moduleid="{{ $product['id'] }}">이미지 삭제</a>
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>상품 비디오</span></th>
                                                                <td colspan="3">
                                                                    <div class="fileBox">
                                                                        <input class="fileName" value=""
                                                                            placeholder="파일을 선택하세요 (최대 2MB)" readonly>
                                                                        <label for="product_video" class="btn_file">파일
                                                                            선택</label>
                                                                        <input type="file" id="product_video"
                                                                            name="product_video" class="uploadBtn">
                                                                    </div>
                                                                    @if (!empty($product['product_video']))
                                                                        <div style="margin-top: 10px;">
                                                                            <a target="_blank"
                                                                                href="{{ url('front/videos/product_videos/' . $product['product_video']) }}">비디오
                                                                                보기</a>
                                                                            &nbsp;|&nbsp;
                                                                            <a href="JavaScript:void(0)" class="confirmDelete"
                                                                                module="product-video"
                                                                                moduleid="{{ $product['id'] }}">비디오 삭제</a>
                                                                        </div>
                                                                    @endif
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- 상세 설명 -->
                                            <div class="f_w mt40">
                                                <div class="f_ttl">상세 설명</div>
                                                <div class="tb01">
                                                    <table class="two">
                                                        <tbody class="textL">
                                                            <tr>
                                                                <th class="w160"><span>상품 설명</span></th>
                                                                <td colspan="3">
                                                                    <textarea name="description" id="description" rows="6"
                                                                        style="width: 100%; padding: 10px; border: 1px solid #ddd;">{{ $product['description'] ?? '' }}</textarea>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- SEO 정보 -->
                                            <div class="f_w mt40">
                                                <div class="f_ttl">SEO 정보</div>
                                                <div class="tb01">
                                                    <table class="two">
                                                        <tbody class="textL">
                                                            <tr>
                                                                <th class="w160"><span>메타 제목</span></th>
                                                                <td colspan="3">
                                                                    <input type="text" class="wFull" id="meta_title"
                                                                        placeholder="메타 제목을 입력하세요" name="meta_title" @if (!empty($product['meta_title']))
                                                                        value="{{ $product['meta_title'] }}" @else
                                                                        value="{{ old('meta_title') }}" @endif>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>메타 설명</span></th>
                                                                <td colspan="3">
                                                                    <input type="text" class="wFull"
                                                                        id="meta_description" placeholder="메타 설명을 입력하세요"
                                                                        name="meta_description" @if (!empty($product['meta_description']))
                                                                        value="{{ $product['meta_description'] }}" @else
                                                                        value="{{ old('meta_description') }}" @endif>
                                                                </td>
                                                            </tr>
                                                            <tr>
                                                                <th class="w160"><span>메타 키워드</span></th>
                                                                <td colspan="3">
                                                                    <input type="text" class="wFull"
                                                                        id="meta_keywords"
                                                                        placeholder="메타 키워드를 입력하세요 (쉼표로 구분)"
                                                                        name="meta_keywords" @if (!empty($product['meta_keywords']))
                                                                        value="{{ $product['meta_keywords'] }}" @else
                                                                        value="{{ old('meta_keywords') }}" @endif>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- 추가 옵션 -->
                                            <div class="f_w mt40">
                                                <div class="f_ttl">추가 옵션</div>
                                                <div class="tb01">
                                                    <table class="two">
                                                        <tbody class="textL">
                                                            <tr>
                                                                <th class="w160"><span>추천 상품</span></th>
                                                                <td>
                                                                    <ul class="chk01">
                                                                        <li>
                                                                            <input type="checkbox" name="is_featured"
                                                                                id="is_featured" value="Yes" @if (!empty($product['is_featured']) && $product['is_featured'] == 'Yes') checked
                                                                                @endif>
                                                                            <label for="is_featured">추천 상품으로 표시</label>
                                                                        </li>
                                                                    </ul>
                                                                </td>
                                                                <th class="w160"><span>베스트셀러</span></th>
                                                                <td>
                                                                    <ul class="chk01">
                                                                        <li>
                                                                            <input type="checkbox" name="is_bestseller"
                                                                                id="is_bestseller" value="Yes" @if (!empty($product['is_bestseller']) && $product['is_bestseller'] == 'Yes') checked
                                                                                @endif>
                                                                            <label for="is_bestseller">베스트셀러로 표시</label>
                                                                        </li>
                                                                    </ul>
                                                                    <p
                                                                        style="margin-top: 5px; font-size: 12px; color: #666;">
                                                                        ※ 관리자만 베스트셀러로 설정할 수 있습니다.</p>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            <!-- 버튼 -->
                                            <div class="btm_btn center mt40"
                                                style="display: flex; justify-content: center; gap: 10px;">
                                                <a href="{{ url('admin/products') }}" class="btn01 col3">취소</a>
                                                <button type="submit" class="btn01 col5">저장</button>
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

    <script>
        $(document).ready(function () {
            // File upload display
            var uploadFile = $('.fileBox .uploadBtn');
            uploadFile.on('change', function () {
                if (window.FileReader) {
                    var filename = $(this)[0].files[0].name;
                } else {
                    var filename = $(this).val().split('/').pop().split('\\').pop();
                }
                $(this).parents('.fileBox').find('.fileName').val(filename);
            });

            // AJAX: Load Filters based on selected Category
            $('#category_id').change(function () {
                var category_id = $(this).val();
                $.ajax({
                    type: 'get',
                    url: '/admin/category-filters',
                    data: { category_id: category_id },
                    success: function (resp) {
                        $('.loadFilters').html(resp);
                    },
                    error: function () {
                        alert('필터 목록을 불러오는데 실패했습니다.');
                    }
                });
            });

            // Confirm Delete
            $(".confirmDelete").click(function (e) {
                var module = $(this).attr('module');
                var moduleid = $(this).attr('moduleid');
                if (!confirm("정말로 삭제하시겠습니까?")) {
                    return false;
                }
                if (module == 'product-image') {
                    window.location.href = "/admin/delete-product-image/" + moduleid;
                } else if (module == 'product-video') {
                    window.location.href = "/admin/delete-product-video/" + moduleid;
                }
            });
        });
    </script>
@endsection
