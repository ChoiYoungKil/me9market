@extends('layouts.mypage')
@section('page_type', 'sub')
@section('dep1_id', '02')
@section('content')
<div id="contents"><div class="box_w"><section class="box member-visits">
    <div class="page_info"><div class="ttl">방문한 채널</div><ul class="dep"><li>HOME</li><li>방문한 채널</li></ul></div>
    <div class="member-list-toolbar">
        <p>총 <strong>{{ number_format($visitedChannels->total()) }}</strong>건</p>
        <form method="get" action="{{ route('mypage.visited_channels') }}" class="member-list-search">
            <label for="visited-channel-search">채널명</label><input id="visited-channel-search" type="search" name="search" value="{{ request('search') }}" maxlength="100" placeholder="채널명 또는 채널코드"><button type="submit">검색</button>
        </form>
        <form method="post" action="{{ route('mypage.visited_channels.delete_all') }}" onsubmit="return confirm('모든 방문 기록을 삭제하시겠습니까?');">@csrf<button type="submit" class="member-list-outline">전체 삭제</button></form>
    </div>
    <div class="member-table-scroll"><div class="tb01"><table><thead><tr><th>방문 일자</th><th>QR 코드</th><th>Shop 채널명</th><th>Shop 채널 주소</th><th>관리</th></tr></thead><tbody>
    @forelse($visitedChannels as $visit)
        @php
            $shop = $visit->shopChannel;
            $visitUrl = $shop ? route('shop.enter', $shop->channel_code) : null;
            $channelName = $shop?->channel_name ?: '이전 방문 · '.($visit->vendor?->vendorbusinessdetails?->shop_name ?: '채널 정보 없음');
        @endphp
        <tr><td>{{ $visit->updated_at->format('Y-m-d') }}</td><td>
        @if($visitUrl)
            @php
                $qrImage = 'data:image/png;base64,'.DNS2D::getBarcodePNG($visitUrl, 'QRCODE', 4, 4);
                $largeQrImage = 'data:image/png;base64,'.DNS2D::getBarcodePNG($visitUrl, 'QRCODE', 10, 10);
            @endphp
            <button type="button" class="member-qr-open" aria-label="{{ $channelName }} QR 확대" data-name="{{ $channelName }}" data-url="{{ $visitUrl }}" data-image="{{ $largeQrImage }}"><img src="{{ $qrImage }}" alt="{{ $channelName }} QR 코드" width="72" height="72"></button>
        @else — @endif
        </td><td class="member-channel-name">@if($shop)<small>[{{ $shop->channel_code }}]</small>@endif<strong>{{ $channelName }}</strong>@if($shop?->og_description)<p>{{ $shop->og_description }}</p>@endif</td>
        <td>@if($visitUrl)<a href="{{ $visitUrl }}" class="member-channel-url">{{ $visitUrl }}</a>@else<span>채널 주소 확인 불가</span>@endif</td>
        <td><div class="member-row-actions">@if($visitUrl)<a href="{{ $visitUrl }}" class="member-list-primary">방문</a>@endif<form method="post" action="{{ route('mypage.visited_channels.delete', $visit->id) }}" onsubmit="return confirm('이 방문 기록을 삭제하시겠습니까?');">@csrf<button type="submit" class="member-list-outline">삭제</button></form></div></td></tr>
    @empty<tr><td colspan="5">{{ request('search') ? '검색된 방문 채널이 없습니다.' : '방문한 채널 내역이 없습니다.' }}</td></tr>@endforelse
    </tbody></table></div></div>
    @if($visitedChannels->lastPage()>1)<nav class="page_bx1" aria-label="방문 채널 목록 페이지">@foreach($visitedChannels->getUrlRange(1,$visitedChannels->lastPage()) as $page=>$url)<a class="num {{ $page===$visitedChannels->currentPage()?'on':'' }}" href="{{ $url }}">{{ $page }}</a>@endforeach</nav>@endif
</section></div></div>
<div class="popup_bx" id="visited-qr-modal" role="dialog" aria-modal="true" aria-labelledby="visited-qr-title"><div class="pop_w"><div class="pop_inner"><div class="pop_con w560 member-qr-modal"><button type="button" class="close_btn close1" aria-label="QR 닫기">닫기</button><h2 id="visited-qr-title"></h2><p id="visited-qr-url"></p><img id="visited-qr-image" alt="확대 QR 코드" width="240" height="240"><div class="btm_btn"><button type="button" class="btn_submit close_btn">닫기</button></div></div></div></div></div>
@endsection
@push('scripts')
<script>
document.querySelectorAll('.member-qr-open').forEach(button=>button.addEventListener('click',()=>{
    document.getElementById('visited-qr-title').textContent=button.dataset.name;
    document.getElementById('visited-qr-url').textContent=button.dataset.url;
    document.getElementById('visited-qr-image').src=button.dataset.image;
    document.getElementById('visited-qr-modal').style.display='block';
    document.querySelector('#visited-qr-modal .close_btn').focus();
}));
document.querySelectorAll('#visited-qr-modal .close_btn').forEach(button=>button.addEventListener('click',()=>{document.getElementById('visited-qr-modal').style.display='none';}));
document.addEventListener('keydown',event=>{if(event.key==='Escape') document.getElementById('visited-qr-modal').style.display='none';});
</script>
@endpush
