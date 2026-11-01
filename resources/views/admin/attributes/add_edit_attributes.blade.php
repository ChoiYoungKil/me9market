@extends('layouts.admin')

@section('content')
<div id="contents" class="admin-workspace admin-product-options">
    <div class="admin-toolbar options-heading">
        <h1>상품 옵션 관리</h1>
        <div class="admin-actions">
            <a class="admin-btn" href="{{ url('admin/add-edit-product/'.$product->id) }}">상품 정보 수정</a>
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
        <h2>옵션 추가</h2>
        <p class="admin-help">옵션명·옵션코드·옵션 판매가·재고를 입력해 주세요. ‘입력행 추가’로 여러 옵션을 함께 등록할 수 있습니다. 옵션코드는 중복될 수 없습니다.</p>
        <form method="post" action="{{ url('admin/add-edit-attributes/'.$product->id) }}" id="option-create-form">
            @csrf
            <input type="hidden" name="form_kind" value="add">
            <div id="option-input-rows">
                @php($sizes = old('form_kind') === 'add' ? old('size', ['']) : [''])
                @foreach($sizes as $row => $size)
                    <div class="option-input-row">
                        <label>옵션명 (규격)<input name="size[]" value="{{ $size }}" placeholder="예: 기본, 대형, 250mm" maxlength="255" required></label>
                        <label>옵션코드<input name="sku[]" value="{{ old('form_kind') === 'add' ? old('sku.'.$row) : '' }}" placeholder="예: 상품코드-01" maxlength="255" required></label>
                        <label>옵션 판매가 (원)<input name="price[]" value="{{ old('form_kind') === 'add' ? old('price.'.$row) : '' }}" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0" required></label>
                        <label>재고 (개)<input name="stock[]" value="{{ old('form_kind') === 'add' ? old('stock.'.$row) : '' }}" type="number" min="0" max="2147483647" step="1" inputmode="numeric" placeholder="0" required></label>
                        <button type="button" class="admin-btn danger" data-remove-option @disabled(count($sizes) === 1)>입력행 삭제</button>
                    </div>
                @endforeach
            </div>
            <div class="admin-toolbar options-form-actions"><button class="admin-btn" type="button" id="add-option-row">입력행 추가</button><button class="admin-btn primary" type="submit">옵션 등록</button></div>
            <p class="admin-help" id="option-row-feedback" role="status" aria-live="polite"></p>
        </form>
    </section>

    <section class="admin-panel options-section">
        <div class="admin-toolbar"><h2>등록된 옵션 <span class="options-count">{{ count($product->attributes) }}개</span></h2></div>
        <p class="admin-help">옵션 판매가와 재고를 변경한 후 ‘변경사항 저장’을 눌러 주세요. 판매중지한 옵션은 구매할 수 없습니다.</p>
        <p id="option-status-feedback" class="admin-alert" role="status" aria-live="polite" hidden></p>
        <form method="post" action="{{ url('admin/edit-attributes/'.$product->id) }}">
            @csrf
            <input type="hidden" name="form_kind" value="edit">
            <div class="admin-table-wrap"><table class="admin-table options-table">
                <colgroup><col style="width:70px"><col><col style="width:230px"><col style="width:170px"><col style="width:145px"><col style="width:210px"></colgroup>
                <thead><tr><th>번호</th><th>옵션명 (규격)</th><th>옵션코드</th><th>옵션 판매가 (원)</th><th>재고 (개)</th><th>관리</th></tr></thead>
                <tbody>
                @forelse($product->attributes as $attribute)
                    @php($oldIndex = old('form_kind') === 'edit' ? array_search((string) $attribute->id, array_map('strval', old('attributeId', [])), true) : false)
                    <tr><td>{{ $attribute->id }}<input type="hidden" name="attributeId[]" value="{{ $attribute->id }}"></td>
                        <td class="options-name">{{ $attribute->size }}@if($attribute->option_type === 'text')<small>직접입력형 · 가격 유지</small>@endif</td><td class="options-code">{{ $attribute->sku }}</td>
                        <td><input type="number" name="price[]" value="{{ $oldIndex !== false ? old('price.'.$oldIndex) : $attribute->price }}" min="0" step="0.01" inputmode="decimal" required @readonly($attribute->option_type === 'text') aria-label="{{ $attribute->size }} 옵션 판매가"></td>
                        <td><input type="number" name="stock[]" value="{{ $oldIndex !== false ? old('stock.'.$oldIndex) : $attribute->stock }}" min="0" max="2147483647" step="1" inputmode="numeric" required aria-label="{{ $attribute->size }} 재고"></td>
                        <td><div class="options-row-actions"><button class="admin-btn small {{ $attribute->status ? 'primary' : '' }}" type="button" data-option-status="{{ $attribute->id }}" data-active="{{ $attribute->status ? '1' : '0' }}" aria-label="{{ $attribute->size }} {{ $attribute->status ? '판매중지로 변경' : '판매중으로 변경' }}">{{ $attribute->status ? '판매중' : '판매중지' }}</button><button class="admin-btn small danger" type="button" data-admin-delete="{{ url('admin/delete-attribute/'.$attribute->id) }}">삭제</button></div></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="admin-empty">등록된 옵션이 없습니다. 위에서 옵션을 추가해 주세요.</td></tr>
                @endforelse
                </tbody>
            </table></div>
            @if(count($product->attributes))
                <div class="admin-modal-footer"><a href="{{ url('admin/products') }}" class="admin-btn">목록</a><button type="submit" class="admin-btn primary">변경사항 저장</button></div>
            @endif
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script src="{{ asset('common/admin-product-options.js') }}?v={{ filemtime(public_path('common/admin-product-options.js')) }}"></script>
@endpush
