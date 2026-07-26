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
                        <li>분류 관리</li>
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
                        <form @if (empty($category['id'])) action="{{ url('admin/add-edit-category') }}" @else
                        action="{{ url('admin/add-edit-category/' . $category['id']) }}" @endif method="post"
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
                                                            <th class="w160"><span>분류명</span></th>
                                                            <td>
                                                                <input type="text" class="w300" id="category_name"
                                                                    placeholder="분류명을 입력하세요" name="category_name" @if (!empty($category['category_name']))
                                                                    value="{{ $category['category_name'] }}" @else
                                                                    value="{{ old('category_name') }}" @endif>
                                                            </td>
                                                            <th class="w160"><span>섹션 선택</span></th>
                                                            <td>
                                                                <select name="section_id" id="section_id" class="w300">
                                                                    <option value="">섹션을 선택하세요</option>
                                                                    @foreach ($getSections as $section)
                                                                        <option value="{{ $section['id'] }}" @if (!empty($category['section_id']) && $category['section_id'] == $section['id']) selected
                                                                        @endif>{{ $section['name'] }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- 상위 분류 -->
                                        <div id="appendCategoriesLevel">
                                            @include('admin.categories.append_categories_level')
                                        </div>

                                        <!-- 상세 정보 -->
                                        <div class="f_w mt40">
                                            <div class="f_ttl">상세 정보</div>
                                            <div class="tb01">
                                                <table class="two">
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th class="w160"><span>분류 이미지</span></th>
                                                            <td colspan="3">
                                                                <div class="fileBox">
                                                                    <input type="text" class="fileName" value="" placeholder="파일을 선택하세요"
                                                                        readonly>
                                                                    <label for="category_image" class="btn_file">파일
                                                                        선택</label>
                                                                    <input type="file" id="category_image"
                                                                        name="category_image" class="uploadBtn">
                                                                </div>
                                                                @if (!empty($category['category_image']))
                                                                    <div style="margin-top: 10px;">
                                                                        <a target="_blank"
                                                                            href="{{ url('front/images/category_images/' . $category['category_image']) }}">이미지
                                                                            보기</a>
                                                                        &nbsp;|&nbsp;
                                                                        <a href="JavaScript:void(0)" class="confirmDelete"
                                                                            module="category-image"
                                                                            moduleid="{{ $category['id'] }}">이미지 삭제</a>
                                                                    </div>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>분류 할인율 (%)</span></th>
                                                            <td>
                                                                <input type="text" class="w300" id="category_discount"
                                                                    placeholder="할인율을 입력하세요" name="category_discount" @if (!empty($category['category_discount']))
                                                                    value="{{ $category['category_discount'] }}" @else
                                                                    value="{{ old('category_discount') }}" @endif>
                                                            </td>
                                                            <th class="w160"><span>분류 URL</span></th>
                                                            <td>
                                                                <input type="text" class="w300" id="url"
                                                                    placeholder="URL을 입력하세요" name="url" @if (!empty($category['url']))
                                                                    value="{{ $category['url'] }}" @else
                                                                    value="{{ old('url') }}" @endif>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>분류 설명</span></th>
                                                            <td colspan="3">
                                                                <textarea name="description" id="description" rows="4"
                                                                    class="wFull">{{ $category['description'] ?? '' }}</textarea>
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
                                                                    placeholder="메타 제목을 입력하세요" name="meta_title" @if (!empty($category['meta_title']))
                                                                    value="{{ $category['meta_title'] }}" @else
                                                                    value="{{ old('meta_title') }}" @endif>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>메타 설명</span></th>
                                                            <td colspan="3">
                                                                <input type="text" class="wFull"
                                                                    id="meta_description" placeholder="메타 설명을 입력하세요"
                                                                    name="meta_description" @if (!empty($category['meta_description']))
                                                                    value="{{ $category['meta_description'] }}" @else
                                                                    value="{{ old('meta_description') }}" @endif>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>메타 키워드</span></th>
                                                            <td colspan="3">
                                                                <input type="text" class="wFull" id="meta_keywords"
                                                                    placeholder="메타 키워드를 입력하세요 (쉼표로 구분)"
                                                                    name="meta_keywords" @if (!empty($category['meta_keywords']))
                                                                    value="{{ $category['meta_keywords'] }}" @else
                                                                    value="{{ old('meta_keywords') }}" @endif>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- 버튼 -->
                                        <div class="btm_btn center mt40 form-button-row">
                                            <a href="{{ url('admin/categories') }}" class="btn01 col3">취소</a>
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

            // AJAX: Append Categories Level based on selected Section
            $('#section_id').change(function () {
                var section_id = $(this).val();
                $.ajax({
                    type: 'get',
                    url: '/admin/append-categories-level',
                    data: { section_id: section_id },
                    success: function (resp) {
                        $('#appendCategoriesLevel').html(resp);
                    },
                    error: function () {
                        alert('카테고리 목록을 불러오는데 실패했습니다.');
                    }
                });
            });

            // Confirm Delete
            $(".confirmDelete").click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                var module = $(this).attr('module');
                var moduleid = $(this).attr('moduleid');
                if (!confirm("정말로 삭제하시겠습니까?")) {
                    return false;
                }
                if (module == 'category-image') {
                    submitAdminDelete("/admin/delete-category-image/" + moduleid);
                }
            });
        });
    </script>
@endsection
