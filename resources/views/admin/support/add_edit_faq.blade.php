@extends('layouts.admin')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">{{ $title }}</div>
                    <ul class="dep">
                        <li>홈</li>
                        <li>고객센터</li>
                        <li>자주묻는질문</li>
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
                        <form @if (empty($faq['id'])) action="{{ url('admin/add-edit-faq') }}" @else
                        action="{{ url('admin/add-edit-faq/' . $faq['id']) }}" @endif method="post">
                            @csrf

                            <div id="board">
                                <div class="write02">
                                    <div class="f_bx">
                                        <!-- 기본 정보 -->
                                        <div class="f_w">
                                            <div class="f_ttl">FAQ 정보</div>
                                            <div class="tb01">
                                                <table class="two">
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th class="w160"><span>카테고리</span></th>
                                                            <td>
                                                                <select name="category" id="category" class="w300">
                                                                    <option value="">선택하세요</option>
                                                                    <option value="주문/배송" @if (!empty($faq['category']) && $faq['category'] == '주문/배송') selected @endif>주문/배송
                                                                    </option>
                                                                    <option value="결제" @if (!empty($faq['category']) && $faq['category'] == '결제') selected @endif>결제</option>
                                                                    <option value="회원" @if (!empty($faq['category']) && $faq['category'] == '회원') selected @endif>회원</option>
                                                                    <option value="상품" @if (!empty($faq['category']) && $faq['category'] == '상품') selected @endif>상품</option>
                                                                    <option value="기타" @if (!empty($faq['category']) && $faq['category'] == '기타') selected @endif>기타</option>
                                                                </select>
                                                            </td>
                                                            <th class="w160"><span>정렬순서</span></th>
                                                            <td>
                                                                <select name="order" id="order" class="w300">
                                                                    @for ($i = 1; $i <= 100; $i++)
                                                                        <option value="{{ $i }}" @if (!empty($faq['order']) && $faq['order'] == $i) selected @elseif(empty($faq['id']) && $i == 1) selected @endif>{{ $i }}</option>
                                                                    @endfor
                                                                </select>
                                                                <p style="margin-top: 5px; font-size: 12px; color: #666;">※
                                                                    숫자가 작을수록 먼저 표시됩니다.</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>질문</span></th>
                                                            <td colspan="3">
                                                                <input type="text" class="wFull" id="question"
                                                                    placeholder="질문을 입력하세요" name="question" @if (!empty($faq['question']))
                                                                    value="{{ $faq['question'] }}" @else
                                                                    value="{{ old('question') }}" @endif required>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>답변</span></th>
                                                            <td colspan="3">
                                                                <textarea name="answer" id="answer" rows="10"
                                                                    style="width: 100%; padding: 10px; border: 1px solid #ddd;"
                                                                    required>{{ $faq['answer'] ?? '' }}</textarea>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>상태</span></th>
                                                            <td colspan="3">
                                                                <div style="display: flex; gap: 20px;">
                                                                    <label
                                                                        style="display: flex; align-items: center; cursor: pointer;">
                                                                        <input type="radio" name="status" value="1" @if (!empty($faq['status']) && $faq['status'] == 1) checked
                                                                        @elseif(empty($faq['id'])) checked @endif
                                                                            style="margin-right: 5px;">
                                                                        <span>노출</span>
                                                                    </label>
                                                                    <label
                                                                        style="display: flex; align-items: center; cursor: pointer;">
                                                                        <input type="radio" name="status" value="0" @if (isset($faq['status']) && $faq['status'] == 0) checked @endif
                                                                            style="margin-right: 5px;">
                                                                        <span>비노출</span>
                                                                    </label>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- 버튼 -->
                                        <div class="btm_btn center mt40"
                                            style="display: flex; justify-content: center; gap: 10px;">
                                            <a href="{{ url('admin/faqs') }}" class="btn01 col3">취소</a>
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

    <!-- CKEditor CDN -->
    <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>

    <script>
        $(document).ready(function () {
            // CKEditor 초기화
            CKEDITOR.replace('answer', {
                height: 400,
                language: 'ko',
                versionCheck: false, // 보안 경고 메시지 비활성화
                toolbar: [
                    { name: 'document', items: ['Source', '-', 'Preview'] },
                    { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
                    '/',
                    { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'] },
                    { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] },
                    { name: 'links', items: ['Link', 'Unlink'] },
                    { name: 'insert', items: ['Image', 'Table', 'HorizontalRule'] },
                    '/',
                    { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
                    { name: 'colors', items: ['TextColor', 'BGColor'] },
                    { name: 'tools', items: ['Maximize'] }
                ]
            });
        });
    </script>
@endsection
