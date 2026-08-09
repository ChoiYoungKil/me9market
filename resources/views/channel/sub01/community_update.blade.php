@extends('layouts.channel')

@section('content')
    @php
        $page_type = "sub";
        $dep1_id = "01";
        $dep1_tit = "Shop채널관리";
    @endphp
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">Shop채널 상세페이지</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>Shop채널관리</li>
                        <li>Shop채널 상세페이지</li>
                    </ul>
                </div>
                <div class="tab_bx1">
                    <ul>
                        <li><a href="{{ route('channel.shop_info') }}"><span>Shop채널 정보</span></a></li>
                        <li><a href="{{ route('channel.product_own') }}"><span>판매상품</span></a></li>
                        <li><a href="#" class="on"><span>커뮤니티</span></a></li>
                    </ul>
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger" style="margin: 20px; padding: 15px; background: #f8d7da; border: 1px solid #f5c6cb; border-radius: 4px; color: #721c24;">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (Session::has('success_message'))
                    <div class="alert alert-success" style="margin: 20px; padding: 15px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; color: #155724;">
                        {{ Session::get('success_message') }}
                    </div>
                @endif

                <div class="conbx">
                    <div class="con_w">
                        <form action="{{ route('channel.community.update.submit', $notice->id) }}" method="POST"
                            enctype="multipart/form-data" id="communityForm">
                            @csrf
                            <input type="hidden" name="shop_id" value="{{ $shopId }}">

                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="160px">
                                        <col width="">
                                        <col width="160px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>등록일</span></th>
                                            <td>{{ $notice->created_at->format('Y.m.d') }}</td>
                                            <th class="w160"><span>작성자</span></th>
                                            <td>
                                                <input type="text" name="author" value="{{ $notice->author }}" 
                                                    placeholder="Shop 채널명">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>분류</span></th>
                                            <td colspan="3">
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="type" id="type_notice" value="notice" 
                                                            {{ $notice->type == 'notice' ? 'checked' : '' }} required>
                                                        <label for="type_notice">공지</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="type" id="type_general" value="general"
                                                            {{ $notice->type == 'general' ? 'checked' : '' }} required>
                                                        <label for="type_general">일반</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>제목</span></th>
                                            <td colspan="3">
                                                <input type="text" name="title" value="{{ $notice->title }}" 
                                                    required="required" placeholder="게시판 제목입니다">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>내용</span></th>
                                            <td colspan="3">
                                                <textarea name="content" id="content" class="h2" required="required" 
                                                    placeholder="게시판 내용입니다">{{ $notice->content }}</textarea>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>첨부파일</span></th>
                                            <td colspan="3">
                                                @if($notice->attachment)
                                                <div style="margin-bottom: 10px;">
                                                    <span>현재 파일: </span>
                                                    <a href="{{ asset('uploads/notices/' . $notice->attachment) }}" 
                                                       target="_blank" style="color: #0066cc;">
                                                        {{ $notice->attachment }}
                                                    </a>
                                                    <label style="margin-left: 10px;">
                                                        <input type="checkbox" name="delete_attachment" value="1">
                                                        파일 삭제
                                                    </label>
                                                </div>
                                                @endif
                                                <div class="fileBox">
                                                    <input type="text" class="fileName" readonly="readonly" 
                                                        placeholder="새 파일을 선택하세요">
                                                    <label for="uploadBtn" class="btn_file">찾아보기</label>
                                                    <input type="file" id="uploadBtn" class="uploadBtn" name="attachment">
                                                    <div class="del_btn">삭제</div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="btm_btn mt10">
                                <a href="{{ route('channel.shop_community', ['shop_id' => $shopId]) }}"
                                    class="col5">목록</a>
                                <button type="button" id="submitCommunityForm">수정하기</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CKEditor CDN -->
    <script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>

    <script type="text/javascript">
        $(document).ready(function () {
            // CKEditor 초기화
            if(typeof CKEDITOR !== 'undefined') {
                CKEDITOR.replace('content', {
                    height: 400,
                    language: 'ko',
                    versionCheck: false,
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
            } else {
                console.error('CKEditor not loaded');
            }

            /* 파일 */
            var uploadFile = $('.fileBox .uploadBtn');
            uploadFile.on('change', function () {
                if (window.FileReader) {
                    var filename = $(this)[0].files[0].name;
                } else {
                    var filename = $(this).val().split('/').pop().split('\\').pop();
                }
                $(this).parents('.fileBox').find('.fileName').val(filename);
                $(this).parents('.fileBox').find('.fileName').addClass("on");
            });

            // 파일 삭제 버튼
            $('.fileBox .del_btn').on('click', function () {
                $(this).siblings('.fileName').val('').removeClass('on');
                $(this).siblings('.uploadBtn').val('');
            });

            // 수정하기 버튼
            $('#submitCommunityForm').on('click', function(e) {
                e.preventDefault();
                
                // CKEditor 내용을 textarea에 업데이트
                if(typeof CKEDITOR !== 'undefined') {
                    for (var instance in CKEDITOR.instances) {
                        CKEDITOR.instances[instance].updateElement();
                    }
                }
                
                // 폼 제출
                $('#communityForm').submit();
            });
        });
    </script>
@endsection
