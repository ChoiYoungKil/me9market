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
                        <li>공지사항</li>
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
                        <form @if (empty($notice['id'])) action="{{ url('admin/add-edit-notice') }}" @else
                        action="{{ url('admin/add-edit-notice/' . $notice['id']) }}" @endif method="post"
                            enctype="multipart/form-data">
                            @csrf

                            <div id="board">
                                <div class="write02">
                                    <div class="f_bx">
                                        <!-- 기본 정보 -->
                                        <div class="f_w">
                                            <div class="f_ttl">공지사항 정보</div>
                                            <div class="tb01">
                                                <table class="two">
                                                    <tbody class="textL">
                                                        <tr>
                                                            <th class="w160"><span>제목</span></th>
                                                            <td colspan="3">
                                                                <input type="text" class="wFull" id="title"
                                                                    placeholder="제목을 입력하세요" name="title" @if (!empty($notice['title']))
                                                                    value="{{ $notice['title'] }}" @else
                                                                    value="{{ old('title') }}" @endif required>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>내용</span></th>
                                                            <td colspan="3">
                                                                <textarea name="content" id="content" rows="10"
                                                                    style="width: 100%; padding: 10px; border: 1px solid #ddd;"
                                                                    required>{{ $notice['content'] ?? '' }}</textarea>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>첨부파일</span></th>
                                                            <td colspan="3">
                                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                                    <input type="file" id="attachment" name="attachment"
                                                                        style="flex: 1;">
                                                                    @if (!empty($notice['attachment']))
                                                                        <div
                                                                            style="display: flex; align-items: center; gap: 10px; padding: 8px 12px; background: #f5f5f5; border-radius: 4px;">
                                                                            <span style="font-size: 14px;">현재 파일:</span>
                                                                            <a target="_blank"
                                                                                href="{{ url('admin/attachments/notices/' . $notice['attachment']) }}"
                                                                                style="color: #3470f7; text-decoration: underline;">{{ $notice['attachment'] }}</a>
                                                                            <a href="JavaScript:void(0)" class="confirmDelete"
                                                                                module="notice-attachment"
                                                                                moduleid="{{ $notice['id'] }}"
                                                                                style="color: #dc3545; font-weight: bold; margin-left: 10px;">✕
                                                                                삭제</a>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <p style="margin-top: 5px; font-size: 12px; color: #666;">※
                                                                    파일 형식: PDF, DOC, DOCX, XLS, XLSX, ZIP, JPG, PNG (최대
                                                                    10MB)</p>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <th class="w160"><span>옵션</span></th>
                                                            <td>
                                                                <label style="display: flex; align-items: center; cursor: pointer;">
                                                                    <input type="checkbox" name="is_important"
                                                                        id="is_important" value="1" @if (!empty($notice['is_important']) && $notice['is_important'] == 1) checked @endif
                                                                        style="margin-right: 5px; width: 16px; height: 16px;">
                                                                    <span>중요 공지사항</span>
                                                                </label>
                                                            </td>
                                                            <th class="w160"><span>상태</span></th>
                                                            <td>
                                                                <div style="display: flex; gap: 20px;">
                                                                    <label
                                                                        style="display: flex; align-items: center; cursor: pointer;">
                                                                        <input type="radio" name="status" value="1" @if (!empty($notice['status']) && $notice['status'] == 1) checked
                                                                        @elseif(empty($notice['id'])) checked @endif
                                                                            style="margin-right: 5px;">
                                                                        <span>노출</span>
                                                                    </label>
                                                                    <label
                                                                        style="display: flex; align-items: center; cursor: pointer;">
                                                                        <input type="radio" name="status" value="0" @if (isset($notice['status']) && $notice['status'] == 0) checked @endif
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
                                            <a href="{{ url('admin/notices') }}" class="btn01 col3">취소</a>
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
            CKEDITOR.replace('content', {
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

            // Confirm Delete
            $(".confirmDelete").click(function (e) {
                var module = $(this).attr('module');
                var moduleid = $(this).attr('moduleid');
                if (!confirm("정말로 삭제하시겠습니까?")) {
                    return false;
                }
                if (module == 'notice-attachment') {
                    window.location.href = "/admin/delete-notice-attachment/" + moduleid;
                }
            });
        });
    </script>
@endsection
