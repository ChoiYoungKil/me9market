<form method="GET" action="{{ url()->current() }}" class="shop-filters shop-order-search" aria-label="주문 검색">
    @isset($counts)
        <select name="status" class="shop-control" aria-label="주문상태">
            @foreach(['all'=>'전체 주문상태','paid'=>'결제완료','ready_to_ship'=>'배송대기','shipping'=>'배송중','delivered'=>'배송완료','confirmed'=>'구매확정','claims'=>'취소·반품·교환'] as $key=>$label)<option value="{{ $key }}" @selected($status===$key)>{{ $label }}@if($key==='delivered') (확정대기 {{ $counts['confirm_pending'] }}건)@endif</option>@endforeach
            @if($status==='confirm_pending')<option value="confirm_pending" selected>확정대기</option>@endif
        </select>
    @else
        <select name="claim_status" class="shop-control" aria-label="신청 처리상태">
            @foreach(['all'=>'전체 처리상태','requested'=>'신청접수','approved'=>'승인','received'=>'회수완료','held'=>'보류','completed'=>'처리완료','withdrawn'=>'신청철회','rejected'=>'거절'] as $key=>$label)<option value="{{ $key }}" @selected(request('claim_status','all')===$key)>{{ $label }}</option>@endforeach
        </select>
    @endisset
    <div class="shop-periods" role="group" aria-label="주문 기간">
        @foreach([1=>'1개월',3=>'3개월',6=>'6개월',12=>'1년'] as $months=>$label)<button class="shop-btn small" type="button" data-period="{{ $months }}" aria-label="최근 {{ $label }}" aria-pressed="false">{{ $label }}</button>@endforeach
    </div>
    <div class="shop-order-date-range"><input type="date" name="from" value="{{ request('from') }}" aria-label="주문기간 시작" class="shop-control"><span aria-hidden="true">~</span><input type="date" name="to" value="{{ request('to') }}" aria-label="주문기간 종료" class="shop-control"></div>
    <button class="shop-btn primary" type="submit">조회</button>
    <details class="shop-order-extra-search" @if(request()->filled('search')) open @endif><summary>주문번호·상품명 검색</summary><div><input name="search" value="{{ request('search') }}" maxlength="100" class="shop-control" aria-label="주문번호 / 상품명"><a class="shop-btn" href="{{ url()->current() }}">초기화</a></div></details>
</form>
