@extends('layouts.admin')

@section('content')
<div id="contents" class="admin-workspace admin-legacy admin-account-editor admin-simple-setting-editor admin-banner-editor">
    <div class="page_info"><h1 class="ttl">{{ $title }}</h1><ul class="dep"><li>홈</li><li>상품 관리</li><li>배너 관리</li><li>{{ $title }}</li></ul></div>
    @include('admin.shared.messages')
    <form id="banner-editor-form" method="post" action="{{ url('admin/add-edit-banner'.($banner->id ? '/'.$banner->id : '')) }}" enctype="multipart/form-data">
        @csrf
        <div id="board"><div class="write02"><div class="f_bx">
            <section class="f_w">
                <h2 class="f_ttl">배너 정보</h2>
                <div class="tb01"><table class="two">
                    <colgroup><col><col><col><col></colgroup>
                    <tbody class="textL"><tr>
                        <th><label for="type">배너 유형</label></th>
                        <td><select class="w300" name="type" id="type" required>
                            <option value="">선택해 주세요</option>
                            <option value="Slider" @selected(old('type', $banner->type) === 'Slider')>슬라이드</option>
                            <option value="Fix" @selected(old('type', $banner->type) === 'Fix')>고정</option>
                        </select></td>
                        <th><label for="title">배너 제목</label></th>
                        <td><input class="w300" type="text" name="title" id="title" value="{{ old('title', $banner->title) }}" placeholder="배너 제목을 입력해 주세요" maxlength="255"></td>
                    </tr></tbody>
                </table></div>
            </section>
            <section class="f_w">
                <h2 class="f_ttl">배너 이미지</h2>
                <div class="tb01"><table class="two">
                    <colgroup><col><col><col><col></colgroup>
                    <tbody class="textL"><tr>
                        <th><label for="image">이미지 파일</label></th>
                        <td colspan="3">
                            <div class="fileBox">
                                <input class="fileName" id="banner-image-filename" type="text" value="" placeholder="이미지 파일을 선택해 주세요" readonly aria-label="선택한 이미지 파일명">
                                <label for="image" class="btn_file admin-btn">파일 선택</label>
                                <input class="uploadBtn" type="file" name="image" id="image" accept="image/jpeg,image/png,image/webp" @required(!$banner->image) aria-describedby="banner-image-help">
                            </div>
                            <p class="admin-help" id="banner-image-help">JPG, PNG, WEBP · 최대 5MB · 가로·세로 최대 8,192px · 저장 크기: 슬라이드 1,920×720px / 고정 1,920×450px</p>
                            <p class="admin-alert error" id="banner-image-error" role="alert" hidden></p>
                            <figure class="banner-image-preview" id="banner-image-preview" data-current-src="{{ $banner->image ? asset('front/images/banner_images/'.$banner->image) : '' }}" @if(!$banner->image) hidden @endif>
                                <img id="banner-preview-image" @if($banner->image) src="{{ asset('front/images/banner_images/'.$banner->image) }}" @endif alt="배너 이미지 미리보기">
                                <figcaption id="banner-preview-caption">등록된 이미지</figcaption>
                            </figure>
                            @if($banner->image)
                                <a class="admin-btn" target="_blank" rel="noopener" href="{{ asset('front/images/banner_images/'.$banner->image) }}">기존 이미지 보기</a>
                            @endif
                        </td>
                    </tr></tbody>
                </table></div>
            </section>
            <section class="f_w">
                <h2 class="f_ttl">연결 및 이미지 설명</h2>
                <div class="tb01"><table class="two">
                    <colgroup><col><col><col><col></colgroup>
                    <tbody class="textL">
                        <tr><th><label for="link">연결 주소</label></th><td colspan="3"><input class="wFull" type="text" name="link" id="link" value="{{ old('link', $banner->link) }}" placeholder="연결 주소를 입력해 주세요" maxlength="2048"></td></tr>
                        <tr><th><label for="alt">이미지 설명</label></th><td colspan="3"><input class="wFull" type="text" name="alt" id="alt" value="{{ old('alt', $banner->alt) }}" placeholder="이미지를 설명하는 문구를 입력해 주세요" maxlength="255"><p class="admin-help">이미지가 표시되지 않을 때 제공하는 대체 텍스트입니다.</p></td></tr>
                    </tbody>
                </table></div>
            </section>
            <div class="admin-modal-footer account-editor-footer">
                <a class="admin-btn" href="{{ url('admin/banners') }}">취소</a>
                <button class="admin-btn" type="reset">입력 초기화</button>
                <button class="admin-btn primary" type="submit">저장</button>
            </div>
        </div></div></div>
    </form>
</div>
@endsection

@push('scripts')
<script src="{{ asset('common/admin-banner-editor.js') }}?v={{ filemtime(public_path('common/admin-banner-editor.js')) }}"></script>
@endpush
