@php($rules=$charge->calculation_rules ?? [])
<h3>{{ $charge->name }}</h3><dl class="delivery-facts">
<div><dt>배송구분</dt><dd>사용자</dd></div><div><dt>상태</dt><dd>{{ $charge->status ? '사용' : '중지' }}</dd></div>
<div><dt>지정택배사</dt><dd>{{ $charge->courier ?: '자체배송' }}</dd></div><div><dt>배송비 유형</dt><dd>{{ $shippingTypes[$charge->shipping_type] ?? $charge->shipping_type }}
@if($charge->shipping_type==='free')<p>기본 배송비 0원</p>@endif
@if(in_array($charge->shipping_type,['conditional','variable']))<p>기본 배송비 {{ number_format($charge->base_fee) }}원</p>@endif
@if($charge->shipping_type==='conditional')@if($charge->free_order_amount)<p>{{ number_format($charge->free_order_amount) }}원 이상 구매 시 무료</p>@endif @if($charge->free_order_quantity)<p>{{ number_format($charge->free_order_quantity) }}개 이상 구매 시 무료</p>@endif<p>조건 중 하나 이상 충족 시 기본 배송비 무료</p>@endif
@if($charge->shipping_type==='fixed')<p>고정 배송비 {{ number_format($charge->fixed_fee ?? $charge->base_fee) }}원</p>@endif
@if($charge->shipping_type==='variable')<p>{{ number_format($rules['repeat_every'] ?? 0) }}{{ ($rules['basis'] ?? 'quantity')==='amount' ? '원' : '개' }}마다 {{ number_format($rules['repeat_fee'] ?? 0) }}원 반복 부과</p><p>{{ !empty($rules['start_at_one']) ? '반복 부과 기준을 1부터 시작' : '반복 기준값부터 부과' }}</p><p>지역 배송비 {{ !empty($rules['repeat_region']) ? '동일 조건으로 반복 부과' : '기본 1회 부과' }}</p>@endif
@if($charge->shipping_type==='tiered')@foreach($rules['tiers'] ?? [] as $tier)<p>{{ number_format($tier['min']) }}{{ ($rules['basis'] ?? '')==='amount' ? '원' : '개' }} 이상 @if($tier['max']!==null){{ number_format($tier['max']) }} 미만@endif : {{ number_format($tier['fee']) }}원</p>@endforeach<p>해당 구간 한 개의 배송비만 적용</p>@endif
</dd></div><div><dt>배송비 결제</dt><dd>{{ $paymentTypes[$charge->payment_type] ?? $charge->payment_type }}</dd></div>
<div><dt>지역별 배송</dt><dd>제주 지역: {{ !empty($rules['block_jeju']) ? '배송안함' : '배송함' }}<br>도서산간 지역: {{ !empty($rules['block_remote']) ? '배송안함' : '배송함' }}</dd></div>
<div><dt>지역별 배송비</dt><dd>@if(($rules['region_mode'] ?? 'none')==='none')사용안함 @else 배송권역 방식 · {{ $rules['region_mode']==='two' ? '2권역' : '3권역' }}<p>{{ $rules['region_mode']==='two' ? '제주/도서산간' : '제주지역' }} 추가 배송비: {{ number_format($rules['jeju_fee'] ?? 0) }}원</p>@if($rules['region_mode']==='three')<p>도서산간 추가 배송비: {{ number_format($rules['remote_fee'] ?? 0) }}원</p>@endif<p>그 외 지역 추가 배송비: {{ number_format($rules['mainland_fee'] ?? 0) }}원</p>@endif</dd></div>
<div><dt>연결 상품수</dt><dd>{{ number_format($charge->linked_product_count) }}</dd></div><div><dt>메모</dt><dd class="delivery-memo-text">{{ $charge->memo ?: '-' }}</dd></div></dl>
<details><summary>배송 지역 우편번호 기준</summary><p>제주: {{ implode(', ',$rules['jeju_postcodes'] ?? []) ?: '미등록' }}</p><p>도서산간: {{ implode(', ',$rules['remote_postcodes'] ?? []) ?: '미등록' }}</p></details>
