@extends('layouts.admin')
@section('content')
<div id="contents"><div class="row"><div class="box">
    <div class="page_info"><h1 class="ttl">배송비 설정</h1><ul class="dep"><li>홈</li><li>배송비 설정</li></ul></div>
    @include('partials.delivery_messages')
    <p class="portal-description">판매자별 Shop 상품에 적용되는 배송비 정책을 관리합니다.</p>
    <form method="GET" class="portal-filter">
        <label>판매자 <select name="vendor_id"><option value="">전체 판매자</option>@foreach($vendors as $vendor)<option value="{{ $vendor->id }}" @selected((string) request('vendor_id') === (string) $vendor->id)>{{ $vendor->name }}</option>@endforeach</select></label>
        <label>정책명 <input name="search" value="{{ request('search') }}" maxlength="100"></label><button class="btn02" type="submit">검색</button>
    </form>
    <div class="list_top1"><div class="count">총 <strong>{{ $charges->total() }}</strong> 건</div><a class="btn02" href="#new-delivery-policy">배송비 등록</a></div>
    <div class="tb01 ovS"><table><thead><tr><th>판매자</th><th>정책명</th><th>유형</th><th>결제</th><th>기본 배송비</th><th>무료 조건</th><th>상태</th><th>관리</th></tr></thead><tbody>
    @forelse($charges as $charge)
        <tr><td>{{ $charge->vendor?->name }}</td><td>{{ $charge->name }}</td><td>{{ \App\Services\DeliveryPolicyRules::shippingTypes()[$charge->shipping_type] ?? $charge->shipping_type }}</td><td>{{ \App\Services\DeliveryPolicyRules::paymentTypes()[$charge->payment_type] ?? $charge->payment_type }}</td><td>{{ number_format($charge->base_fee) }}원</td><td>{{ $charge->free_order_amount ? number_format($charge->free_order_amount).'원 이상' : '' }} {{ $charge->free_order_quantity ? number_format($charge->free_order_quantity).'개 이상' : '' }}</td><td>{{ $charge->status ? '사용' : '중지' }}</td><td><a class="btn01" href="{{ route('admin.shipping.edit', $charge->id) }}">수정</a></td></tr>
    @empty<tr><td colspan="8">등록된 배송비 정책이 없습니다.</td></tr>@endforelse
    </tbody></table></div>
    <div class="portal-pagination">{{ $charges->links() }}</div>
    <section id="new-delivery-policy" class="delivery-editor"><h2>배송비 등록</h2>
        <form method="POST" action="{{ route('admin.shipping.store') }}">@csrf
            <label class="delivery-vendor">판매자 <select name="vendor_id" required><option value="">판매자를 선택하세요</option>@foreach($vendors as $vendor)<option value="{{ $vendor->id }}" @selected((string) old('vendor_id') === (string) $vendor->id)>{{ $vendor->name }}</option>@endforeach</select></label>
            @include('channel.sub00.inc.delivery_charge_form', ['charge' => null, 'shippingTypes' => \App\Services\DeliveryPolicyRules::shippingTypes(), 'paymentTypes' => \App\Services\DeliveryPolicyRules::paymentTypes()])
            <div class="btm_btn"><button class="btn02" type="submit">등록</button></div>
        </form>
    </section>
</div></div></div>
@endsection
