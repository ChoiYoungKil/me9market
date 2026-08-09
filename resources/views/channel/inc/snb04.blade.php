<li class="dep1 icon1_4">
    <a href="javascript:void(0);">주문관리</a>
    <ul class="dep2_wrap">
        <li>
            <a href="{{ route('channel.order.list') }}">주문목록</a>
            <ul class="dep3_wrap">
                <li><a href="javascript:void(0);">- 결제대기<span>(5)</span></a></li>
                <li><a href="javascript:void(0);">- 결제완료<span>(5)</span></a></li>
                <li><a href="javascript:void(0);">- 배송준비<span>(1)</span></a></li>
                <li><a href="javascript:void(0);">- 발주대기<span>(2)</span></a></li>
                <li><a href="javascript:void(0);">- 배송중<span>(0)</span></a></li>
                <li><a href="javascript:void(0);">- 구매확정<span>(5)</span></a></li>
                <li><a href="{{ route('channel.order.cancel_list') }}">- 취소요청<span>(2)</span></a></li>
                <li><a href="javascript:void(0);">- 취소완료<span>(0)</span></a></li>
                <li><a href="{{ route('channel.order.return_list') }}">- 반품신청<span>(3)</span></a></li>
                <li><a href="javascript:void(0);">- 반품완료<span>(8)</span></a></li>
                <li><a href="{{ route('channel.order.exchange_list') }}">- 교환신청<span>(0)</span></a></li>
            </ul>
        </li>
    </ul>
</li>