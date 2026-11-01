@extends('layouts.admin')

@section('content')
<div id="contents" class="admin-workspace admin-product-options admin-product-images">
    <div class="admin-toolbar options-heading">
        <h1>상품 이미지 관리</h1>
        <div class="admin-actions">
            <a class="admin-btn" href="{{ url('admin/add-edit-product/'.$product->id) }}">상품 정보 수정</a>
            <a class="admin-btn" href="{{ url('admin/add-edit-attributes/'.$product->id) }}">옵션 관리</a>
            <a class="admin-btn" href="{{ url('admin/products') }}">상품 목록</a>
        </div>
    </div>
    @include('admin.shared.messages')
    <section class="admin-panel options-product">
        @if($product->product_image)
            <img src="{{ asset('front/images/product_images/small/'.$product->product_image) }}" alt="{{ $product->product_name }}">
        @else
            <div class="options-no-image">이미지 없음</div>
        @endif
        <div><h2>{{ $product->product_name }}</h2>
            <dl><dt>상품코드</dt><dd>{{ $product->product_code }}</dd><dt>색상</dt><dd>{{ $product->product_color ?: '미등록' }}</dd><dt>상품 판매가</dt><dd>{{ number_format($product->product_price) }}원</dd></dl>
        </div>
    </section>

    <section class="admin-panel options-section">
        <h2>추가 이미지 등록</h2>
        <p class="admin-help" id="product-image-help">한 번에 최대 10개 · 파일당 5MB 이하 · JPG, PNG, WEBP · 가로·세로 각각 4,096px 이하<br>대표이미지는 ‘상품 정보 수정’에서 변경할 수 있습니다. 선택한 파일은 아래에서 확인한 뒤 등록해 주세요.</p>
        <form method="post" action="{{ url('admin/add-images/'.$product->id) }}" enctype="multipart/form-data" id="product-image-form">
            @csrf
            <div class="product-image-upload">
                <label for="images" class="admin-btn">이미지 파일 선택</label>
                <input type="file" name="images[]" multiple id="images" accept="image/jpeg,image/png,image/webp" aria-describedby="product-image-help" class="image-file-input" required>
                <p id="image-selection-summary" role="status" aria-live="polite">선택한 이미지가 없습니다.</p>
            </div>
            <p id="image-selection-error" class="admin-alert error" role="alert" hidden></p>
            <div id="image-selection-preview" class="image-preview-grid"></div>
            <div class="admin-toolbar options-form-actions">
                <button type="reset" class="admin-btn" id="clear-image-selection" disabled>선택 취소</button>
                <button type="submit" class="admin-btn primary" id="upload-product-images" disabled>이미지 등록</button>
            </div>
        </form>
    </section>

    <section class="admin-panel options-section">
        <h2>등록된 추가 이미지 <span class="options-count">{{ count($product->images) }}개</span></h2>
        <p class="admin-help">‘크게 보기’에서 이미지를 확인하거나 다운로드할 수 있습니다. ‘노출중’을 누르면 추가 이미지를 숨길 수 있습니다.</p>
        <p id="product-image-status-feedback" class="admin-alert" role="status" aria-live="polite" hidden></p>
        <div class="admin-table-wrap"><table class="admin-table product-images-table">
            <colgroup><col style="width:80px"><col style="width:130px"><col><col style="width:120px"><col style="width:220px"></colgroup>
            <thead><tr><th>번호</th><th>이미지</th><th>파일 정보</th><th>노출 상태</th><th>관리</th></tr></thead>
            <tbody>
            @forelse($product->images as $image)
                <tr><td>{{ $image->id }}</td>
                    <td><button type="button" class="image-thumb-button" data-dialog-open="product-image-{{ $image->id }}" aria-label="추가 이미지 {{ $loop->iteration }} 크게 보기"><img src="{{ asset('front/images/product_images/small/'.$image->image) }}" alt="추가 이미지 {{ $loop->iteration }}"></button></td>
                    <td class="image-file-info"><strong>추가 이미지 {{ $loop->iteration }}</strong><small>{{ $image->image }}</small></td>
                    <td><button class="admin-btn small {{ $image->status ? 'primary' : '' }}" type="button" data-product-image-status="{{ $image->id }}" data-active="{{ $image->status ? '1' : '0' }}" aria-label="추가 이미지 {{ $loop->iteration }} {{ $image->status ? '숨김으로 변경' : '노출중으로 변경' }}">{{ $image->status ? '노출중' : '숨김' }}</button></td>
                    <td><div class="options-row-actions"><button type="button" class="admin-btn small primary" data-dialog-open="product-image-{{ $image->id }}">크게 보기</button><button type="button" class="admin-btn small danger" data-admin-delete="{{ url('admin/delete-image/'.$image->id) }}">삭제</button></div></td>
                </tr>
            @empty
                <tr><td colspan="5" class="admin-empty">등록된 추가 이미지가 없습니다. 위에서 이미지를 선택해 등록해 주세요.</td></tr>
            @endforelse
            </tbody>
        </table></div>
    </section>
    @foreach($product->images as $image)
        <dialog class="admin-modal product-image-dialog" id="product-image-{{ $image->id }}" aria-labelledby="product-image-title-{{ $image->id }}">
            <div class="admin-modal-header"><h2 id="product-image-title-{{ $image->id }}">추가 이미지 {{ $loop->iteration }}</h2><button type="button" class="admin-btn" data-dialog-close>닫기</button></div>
            <img src="{{ asset('front/images/product_images/large/'.$image->image) }}" alt="추가 이미지 {{ $loop->iteration }} 원본 크기">
            <div class="admin-modal-footer"><a class="admin-btn primary" href="{{ asset('front/images/product_images/large/'.$image->image) }}" download>이미지 다운로드</a><button type="button" class="admin-btn" data-dialog-close>닫기</button></div>
        </dialog>
    @endforeach
</div>
@endsection

@push('scripts')
<script src="{{ asset('common/admin-product-images.js') }}?v={{ filemtime(public_path('common/admin-product-images.js')) }}"></script>
@endpush
