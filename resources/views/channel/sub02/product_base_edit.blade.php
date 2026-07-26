@extends('layouts.channel')

@section('page_type', 'sub')

@php
    $dep1_id = "02";
    $dep1_tit = "상품관리";
    $isCreate = $isCreate ?? false;
    $pageTitle = $isCreate ? '자사 상품 등록' : '자사 상품 수정';
    
    $registeredImages = !$isCreate && $product->relationLoaded('images') ? $product->images : collect();
    $mainImage = $registeredImages->first();
    $mainImageName = $product->product_image ?: ($mainImage->image ?? null);
    $imageUrl = $mainImageName ? asset('front/images/product_images/small/' . $mainImageName) : asset('channel_assets/images/sub/thum01.jpg');
    $additionalImages = $registeredImages
        ->filter(fn ($image) => !empty($image->image) && $image->image !== $mainImageName)
        ->values();
    $existingAdditionalImageCount = $additionalImages->count();
    $categorySelection = $categorySelection ?? ['major' => null, 'middle' => null, 'minor' => null, 'final' => null];
    $selectedMajorCategoryId = old('major_category_id', $categorySelection['major']);
    $selectedMiddleCategoryId = old('middle_category_id', $categorySelection['middle']);
    $selectedMinorCategoryId = old('minor_category_id', $categorySelection['minor']);
    $selectedFinalCategoryId = old('category_id', $categorySelection['final'] ?? $product->category_id);
    $productNoticeItems = old('product_notice_items', $productNoticeItems ?? ($product->product_notice_items ?? []));
    $productNoticeItems = is_array($productNoticeItems) ? $productNoticeItems : [];
    $productOptions = $productOptions ?? [];
    if (old('option_values') !== null) {
        $oldOptionIds = old('option_ids', []);
        $oldOptionNames = old('option_names', []);
        $oldOptionTypes = old('option_types', []);
        $oldOptionValues = old('option_values', []);
        $oldOptionSkus = old('option_skus', []);
        $oldOptionPrices = old('option_prices', []);
        $oldOptionStocks = old('option_stocks', []);
        $oldOptionStatuses = old('option_statuses', []);
        $productOptions = collect($oldOptionValues)->map(function ($value, $index) use ($oldOptionIds, $oldOptionNames, $oldOptionTypes, $oldOptionSkus, $oldOptionPrices, $oldOptionStocks, $oldOptionStatuses) {
            return [
                'id' => $oldOptionIds[$index] ?? null,
                'option_name' => $oldOptionNames[$index] ?? '기본옵션',
                'option_type' => $oldOptionTypes[$index] ?? 'general',
                'option_value' => $value,
                'sku' => $oldOptionSkus[$index] ?? '',
                'price' => $oldOptionPrices[$index] ?? 0,
                'stock' => $oldOptionStocks[$index] ?? 0,
                'status' => $oldOptionStatuses[$index] ?? 1,
            ];
        })->values()->toArray();
    }
    if (empty($productOptions)) {
        $productOptions = [[
            'id' => null,
            'option_name' => '기본옵션',
            'option_type' => 'general',
            'option_value' => '',
            'sku' => '',
            'price' => old('product_price', $product->product_price ?: 0),
            'stock' => 0,
            'status' => 1,
        ]];
    }
    $selectedSaleScope = old('sale_scope', $product->sale_scope ?? (($product->is_partial === 'Yes') ? 'affiliate' : (($product->is_public === 'Yes') ? 'public' : 'own')));
    $selectedTaxType = old('tax_type', $product->tax_type ?? 'taxable');
    $priceConstraintEnabled = (string) old('price_constraint_enabled', (int) ($product->price_constraint_enabled ?? 0));
    $priceConstraintType = old('price_constraint_type', $product->price_constraint_type ?? 'range');
    $purchaseLimitEnabled = (string) old('purchase_limit_enabled', (int) ($product->purchase_limit_enabled ?? 0));
    $stockUsage = old('stock_usage', $product->stock_usage ?? 'unused');
    $detailDisplayType = old('detail_display_type', $product->detail_display_type ?? 'unused');
    $orderManagerEnabled = (string) old('order_manager_enabled', (int) ($product->order_manager_enabled ?? 0));
    $selectedDistributorId = old('distributor_id', $product->distributor_id ?? '');
    $shippingPolicyType = old('shipping_policy_type', $product->shipping_policy_type ?? 'free_conditional');
    $shippingPaymentType = old('shipping_payment_type', $product->shipping_payment_type ?? 'prepaid');
    $selectedCancelRefundPolicyId = old('cancel_refund_policy_id', $product->cancel_refund_policy_id ?? '');
    $selectedProfitShareType = old('profit_share_type', $product->profit_share_type ?? 'none');
    $rawProductColor = old('product_color', $product->product_color ?? '#000000');
    $namedColors = [
        '검정' => '#000000',
        '검은색' => '#000000',
        'black' => '#000000',
        '흰색' => '#ffffff',
        '하양' => '#ffffff',
        'white' => '#ffffff',
        '빨강' => '#ff0000',
        'red' => '#ff0000',
        '파랑' => '#0000ff',
        'blue' => '#0000ff',
        '초록' => '#008000',
        'green' => '#008000',
    ];
    $selectedProductColor = is_string($rawProductColor) && preg_match('/^#[0-9a-fA-F]{6}$/', $rawProductColor)
        ? strtolower($rawProductColor)
        : ($namedColors[trim((string) $rawProductColor)] ?? '#000000');
    $detailPcImageUrl = !empty($product->detail_pc_image) ? asset('front/images/product_detail_images/' . $product->detail_pc_image) : null;
    $detailMobileImageUrl = !empty($product->detail_mobile_image) ? asset('front/images/product_detail_images/' . $product->detail_mobile_image) : null;
@endphp

@section('content')
    <style>
        .product-base-form .field-line {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            min-height: 40px;
        }
        .product-base-form .field-line .chk01 {
            display: inline-flex;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 0;
            vertical-align: middle;
        }
        .product-base-form .field-line .chk01 li {
            margin-bottom: 0;
            margin-right: 20px;
        }
        .product-base-form .inline-fields {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .product-base-form .inline-fields input[type="text"] {
            width: 104px !important;
            max-width: 104px !important;
        }
        .product-base-form .inline-fields select,
        .product-base-form select.compact-select {
            width: 180px !important;
            max-width: 180px !important;
        }
        .product-base-form select.order-manager-select {
            width: 280px !important;
            max-width: 280px !important;
        }
        .product-base-form select.cancel-refund-select {
            width: 320px !important;
            max-width: 320px !important;
        }
        .product-base-form .policy-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 40px;
            border: 1px solid #d0d5dd;
            padding: 0 12px;
            background: #fff;
            border-radius: 5px;
            white-space: nowrap;
        }
        .product-base-form .inline-fields.disabled-control input {
            background-color: #eeeeee !important;
            color: #999999;
        }
	        .product-base-form .stock-column.disabled-control input {
	            background-color: #eeeeee;
	            color: #999999;
	        }
	        .product-base-form .cke {
	            border-color: #eeeeee;
	            border-radius: 5px;
	            overflow: hidden;
	        }
    </style>
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">{{ $pageTitle }}</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>상품관리</li>
                        <li>{{ $pageTitle }}</li>
                    </ul>
                </div>
                
                <div class="tab_bx1">
                    <ul>
                        <li><a href="{{ route('channel.product_own') }}" class="on"><span>자사상품관리</span></a></li>
                        <li><a href="{{ route('channel.product_public') }}"><span>공개상품관리</span></a></li>
                        <li><a href="{{ route('channel.product_partial') }}"><span>부분공개상품관리</span></a></li>
                        <li><a href="{{ route('channel.product_request') }}"><span>판매 요청 관리</span></a></li>
                    </ul>
                </div>

                <div class="conbx">
                    <form class="product-base-form" action="{{ $isCreate ? route('channel.product.base.store') : route('channel.product.base.update', $product->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="con_w">
                            <div class="ttl01">기본 정보</div>
                            <div class="tb01 textL">
                                <table>
                                    <colgroup>
                                        <col width="160px"><col width=""><col width="160px"><col width="">
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <th>상품코드</th>
                                            <td>
                                                @if($isCreate)
                                                    <input type="text" name="product_code" value="{{ old('product_code', $product->product_code) }}" class="wFull" required>
                                                @else
                                                    {{ $product->product_code }}
                                                @endif
                                            </td>
                                            <th>상품상태</th>
                                            <td>
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="status" id="status_1" value="1" {{ old('status', $product->status) == 1 ? 'checked' : '' }}>
                                                        <label for="status_1">판매</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="status" id="status_0" value="0" {{ old('status', $product->status) == 0 ? 'checked' : '' }}>
                                                        <label for="status_0">중지</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>상품분류</th>
                                            <td colspan="3">
                                                <input type="hidden" name="category_id" id="category_id" value="{{ $selectedFinalCategoryId }}">
                                                <ul class="type_bx w600" style="display:flex; gap:8px; flex-wrap:wrap;">
                                                    <li>
                                                        <select name="major_category_id" id="major_category_id" required>
                                                            <option value="">대분류</option>
                                                            @foreach($categories as $category)
                                                                <option value="{{ $category->id }}" {{ (string) $selectedMajorCategoryId === (string) $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </li>
                                                    <li>
                                                        <select name="middle_category_id" id="middle_category_id" required>
                                                            <option value="">중분류</option>
                                                        </select>
                                                    </li>
                                                    <li>
                                                        <select name="minor_category_id" id="minor_category_id">
                                                            <option value="">소분류</option>
                                                        </select>
                                                    </li>
                                                    <li>
                                                        <a href="{{ route('channel.product.categories') }}" style="display:inline-flex; align-items:center; justify-content:center; height:38px; border:1px solid #d0d5dd; padding:0 12px; background:#fff;">분류관리</a>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>브랜드</th>
                                            <td colspan="3">
                                                <select name="brand_id" required>
                                                    <option value="">브랜드 선택</option>
                                                    @foreach($brands as $brand)
                                                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>상품명</th>
                                            <td colspan="3">
                                                <input type="text" name="product_name" value="{{ old('product_name', $product->product_name) }}" class="wFull" required>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>기본 원가</th>
                                            <td>
                                                <input type="text" name="product_price" value="{{ old('product_price', $product->product_price) }}" class="w160" required> 원
                                            </td>
                                            <th>상품 색상</th>
                                            <td>
                                                <input type="color" name="product_color" value="{{ $selectedProductColor }}" title="상품 색상 선택" style="width:48px; height:34px; padding:2px; border:1px solid #ddd; background:#fff; vertical-align:middle;">
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>판매범위</th>
                                            <td colspan="3">
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="sale_scope" id="sale_scope_own" value="own" {{ $selectedSaleScope === 'own' ? 'checked' : '' }}>
                                                        <label for="sale_scope_own">자사</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="sale_scope" id="sale_scope_public" value="public" {{ $selectedSaleScope === 'public' ? 'checked' : '' }}>
                                                        <label for="sale_scope_public">공개</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="sale_scope" id="sale_scope_affiliate" value="affiliate" {{ $selectedSaleScope === 'affiliate' ? 'checked' : '' }}>
                                                        <label for="sale_scope_affiliate">제휴</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>지급 포인트</th>
                                            <td>
                                                <input type="text" name="reward_points" value="{{ old('reward_points', $product->reward_points ?? 0) }}" inputmode="numeric" pattern="[0-9]*" class="w160"> P
                                            </td>
                                            <th>과세구분</th>
                                            <td>
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="tax_type" id="tax_type_taxable" value="taxable" {{ $selectedTaxType === 'taxable' ? 'checked' : '' }}>
                                                        <label for="tax_type_taxable">과세</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="tax_type" id="tax_type_tax_free" value="tax_free" {{ $selectedTaxType === 'tax_free' ? 'checked' : '' }}>
                                                        <label for="tax_type_tax_free">면세</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="tax_type" id="tax_type_zero_rated" value="zero_rated" {{ $selectedTaxType === 'zero_rated' ? 'checked' : '' }}>
                                                        <label for="tax_type_zero_rated">영세</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>할인율</th>
                                            <td>
                                                <input type="text" name="product_discount" value="{{ old('product_discount', $product->product_discount ?? 0) }}" class="w160"> %
                                            </td>
                                            <th>상품무게</th>
                                            <td>
                                                <input type="text" name="product_weight" value="{{ old('product_weight', $product->product_weight ?? 1) }}" class="w160"> kg
                                            </td>
                                        </tr>
	                                        <tr>
	                                            <th>상품설명</th>
	                                            <td colspan="3">
	                                                <textarea name="description" id="product_description_editor" class="wFull">{{ old('description', $product->description) }}</textarea>
	                                            </td>
	                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            @error('major_category_id')
                                <p class="fcol1 mt10">{{ $message }}</p>
                            @enderror
                            @error('middle_category_id')
                                <p class="fcol1 mt10">{{ $message }}</p>
                            @enderror
	                            @error('minor_category_id')
	                                <p class="fcol1 mt10">{{ $message }}</p>
	                            @enderror
	                            @error('description')
	                                <p class="fcol1 mt10">{{ $message }}</p>
	                            @enderror
	                        </div>

                        <div class="con_w" id="product_notice_section" style="display:none;">
                            <div class="ttl01">상품 유형별 필수 표시 사항 <span id="product_notice_title" style="font-size:13px; color:#666;"></span></div>
                            <input type="hidden" name="product_notice_type" id="product_notice_type" value="{{ old('product_notice_type', $product->product_notice_type ?? '') }}">
                            <div class="tb01 textL">
                                <table>
                                    <colgroup>
                                        <col width="220px"><col width="">
                                    </colgroup>
                                    <tbody id="product_notice_body"></tbody>
                                </table>
                            </div>
                            <p class="mt5" style="font-size:12px; color:#888;">대분류가 상품정보고시 대상 품목군이면 입력 항목이 자동으로 표시됩니다.</p>
                            @error('product_notice_items')
                                <p class="fcol1 mt10">{{ $message }}</p>
                            @enderror
                            @error('product_notice_items.*')
                                <p class="fcol1 mt10">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="con_w">
                            <div class="ttl01">상품 제약 조건</div>
                            <div class="tb01 textL">
                                <table>
                                    <colgroup>
                                        <col width="180px"><col width=""><col width="180px"><col width="">
                                    </colgroup>
                                    <tbody>
	                                        <tr>
	                                            <th>판매금액 제약</th>
	                                            <td colspan="3">
	                                                <div class="field-line">
	                                                    <ul class="chk01">
	                                                        <li>
	                                                            <input type="radio" name="price_constraint_enabled" id="price_constraint_disabled" value="0" {{ $priceConstraintEnabled !== '1' ? 'checked' : '' }}>
	                                                            <label for="price_constraint_disabled">사용안함</label>
	                                                        </li>
	                                                        <li>
	                                                            <input type="radio" name="price_constraint_enabled" id="price_constraint_enabled" value="1" {{ $priceConstraintEnabled === '1' ? 'checked' : '' }}>
	                                                            <label for="price_constraint_enabled">사용</label>
	                                                        </li>
	                                                    </ul>
	                                                    <div id="price_constraint_detail" class="field-line">
	                                                        <ul class="chk01">
	                                                            <li>
	                                                                <input type="radio" name="price_constraint_type" id="price_constraint_range" value="range" {{ $priceConstraintType === 'range' ? 'checked' : '' }}>
	                                                                <label for="price_constraint_range">범위 제한</label>
	                                                            </li>
	                                                            <li>
	                                                                <input type="radio" name="price_constraint_type" id="price_constraint_fixed" value="fixed" {{ $priceConstraintType === 'fixed' ? 'checked' : '' }}>
	                                                                <label for="price_constraint_fixed">고정 금액</label>
	                                                            </li>
	                                                        </ul>
	                                                        <span class="price-range-fields inline-fields">
	                                                            <input type="text" name="price_min" value="{{ old('price_min', $product->price_min ?? '') }}" inputmode="decimal"> 원 이상
	                                                            <input type="text" name="price_max" value="{{ old('price_max', $product->price_max ?? '') }}" inputmode="decimal"> 원 이하
	                                                        </span>
	                                                        <span class="price-fixed-field inline-fields">
	                                                            <input type="text" name="price_fixed" value="{{ old('price_fixed', $product->price_fixed ?? '') }}" inputmode="decimal"> 원
	                                                        </span>
	                                                    </div>
	                                                </div>
	                                            </td>
	                                        </tr>
                                        <tr>
	                                            <th>수익배분</th>
	                                            <td>
	                                                <div class="field-line">
	                                                    <select name="profit_share_type" id="profit_share_type" class="compact-select">
	                                                        <option value="none" {{ $selectedProfitShareType === 'none' ? 'selected' : '' }}>사용안함</option>
	                                                        <option value="fixed" {{ $selectedProfitShareType === 'fixed' ? 'selected' : '' }}>정액</option>
	                                                        <option value="percent" {{ $selectedProfitShareType === 'percent' ? 'selected' : '' }}>정률</option>
	                                                    </select>
	                                                    <span id="profit_share_value_wrap" class="inline-fields">
	                                                        <input type="text" name="profit_share_value" id="profit_share_value" value="{{ old('profit_share_value', $product->profit_share_value ?? '') }}" inputmode="decimal">
	                                                        <span id="profit_share_unit">원</span>
	                                                    </span>
	                                                </div>
	                                            </td>
	                                            <th>구매수량 제한</th>
	                                            <td>
	                                                <div class="field-line">
	                                                    <ul class="chk01">
	                                                        <li>
	                                                            <input type="radio" name="purchase_limit_enabled" id="purchase_limit_disabled" value="0" {{ $purchaseLimitEnabled !== '1' ? 'checked' : '' }}>
	                                                            <label for="purchase_limit_disabled">사용안함</label>
	                                                        </li>
	                                                        <li>
	                                                            <input type="radio" name="purchase_limit_enabled" id="purchase_limit_enabled" value="1" {{ $purchaseLimitEnabled === '1' ? 'checked' : '' }}>
	                                                            <label for="purchase_limit_enabled">사용</label>
	                                                        </li>
	                                                    </ul>
	                                                    <div id="purchase_limit_detail" class="inline-fields">
	                                                        <input type="text" name="purchase_min_qty" value="{{ old('purchase_min_qty', $product->purchase_min_qty ?? '') }}" inputmode="numeric" pattern="[0-9]*"> 개 이상
	                                                        <input type="text" name="purchase_max_qty" value="{{ old('purchase_max_qty', $product->purchase_max_qty ?? '') }}" inputmode="numeric" pattern="[0-9]*"> 개 이하
	                                                    </div>
	                                                </div>
	                                            </td>
	                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            @foreach(['sale_scope', 'tax_type', 'reward_points', 'price_constraint_enabled', 'price_constraint_type', 'price_min', 'price_max', 'price_fixed', 'profit_share_type', 'profit_share_value', 'purchase_limit_enabled', 'purchase_min_qty', 'purchase_max_qty'] as $field)
                                @error($field)
                                    <p class="fcol1 mt10">{{ $message }}</p>
                                @enderror
                            @endforeach
                        </div>

                        <div class="con_w">
                            <div class="ttl01">상품 옵션</div>
                            <div class="tb01 textL mb10">
                                <table>
                                    <colgroup>
                                        <col width="180px"><col width="">
                                    </colgroup>
                                    <tbody>
	                                        <tr>
	                                            <th>재고사용 여부</th>
	                                            <td>
	                                                <div class="field-line">
	                                                    <ul class="chk01">
	                                                        <li>
	                                                            <input type="radio" name="stock_usage" id="stock_usage_unused" value="unused" {{ $stockUsage === 'unused' ? 'checked' : '' }}>
	                                                            <label for="stock_usage_unused">사용안함</label>
	                                                        </li>
	                                                        <li>
	                                                            <input type="radio" name="stock_usage" id="stock_usage_used" value="used" {{ $stockUsage === 'used' ? 'checked' : '' }}>
	                                                            <label for="stock_usage_used">사용</label>
	                                                        </li>
	                                                    </ul>
	                                                </div>
	                                            </td>
	                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div class="tb01 textL">
                                <table>
                                    <colgroup>
                                        <col width="120px"><col width="120px"><col width=""><col width="150px"><col width="140px"><col width="110px"><col width="90px"><col width="70px">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th>옵션명</th>
                                            <th>옵션타입</th>
                                            <th>옵션값</th>
                                            <th>SKU</th>
                                            <th>옵션 판매가</th>
                                            <th class="stock-column">재고</th>
                                            <th>상태</th>
                                            <th>관리</th>
                                        </tr>
                                    </thead>
                                    <tbody id="product_option_body">
                                        @foreach($productOptions as $optionIndex => $option)
                                            <tr class="product-option-row">
                                                <td>
                                                    <input type="hidden" name="option_ids[]" value="{{ $option['id'] ?? '' }}">
                                                    <input type="text" name="option_names[]" value="{{ $option['option_name'] ?? '기본옵션' }}" class="wFull">
                                                </td>
                                                <td>
                                                    <select name="option_types[]" class="wFull">
                                                        <option value="text" {{ ($option['option_type'] ?? '') === 'text' ? 'selected' : '' }}>비고형</option>
                                                        <option value="general" {{ ($option['option_type'] ?? 'general') === 'general' ? 'selected' : '' }}>일반선택형</option>
                                                        <option value="price" {{ ($option['option_type'] ?? '') === 'price' ? 'selected' : '' }}>금액선택형</option>
                                                    </select>
                                                </td>
                                                <td><input type="text" name="option_values[]" value="{{ $option['option_value'] ?? '' }}" placeholder="예: S, 블랙, 추가상품" class="wFull"></td>
                                                <td><input type="text" name="option_skus[]" value="{{ $option['sku'] ?? '' }}" placeholder="자동생성 가능" class="wFull"></td>
                                                <td><input type="text" name="option_prices[]" value="{{ $option['price'] ?? old('product_price', $product->product_price ?? 0) }}" inputmode="numeric" pattern="[0-9]*" class="wFull"></td>
	                                                <td class="stock-column"><input type="text" name="option_stocks[]" value="{{ $option['stock'] ?? 0 }}" inputmode="numeric" pattern="[0-9]*" class="wFull"></td>
                                                <td>
                                                    <select name="option_statuses[]" class="wFull">
                                                        <option value="1" {{ ($option['status'] ?? 1) == 1 ? 'selected' : '' }}>사용</option>
                                                        <option value="0" {{ ($option['status'] ?? 1) == 0 ? 'selected' : '' }}>중지</option>
                                                    </select>
                                                </td>
                                                <td><button type="button" class="remove-option-row" style="height:32px; border:1px solid #ddd; background:#fff; padding:0 8px;">삭제</button></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="btm_btn mt10" style="display:flex; justify-content:space-between; align-items:center;">
                                <p style="font-size:12px; color:#777;">옵션값을 비워 둔 행은 저장하지 않습니다. 옵션 판매가는 실제 구매 선택 시 사용할 금액입니다.</p>
                                <button type="button" id="add_option_row" class="btn01 col2" style="height:38px; border:0; padding:0 16px;">옵션 추가</button>
                            </div>
                            @error('option_values.*')
                                <p class="fcol1 mt10">{{ $message }}</p>
                            @enderror
                            @error('stock_usage')
                                <p class="fcol1 mt10">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="con_w">
                            <div class="ttl01">상품 상세 설명</div>
                            <div class="tb01 textL">
                                <table>
                                    <colgroup>
                                        <col width="180px"><col width="">
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <th>상품상세설명</th>
                                            <td>
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="detail_display_type" id="detail_type_unused" value="unused" {{ $detailDisplayType === 'unused' ? 'checked' : '' }}>
                                                        <label for="detail_type_unused">미사용</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="detail_display_type" id="detail_type_image" value="image" {{ $detailDisplayType === 'image' ? 'checked' : '' }}>
                                                        <label for="detail_type_image">이미지로 등록</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="detail_display_type" id="detail_type_text" value="text" {{ $detailDisplayType === 'text' ? 'checked' : '' }}>
                                                        <label for="detail_type_text">텍스트로 등록</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr id="detail_image_row">
                                            <th>상세 이미지</th>
                                            <td>
                                                <div style="display:flex; gap:16px; align-items:flex-start; flex-wrap:wrap;">
                                                    <div>
                                                        <label for="detail_pc_image" class="image-upload-slot detail-image-slot {{ $detailPcImageUrl ? 'has-image' : '' }}" style="width:170px; height:120px; display:flex; align-items:center; justify-content:center; border:1px solid #d7dbe3; border-radius:6px; background:#f7f8fa; cursor:pointer; overflow:hidden; position:relative;">
                                                            <input type="file" id="detail_pc_image" name="detail_pc_image" accept="image/*" class="detail-image-input" data-preview="detail_pc_preview" style="position:absolute; width:1px; height:1px; opacity:0; pointer-events:none;">
                                                            <span id="detail_pc_preview" class="image-upload-preview" style="position:absolute; inset:0; background-size:cover; background-position:center; {{ $detailPcImageUrl ? 'background-image:url(' . $detailPcImageUrl . ');' : '' }}"></span>
                                                            <span class="image-upload-placeholder" style="{{ $detailPcImageUrl ? 'display:none;' : '' }} position:relative; z-index:1; font-size:13px; color:#667085;">PC 이미지</span>
                                                        </label>
                                                    </div>
                                                    <div>
                                                        <label for="detail_mobile_image" class="image-upload-slot detail-image-slot {{ $detailMobileImageUrl ? 'has-image' : '' }}" style="width:120px; height:160px; display:flex; align-items:center; justify-content:center; border:1px solid #d7dbe3; border-radius:6px; background:#f7f8fa; cursor:pointer; overflow:hidden; position:relative;">
                                                            <input type="file" id="detail_mobile_image" name="detail_mobile_image" accept="image/*" class="detail-image-input" data-preview="detail_mobile_preview" style="position:absolute; width:1px; height:1px; opacity:0; pointer-events:none;">
                                                            <span id="detail_mobile_preview" class="image-upload-preview" style="position:absolute; inset:0; background-size:cover; background-position:center; {{ $detailMobileImageUrl ? 'background-image:url(' . $detailMobileImageUrl . ');' : '' }}"></span>
                                                            <span class="image-upload-placeholder" style="{{ $detailMobileImageUrl ? 'display:none;' : '' }} position:relative; z-index:1; font-size:13px; color:#667085;">Mobile 이미지</span>
                                                        </label>
                                                    </div>
                                                </div>
                                                <p class="mt5" style="font-size:12px; color:#888;">이미지 박스를 클릭해 PC/Mobile 상세 이미지를 각각 등록합니다. 최대 10MB</p>
                                            </td>
                                        </tr>
                                        <tr id="detail_text_row">
                                            <th>상세 텍스트</th>
                                            <td>
                                                <textarea name="detail_text" class="wFull">{{ old('detail_text', $product->detail_text ?? '') }}</textarea>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            @foreach(['detail_display_type', 'detail_text', 'detail_pc_image', 'detail_mobile_image'] as $field)
                                @error($field)
                                    <p class="fcol1 mt10">{{ $message }}</p>
                                @enderror
                            @endforeach
                        </div>

                        <div class="con_w">
                            <div class="ttl01">발주 및 배송 설정</div>
                            <div class="tb01 textL">
                                <table>
                                    <colgroup>
                                        <col width="180px"><col width=""><col width="180px"><col width="">
                                    </colgroup>
                                    <tbody>
	                                        <tr>
	                                            <th>발주 담당자</th>
	                                            <td colspan="3">
	                                                <div class="field-line">
	                                                    <ul class="chk01">
	                                                        <li>
	                                                            <input type="radio" name="order_manager_enabled" id="order_manager_disabled" value="0" {{ $orderManagerEnabled !== '1' ? 'checked' : '' }}>
	                                                            <label for="order_manager_disabled">사용안함</label>
	                                                        </li>
	                                                        <li>
	                                                            <input type="radio" name="order_manager_enabled" id="order_manager_enabled" value="1" {{ $orderManagerEnabled === '1' ? 'checked' : '' }}>
	                                                            <label for="order_manager_enabled">사용</label>
	                                                        </li>
	                                                    </ul>
	                                                    <select name="distributor_id" id="order_manager_select" class="order-manager-select">
	                                                        <option value="">발주 담당자 선택</option>
	                                                        @foreach($orderManagers ?? [] as $manager)
	                                                            <option value="{{ $manager->id }}" {{ (string) $selectedDistributorId === (string) $manager->id ? 'selected' : '' }}>
	                                                                {{ $manager->name }}{{ !empty($manager->email) ? ' (' . $manager->email . ')' : '' }}
	                                                            </option>
	                                                        @endforeach
	                                                    </select>
	                                                </div>
	                                            </td>
	                                        </tr>
                                        <tr>
	                                            <th>배송비 선택</th>
	                                            <td>
	                                                <div class="field-line">
	                                                    <select name="shipping_policy_type" id="shipping_policy_type" class="compact-select">
	                                                        @foreach($shippingPolicyOptions ?? [] as $value => $label)
	                                                            <option value="{{ $value }}" {{ $shippingPolicyType === $value ? 'selected' : '' }}>{{ $label }}</option>
	                                                        @endforeach
	                                                    </select>
	                                                    <input type="text" name="shipping_policy_name" value="{{ old('shipping_policy_name', $product->shipping_policy_name ?? '') }}" class="w160" placeholder="정책명">
	                                                </div>
	                                            </td>
                                            <th>결제 방식</th>
                                            <td>
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="shipping_payment_type" id="shipping_payment_prepaid" value="prepaid" {{ $shippingPaymentType === 'prepaid' ? 'checked' : '' }}>
                                                        <label for="shipping_payment_prepaid">선불</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="shipping_payment_type" id="shipping_payment_collect" value="collect" {{ $shippingPaymentType === 'collect' ? 'checked' : '' }}>
                                                        <label for="shipping_payment_collect">착불</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
	                                        <tr>
	                                            <th>기본 배송비</th>
	                                            <td>
	                                                <span id="shipping_base_fee_wrap" class="inline-fields">
	                                                    <input type="text" name="shipping_base_fee" value="{{ old('shipping_base_fee', $product->shipping_base_fee ?? 0) }}" inputmode="decimal"> 원
	                                                </span>
	                                            </td>
	                                            <th>무료배송 기준</th>
	                                            <td>
	                                                <span id="shipping_free_threshold_wrap" class="inline-fields">
	                                                    <input type="text" name="shipping_free_threshold" value="{{ old('shipping_free_threshold', $product->shipping_free_threshold ?? 0) }}" inputmode="decimal"> 원 이상
	                                                </span>
	                                            </td>
	                                        </tr>
	                                        <tr>
	                                            <th>취소/환불 안내</th>
	                                            <td colspan="3">
	                                                <div class="field-line">
	                                                    <select name="cancel_refund_policy_id" class="cancel-refund-select">
	                                                        <option value="">기본 안내 사용</option>
	                                                        @foreach($cancelRefundPolicies ?? [] as $policy)
	                                                            <option value="{{ $policy->id }}" {{ (string) $selectedCancelRefundPolicyId === (string) $policy->id ? 'selected' : '' }}>
	                                                                {{ $policy->name }} / {{ $policy->type === 'exchange' ? '교환' : ($policy->type === 'return' ? '반품' : '취소') }}
	                                                            </option>
	                                                        @endforeach
	                                                    </select>
	                                                    <a href="{{ route('channel.refund.list') }}" class="policy-link">설정관리</a>
	                                                </div>
	                                            </td>
	                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            @foreach(['order_manager_enabled', 'distributor_id', 'shipping_policy_type', 'shipping_policy_name', 'shipping_payment_type', 'shipping_base_fee', 'shipping_free_threshold', 'cancel_refund_policy_id'] as $field)
                                @error($field)
                                    <p class="fcol1 mt10">{{ $message }}</p>
                                @enderror
                            @endforeach
                        </div>

                        <div class="con_w">
                            <div class="ttl01">상품 이미지</div>
                            <div class="tb01 textL">
                                <table>
                                    <colgroup>
                                        <col width="160px"><col width="">
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <th>대표 이미지</th>
                                            <td>
                                                <label for="product_image_input" class="image-upload-slot main-image-slot {{ $mainImageName ? 'has-image' : '' }}" style="width:160px; height:160px; display:flex; align-items:center; justify-content:center; border:1px solid #d7dbe3; border-radius:6px; background:#f7f8fa; cursor:pointer; overflow:hidden; position:relative;">
                                                    <input type="file" id="product_image_input" name="product_image" accept="image/*" style="position:absolute; width:1px; height:1px; opacity:0; pointer-events:none;">
                                                    <span id="product_main_preview" class="image-upload-preview" style="position:absolute; inset:0; background-size:cover; background-position:center; background-image:url({{ $imageUrl }});"></span>
                                                    <span class="image-upload-placeholder" style="{{ $mainImageName ? 'display:none;' : '' }} position:relative; z-index:1; width:34px; height:34px; line-height:31px; border-radius:50%; border:1px solid #b8bec9; color:#667085; font-size:28px; text-align:center; background:#fff;">+</span>
                                                </label>
                                                <p class="mt5" style="font-size: 12px; color: #888;">대표 이미지 박스를 클릭해 이미지를 등록합니다. jpg, png, webp, gif / 최대 5MB</p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>추가 이미지</th>
                                            <td>
                                                <div id="additional_image_grid" data-max-count="20" style="display:grid; grid-template-columns:repeat(5, 104px); gap:10px;">
                                                    @for($slot = 0; $slot < 20; $slot++)
                                                        @php
                                                            $slotImage = $additionalImages->get($slot);
                                                            $slotImageUrl = $slotImage ? asset('front/images/product_images/small/' . $slotImage->image) : '';
                                                        @endphp
                                                        <label for="additional_image_{{ $slot }}" class="image-upload-slot additional-image-slot {{ $slotImage ? 'has-image' : '' }}" data-slot="{{ $slot }}" data-existing="{{ $slotImage ? '1' : '0' }}" style="width:104px; height:104px; display:flex; align-items:center; justify-content:center; border:1px solid #d7dbe3; border-radius:6px; background:#f7f8fa; cursor:pointer; overflow:hidden; position:relative;">
                                                            <input type="hidden" name="slot_image_ids[{{ $slot }}]" value="{{ $slotImage->id ?? '' }}">
                                                            <input type="file" id="additional_image_{{ $slot }}" name="slot_images[{{ $slot }}]" accept="image/*" class="additional-image-input" data-slot="{{ $slot }}" style="position:absolute; width:1px; height:1px; opacity:0; pointer-events:none;">
                                                            <span class="additional-image-preview" style="position:absolute; inset:0; background-size:cover; background-position:center; {{ $slotImageUrl ? 'background-image:url(' . $slotImageUrl . ');' : '' }}"></span>
                                                            <span class="image-upload-placeholder" style="{{ $slotImage ? 'display:none;' : '' }} position:relative; z-index:1; width:28px; height:28px; line-height:25px; border-radius:50%; border:1px solid #b8bec9; color:#667085; font-size:24px; text-align:center; background:#fff;">+</span>
                                                            <span style="position:absolute; left:6px; top:6px; z-index:2; min-width:20px; height:20px; line-height:20px; border-radius:10px; background:rgba(17,24,39,.78); color:#fff; font-size:11px; text-align:center;">{{ $slot + 1 }}</span>
                                                        </label>
                                                    @endfor
                                                </div>
                                                <div class="mt10" style="font-size:12px; color:#666;">
                                                    등록/선택 <strong id="additional_image_total">{{ $existingAdditionalImageCount }}</strong>/20개
                                                    <span id="additional_image_new_count" style="color:#888;">(신규 0개)</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            @error('product_image')
                                <p class="fcol1 mt10">{{ $message }}</p>
                            @enderror
                            @error('images')
                                <p class="fcol1 mt10">{{ $message }}</p>
                            @enderror
                            @error('images.*')
                                <p class="fcol1 mt10">{{ $message }}</p>
                            @enderror
                            @error('slot_images')
                                <p class="fcol1 mt10">{{ $message }}</p>
                            @enderror
                            @error('slot_images.*')
                                <p class="fcol1 mt10">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="btm_btn mt20" style="display:flex; justify-content:center; align-items:center; gap:8px; font-size:14px;">
                            <a href="{{ route('channel.product_own') }}" class="col5" style="display:inline-flex; align-items:center; justify-content:center; width:130px; max-width:130px; height:44px; line-height:1; margin:0;">목록으로</a>
                            <button type="submit" class="btn01 col2" style="display:inline-flex; align-items:center; justify-content:center; width:130px; height:44px; border:none; border-radius:5px; background:#1d6d43; color:#fff; cursor:pointer; font-weight:700; font-size:14px; line-height:1;">{{ $isCreate ? '상품 등록' : '수정' }}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="https://cdn.ckeditor.com/4.22.1/full/ckeditor.js"></script>
<script>
	document.addEventListener('DOMContentLoaded', function () {
	    var productForm = document.querySelector('.product-base-form');
	    if (typeof CKEDITOR !== 'undefined' && document.getElementById('product_description_editor')) {
	        CKEDITOR.replace('product_description_editor', {
	            height: 320,
	            language: 'ko',
	            versionCheck: false,
	            toolbar: [
	                { name: 'document', items: ['Source', '-', 'Preview'] },
	                { name: 'clipboard', items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo'] },
	                '/',
	                { name: 'basicstyles', items: ['Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat'] },
	                { name: 'paragraph', items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote'] },
	                { name: 'links', items: ['Link', 'Unlink'] },
	                { name: 'insert', items: ['Table', 'HorizontalRule'] },
	                '/',
	                { name: 'styles', items: ['Format', 'Font', 'FontSize'] },
	                { name: 'colors', items: ['TextColor', 'BGColor'] },
	                { name: 'tools', items: ['Maximize'] }
	            ]
	        });
	    }

	    if (productForm) {
	        productForm.addEventListener('submit', function () {
	            if (typeof CKEDITOR === 'undefined') {
	                return;
	            }
	            Object.keys(CKEDITOR.instances).forEach(function (name) {
	                CKEDITOR.instances[name].updateElement();
	            });
	        });
	    }

	    var categoryTree = @json($categoryTree ?? []);
	    var selectedMiddle = String(@json((string) $selectedMiddleCategoryId));
	    var selectedMinor = String(@json((string) $selectedMinorCategoryId));
	    var productNoticeTemplates = @json($productNoticeTemplates ?? []);
	    var productNoticeItems = @json($productNoticeItems);
	    var majorSelect = document.getElementById('major_category_id');
	    var middleSelect = document.getElementById('middle_category_id');
	    var minorSelect = document.getElementById('minor_category_id');
	    var finalCategoryInput = document.getElementById('category_id');
    var productNoticeSection = document.getElementById('product_notice_section');
    var productNoticeTitle = document.getElementById('product_notice_title');
    var productNoticeType = document.getElementById('product_notice_type');
    var productNoticeBody = document.getElementById('product_notice_body');

    function findCategory(items, id) {
        id = String(id || '');
        return (items || []).find(function (item) {
            return String(item.id) === id;
        }) || null;
    }

	    function fillCategorySelect(select, placeholder, items, selectedValue) {
	        if (!select) {
	            return;
	        }

	        select.innerHTML = '';
	        var placeholderOption = document.createElement('option');
	        placeholderOption.value = '';
	        placeholderOption.textContent = placeholder;
	        select.appendChild(placeholderOption);

	        (items || []).forEach(function (item) {
	            var option = document.createElement('option');
	            option.value = item.id;
	            option.textContent = item.name;
	            if (String(selectedValue || '') === String(item.id)) {
	                option.selected = true;
	            }
	            select.appendChild(option);
	        });

	        select.disabled = !(items && items.length);
	    }

	    function updateFinalCategory() {
	        if (!finalCategoryInput || !majorSelect) {
	            return;
	        }

	        finalCategoryInput.value = minorSelect && minorSelect.value ? minorSelect.value : (middleSelect && middleSelect.value ? middleSelect.value : '');
    }

    function normalizeText(value) {
        return String(value || '').replace(/\s+/g, '');
    }

    function findNoticeTemplateForMajor() {
        var major = findCategory(categoryTree, majorSelect ? majorSelect.value : '');
        if (!major) {
            return null;
        }

        var majorName = normalizeText(major.name);
        var matchedKey = null;
        Object.keys(productNoticeTemplates || {}).some(function (key) {
            var template = productNoticeTemplates[key];
            return (template.keywords || []).some(function (keyword) {
                if (majorName.indexOf(normalizeText(keyword)) !== -1) {
                    matchedKey = key;
                    return true;
                }
                return false;
            });
        });

        if (!matchedKey) {
            return null;
        }

        return {
            key: matchedKey,
            template: productNoticeTemplates[matchedKey]
        };
    }

    function rememberProductNoticeItems() {
        if (!productNoticeBody) {
            return;
        }

        productNoticeBody.querySelectorAll('[name^="product_notice_items["]').forEach(function (input) {
            var match = input.name.match(/^product_notice_items\[([^\]]+)\]$/);
            if (match) {
                productNoticeItems[match[1]] = input.value;
            }
        });
    }

    function renderProductNotice() {
        if (!productNoticeSection || !productNoticeTitle || !productNoticeType || !productNoticeBody) {
            return;
        }

        rememberProductNoticeItems();
        var notice = findNoticeTemplateForMajor();
        productNoticeBody.innerHTML = '';

        if (!notice) {
            productNoticeType.value = '';
            productNoticeSection.style.display = 'none';
            return;
        }

        productNoticeType.value = notice.key;
        productNoticeTitle.textContent = '(' + notice.template.title + ')';
        productNoticeSection.style.display = '';

        (notice.template.fields || []).forEach(function (field) {
            var row = document.createElement('tr');
            var th = document.createElement('th');
            var td = document.createElement('td');
            var input = document.createElement('textarea');

            th.textContent = field.label;
            input.name = 'product_notice_items[' + field.key + ']';
            input.value = productNoticeItems[field.key] || '';
            input.style.width = '100%';
            input.style.minHeight = '42px';
            input.style.padding = '8px 10px';
            input.style.border = '1px solid #ddd';
            input.style.resize = 'vertical';

            td.appendChild(input);
            row.appendChild(th);
            row.appendChild(td);
            productNoticeBody.appendChild(row);
        });
    }

    function currentMajorCategory() {
        return findCategory(categoryTree, majorSelect ? majorSelect.value : '');
    }

	    function currentMiddleCategory() {
	        var major = currentMajorCategory();
	        var middleId = middleSelect ? middleSelect.value : '';

	        if (!major || !middleId) {
	            return null;
	        }

	        return findCategory(major.children || [], middleId);
	    }

	    function renderMiddleSelect(selectedValue) {
	        var major = currentMajorCategory();
	        fillCategorySelect(middleSelect, '중분류', major ? major.children : [], selectedValue);
	    }

	    function renderMinorSelect(selectedValue) {
	        var middle = currentMiddleCategory();
	        fillCategorySelect(minorSelect, '소분류', middle ? middle.children : [], selectedValue);
	    }

	    if (majorSelect) {
	        renderMiddleSelect(selectedMiddle);
	        renderMinorSelect(selectedMinor);
	        updateFinalCategory();
	        renderProductNotice();

	        majorSelect.addEventListener('change', function () {
	            renderMiddleSelect('');
	            renderMinorSelect('');
	            updateFinalCategory();
	            renderProductNotice();
	        });

	        if (middleSelect) {
	            middleSelect.addEventListener('change', function () {
	                renderMinorSelect('');
	                updateFinalCategory();
	            });
	        }

	        if (minorSelect) {
	            minorSelect.addEventListener('change', updateFinalCategory);
	        }
	    }

	    function selectedRadioValue(name) {
	        var checked = document.querySelector('[name="' + name + '"]:checked');
	        return checked ? checked.value : '';
	    }

	    function setGroupEnabled(element, enabled) {
	        if (!element) {
	            return;
	        }
	        element.style.display = enabled ? '' : 'none';
	        element.querySelectorAll('input, select, textarea').forEach(function (field) {
	            field.disabled = !enabled;
	        });
	    }

	    function setControlEnabled(element, enabled) {
	        if (!element) {
	            return;
	        }
	        element.classList.toggle('disabled-control', !enabled);
	        element.querySelectorAll('input, select, textarea').forEach(function (field) {
	            field.disabled = !enabled;
	        });
	    }

	    function updatePriceConstraint() {
	        var enabled = selectedRadioValue('price_constraint_enabled') === '1';
	        var detail = document.getElementById('price_constraint_detail');
	        setGroupEnabled(detail, enabled);
	        if (!enabled) {
	            updateProfitShare();
	            return;
	        }

	        var type = selectedRadioValue('price_constraint_type') || 'range';
	        setGroupEnabled(document.querySelector('.price-range-fields'), type === 'range');
	        setGroupEnabled(document.querySelector('.price-fixed-field'), type === 'fixed');
	        updateProfitShare();
	    }

	    function updateProfitShare() {
	        var priceConstraintEnabled = selectedRadioValue('price_constraint_enabled') === '1';
	        var typeSelect = document.getElementById('profit_share_type');
	        var valueWrap = document.getElementById('profit_share_value_wrap');
	        var valueInput = document.getElementById('profit_share_value');
	        var unit = document.getElementById('profit_share_unit');
	        if (!typeSelect || !valueWrap || !valueInput || !unit) {
	            return;
	        }

	        typeSelect.disabled = !priceConstraintEnabled;
	        var active = priceConstraintEnabled && typeSelect.value !== 'none';
	        valueWrap.style.display = active ? 'inline' : 'none';
	        valueInput.disabled = !active;
	        unit.textContent = typeSelect.value === 'percent' ? '%' : '원';
	    }

	    function updatePurchaseLimit() {
	        setGroupEnabled(document.getElementById('purchase_limit_detail'), selectedRadioValue('purchase_limit_enabled') === '1');
	    }

	    function updateStockUsage() {
	        var enabled = selectedRadioValue('stock_usage') === 'used';
	        document.querySelectorAll('.stock-column').forEach(function (element) {
	            element.classList.toggle('disabled-control', !enabled);
	            element.querySelectorAll('input, select, textarea').forEach(function (field) {
	                field.disabled = !enabled;
	            });
	        });
	    }

	    function updateDetailDisplay() {
	        var type = selectedRadioValue('detail_display_type') || 'unused';
	        setGroupEnabled(document.getElementById('detail_image_row'), type === 'image');
	        setGroupEnabled(document.getElementById('detail_text_row'), type === 'text');
	    }

	    function updateOrderManager() {
	        var select = document.getElementById('order_manager_select');
	        if (!select) {
	            return;
	        }
	        var enabled = selectedRadioValue('order_manager_enabled') === '1';
	        select.style.display = enabled ? '' : 'none';
	        select.disabled = !enabled;
	    }

	    function updateShippingPolicy() {
	        var type = (document.getElementById('shipping_policy_type') || {}).value;
	        setControlEnabled(document.getElementById('shipping_base_fee_wrap'), type !== 'free');
	        setControlEnabled(document.getElementById('shipping_free_threshold_wrap'), type === 'free_conditional');
	    }

	    ['price_constraint_enabled', 'price_constraint_type', 'purchase_limit_enabled', 'stock_usage', 'detail_display_type', 'order_manager_enabled'].forEach(function (name) {
	        document.querySelectorAll('[name="' + name + '"]').forEach(function (input) {
	            input.addEventListener('change', function () {
	                updatePriceConstraint();
	                updatePurchaseLimit();
	                updateStockUsage();
	                updateDetailDisplay();
	                updateOrderManager();
	            });
	        });
	    });

	    var profitShareType = document.getElementById('profit_share_type');
	    if (profitShareType) {
	        profitShareType.addEventListener('change', updateProfitShare);
	    }

	    var shippingPolicySelect = document.getElementById('shipping_policy_type');
	    if (shippingPolicySelect) {
	        shippingPolicySelect.addEventListener('change', updateShippingPolicy);
	    }

	    updatePriceConstraint();
	    updatePurchaseLimit();
	    updateStockUsage();
	    updateDetailDisplay();
	    updateOrderManager();
	    updateShippingPolicy();
	    updateProfitShare();

	    var optionBody = document.getElementById('product_option_body');
	    var addOptionButton = document.getElementById('add_option_row');

	    function optionRowHtml() {
	        var basePriceInput = document.querySelector('[name="product_price"]');
	        var basePrice = basePriceInput && basePriceInput.value ? basePriceInput.value : '0';
	        return '' +
	            '<tr class="product-option-row">' +
	            '<td><input type="hidden" name="option_ids[]" value=""><input type="text" name="option_names[]" value="기본옵션" class="wFull"></td>' +
	            '<td><select name="option_types[]" class="wFull"><option value="text">비고형</option><option value="general" selected>일반선택형</option><option value="price">금액선택형</option></select></td>' +
	            '<td><input type="text" name="option_values[]" value="" placeholder="예: S, 블랙, 추가상품" class="wFull"></td>' +
	            '<td><input type="text" name="option_skus[]" value="" placeholder="자동생성 가능" class="wFull"></td>' +
	            '<td><input type="text" name="option_prices[]" value="' + basePrice + '" inputmode="numeric" pattern="[0-9]*" class="wFull"></td>' +
	            '<td class="stock-column"><input type="text" name="option_stocks[]" value="0" inputmode="numeric" pattern="[0-9]*" class="wFull"></td>' +
	            '<td><select name="option_statuses[]" class="wFull"><option value="1" selected>사용</option><option value="0">중지</option></select></td>' +
	            '<td><button type="button" class="remove-option-row" style="height:32px; border:1px solid #ddd; background:#fff; padding:0 8px;">삭제</button></td>' +
	            '</tr>';
	    }

	    if (addOptionButton && optionBody) {
	        addOptionButton.addEventListener('click', function () {
	            optionBody.insertAdjacentHTML('beforeend', optionRowHtml());
	            updateStockUsage();
	        });

	        optionBody.addEventListener('click', function (event) {
	            if (!event.target.classList.contains('remove-option-row')) {
	                return;
	            }
	            var rows = optionBody.querySelectorAll('.product-option-row');
	            var row = event.target.closest('.product-option-row');
	            if (rows.length <= 1) {
	                row.querySelectorAll('input').forEach(function (input) {
	                    if (input.type !== 'hidden') {
	                        input.value = input.name === 'option_names[]' ? '기본옵션' : '';
	                    }
	                });
	                row.querySelector('[name="option_statuses[]"]').value = '1';
	                row.querySelector('[name="option_types[]"]').value = 'general';
	                return;
	            }
	            row.remove();
	        });
	    }

	    var mainInput = document.getElementById('product_image_input');
    var mainPreview = document.getElementById('product_main_preview');
    var mainSlot = document.querySelector('.main-image-slot');
    var mainPlaceholder = mainSlot ? mainSlot.querySelector('.image-upload-placeholder') : null;
    var additionalGrid = document.getElementById('additional_image_grid');
    var totalCount = document.getElementById('additional_image_total');
    var newCount = document.getElementById('additional_image_new_count');

	    if (mainInput && mainPreview) {
	        mainInput.addEventListener('change', function () {
	            var file = mainInput.files && mainInput.files[0] ? mainInput.files[0] : null;
            if (!file) {
                return;
            }

            var reader = new FileReader();
            reader.onload = function (event) {
                mainPreview.style.backgroundImage = 'url("' + event.target.result + '")';
                if (mainSlot) {
                    mainSlot.classList.add('has-image');
                }
                if (mainPlaceholder) {
                    mainPlaceholder.style.display = 'none';
                }
            };
	            reader.readAsDataURL(file);
	        });
	    }

	    document.querySelectorAll('.detail-image-input').forEach(function (input) {
	        input.addEventListener('change', function () {
	            var file = input.files && input.files[0] ? input.files[0] : null;
	            var preview = document.getElementById(input.getAttribute('data-preview'));
	            var slot = input.closest('.detail-image-slot');
	            var placeholder = slot ? slot.querySelector('.image-upload-placeholder') : null;
	            if (!file || !preview) {
	                return;
	            }

	            var reader = new FileReader();
	            reader.onload = function (event) {
	                preview.style.backgroundImage = 'url("' + event.target.result + '")';
	                if (slot) {
	                    slot.classList.add('has-image');
	                }
	                if (placeholder) {
	                    placeholder.style.display = 'none';
	                }
	            };
	            reader.readAsDataURL(file);
	        });
	    });

	    if (!additionalGrid || !totalCount || !newCount) {
	        return;
    }

    function updateCounts() {
        var occupied = additionalGrid.querySelectorAll('.additional-image-slot.has-image').length;
        var selectedNew = 0;

        additionalGrid.querySelectorAll('.additional-image-input').forEach(function (input) {
            var slot = input.closest('.additional-image-slot');
            if (input.files && input.files.length > 0 && slot && slot.getAttribute('data-existing') !== '1') {
                selectedNew += 1;
            }
        });

        totalCount.textContent = occupied;
        newCount.textContent = '(신규 ' + selectedNew + '개)';
    }

    additionalGrid.querySelectorAll('.additional-image-input').forEach(function (input) {
        input.addEventListener('change', function () {
            var file = input.files && input.files[0] ? input.files[0] : null;
            var slot = input.closest('.additional-image-slot');
            var preview = slot ? slot.querySelector('.additional-image-preview') : null;
            var placeholder = slot ? slot.querySelector('.image-upload-placeholder') : null;

            if (!file || !slot || !preview) {
                updateCounts();
                return;
            }

            var reader = new FileReader();
            reader.onload = function (event) {
                preview.style.backgroundImage = 'url("' + event.target.result + '")';
                slot.classList.add('has-image');
                if (placeholder) {
                    placeholder.style.display = 'none';
                }
                updateCounts();
            };
            reader.readAsDataURL(file);
        });
    });

    updateCounts();
});
</script>
@endpush
