<form method="GET" action="{{ url()->current() }}" class="shop-filters">
    @isset($counts)
        <div class="statuses">
            @foreach(['all' => '전체', 'confirm_pending' => '확정대기', 'shipping' => '배송중', 'claims' => '취소·교환·반품'] as $key => $label)
                <label class="shop-check"><input type="radio" name="status" value="{{ $key }}" @checked($status === $key)>{{ $label }} {{ $counts[$key] }}건</label>
            @endforeach
        </div>
    @endisset
    <div class="shop-periods" role="group" aria-label="주문 기간">
        @foreach([1=>'최근 1개월', 3=>'최근 3개월', 6=>'최근 6개월', 12=>'최근 1년'] as $months=>$label)<button class="shop-btn small" type="button" data-period="{{ $months }}">{{ $label }}</button>@endforeach
    </div>
    <label>주문기간 시작<input type="date" name="from" value="{{ request('from') }}" class="shop-control"></label>
    <label>주문기간 종료<input type="date" name="to" value="{{ request('to') }}" class="shop-control"></label>
    <label>주문번호 / 상품명<input name="search" value="{{ request('search') }}" maxlength="100" class="shop-control"></label>
    <button class="shop-btn primary" type="submit">조회</button><a class="shop-btn" href="{{ url()->current() }}">초기화</a>
</form>
