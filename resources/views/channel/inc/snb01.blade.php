<li class="dep1 icon1_1">
    <a href="{{ url()->current() }}">Shop채널관리</a>
    <ul class="dep2_wrap">
        <li><a href="{{ route('channel.shop_list') }}">Shop채널목록</a></li>
        <li><a href="{{ route('channel.shop_register') }}">Shop채널등록</a></li>
        <!--<li><a href="{{ url()->current() }}">공지사항</a></li>-->
        <li><a href="{{ route('channel.shop_community') }}">커뮤니티</a></li>
    </ul>
</li>