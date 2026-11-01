@extends('layouts.admin')

@section('content')
@php($formTitle = $brand->exists ? '브랜드 수정' : '브랜드 추가')
<div id="contents" class="admin-workspace admin-legacy admin-account-editor admin-brand-editor admin-simple-setting-editor">
    <div class="page_info">
        <h1 class="ttl">{{ $formTitle }}</h1>
        <ul class="dep"><li>홈</li><li>상품 관리</li><li>브랜드 관리</li><li>{{ $formTitle }}</li></ul>
    </div>
    @include('admin.shared.messages')
    <form method="post" action="{{ url('admin/add-edit-brand'.($brand->id ? '/'.$brand->id : '')) }}">
        @csrf
        <div id="board"><div class="write02"><div class="f_bx">
            <section class="f_w">
                <h2 class="f_ttl">기본 정보</h2>
                <div class="tb01"><table class="two">
                    <colgroup><col><col><col><col></colgroup>
                    <tbody class="textL"><tr>
                        <th><label for="brand_name">브랜드명</label></th>
                        <td colspan="3"><input class="w300" type="text" id="brand_name" name="brand_name" value="{{ old('brand_name', $brand->name) }}" placeholder="브랜드명을 입력해 주세요" required></td>
                    </tr></tbody>
                </table></div>
            </section>
            <div class="admin-modal-footer account-editor-footer">
                <a class="admin-btn" href="{{ url('admin/brands') }}">취소</a>
                <button class="admin-btn" type="reset">입력 초기화</button>
                <button class="admin-btn primary" type="submit">저장</button>
            </div>
        </div></div></div>
    </form>
</div>
@endsection
