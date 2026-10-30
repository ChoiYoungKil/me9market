<section><h3>발주담당상태</h3><dl class="manager-facts"><div><dt>상태</dt><dd>{{ $manager->access_status }}</dd></div><div><dt>접속기간</dt><dd>{{ $manager->access_started_at?->format('Y.m.d H:i') ?: '시작 제한 없음' }} ~ {{ $manager->access_ended_at?->format('Y.m.d H:i') ?: '종료 제한 없음' }}</dd></div></dl></section>
<section><h3>발주담당정보</h3><dl class="manager-facts"><div><dt>이메일</dt><dd>{{ $manager->email }}</dd></div><div><dt>담당자 명</dt><dd>{{ $manager->name }}</dd></div><div><dt>연락처</dt><dd>{{ $manager->phone ?: '-' }}</dd></div></dl></section>
<section><h3>출고지 주소</h3><dl class="manager-facts"><div><dt>우편번호</dt><dd>{{ $manager->dispatch_postcode ?: '미등록' }}</dd></div><div><dt>출고지</dt><dd>{{ trim(($manager->dispatch_address ?? '').' '.($manager->dispatch_address_detail ?? '')) ?: '미등록' }}</dd></div></dl></section>
<section><h3>메모</h3><p class="manager-memo">{{ $manager->memo ?: '등록된 메모가 없습니다.' }}</p></section>
<section><h3>발주상품정보 ({{ number_format($products->total()) }})</h3><div class="manager-products">
@forelse($products as $product)
<div class="manager-product">@if($product->product_image && is_file(public_path('front/images/product_images/medium/'.basename($product->product_image))))<img src="{{ asset('front/images/product_images/medium/'.rawurlencode(basename($product->product_image))) }}" alt="{{ $product->product_name }}">@else<div class="manager-image-empty">이미지 없음</div>@endif<div><p>{{ $product->category_path }}</p><strong>{{ $product->product_name }}</strong><small>{{ $product->product_code }}</small></div></div>
@empty<p class="manager-help">배정된 상품이 없습니다. 상품 등록·수정 화면에서 이 발주담당자를 선택하면 표시됩니다.</p>
@endforelse
</div><div data-manager-detail-pagination>{{ $products->links() }}</div></section>
