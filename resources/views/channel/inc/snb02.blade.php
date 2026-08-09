<li class="dep1 icon1_2">
    <a href="{{ url()->current() }}">상품관리</a>
    <ul class="dep2_wrap">
        <li><a href="{{ route('channel.product_own') }}">자사상품</a></li>
        <li><a href="{{ route('channel.product_public') }}">공개상품</a></li>
        <!-- Route for product_partial might need to be added if implemented -->
        <li><a href="{{ url()->current() }}">부분공개상품</a></li>
        <!--<li><a href="{{ url()->current() }}">판매요청목록</a></li>
        <li><a href="{{ url()->current() }}">상품게시채널목록</a></li>-->
        <li><a href="{{ route('channel.product_request') }}">판매요청관리</a></li>
    </ul>
</li>
