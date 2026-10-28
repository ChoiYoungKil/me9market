@extends('layouts.mypage')
@section('page_type', 'sub')
@section('dep1_id', '01')
@section('dep2_id', '3')
@section('dep3_id', '1')
@section('content')
<div id="contents"><div class="box_w"><section class="box member-points">
    <div class="page_info"><div class="ttl">포인트 현황</div><ul class="dep"><li>HOME</li><li>포인트관리</li><li>포인트 현황</li></ul></div>
    <h2 class="ttl01">포인트</h2><div class="member-point-summary"><div><span>채널 포인트</span><strong>{{ number_format($channelPoints) }} P</strong></div><div><span>Me9 포인트</span><strong>{{ number_format($me9Points) }} P</strong></div></div>
    <h2 class="ttl01">채널 포인트</h2><div class="member-table-scroll"><div class="tb01"><table><thead><tr><th>No</th><th>판매자 정보</th><th>상태</th><th>사용 가능 포인트</th><th>사용 가능 Shop 채널</th><th>포인트 전환</th></tr></thead><tbody>
    @forelse($pointsList as $point)<tr><td>{{ $point['no'] }}</td><td class="member-channel-name"><strong>{{ $point['seller_name'] }}</strong><small>{{ $point['channel_name'] }} ({{ $point['channel_code'] }})</small></td><td>{{ $point['status'] }}</td><td>{{ number_format($point['available_points']) }} P</td><td>
    @if($shopChannelsByVendor->get($point['vendor_id'])?->isNotEmpty())<button type="button" class="member-list-outline point-shop-open" data-vendor="{{ $point['vendor_id'] }}" aria-label="{{ $point['channel_name'] }} Shop 채널 보기">보기</button>@else Shop 채널 없음 @endif
    </td><td>@if($point['can_convert'])<button type="button" class="member-list-primary point-convert-open" data-shop="{{ $point['shop_channel_id'] }}" data-points="{{ number_format($point['available_points']) }}">전환</button>@else<span class="member-muted">전환 불가</span>@endif</td></tr>
    @empty<tr><td colspan="6">채널 포인트 내역이 없습니다.</td></tr>@endforelse
    </tbody></table></div></div>
</section></div></div>
<div class="popup_bx" id="point-convert-modal" role="dialog" aria-modal="true" aria-labelledby="point-convert-title"><div class="pop_w"><div class="pop_inner"><div class="pop_con w560"><button class="close_btn close1" type="button" aria-label="전환 창 닫기">닫기</button><h2 id="point-convert-title" class="ttl01">포인트 전환</h2><p class="member-convert-message"><strong id="point-convert-amount"></strong> P를 Me9 포인트로 전환하시겠습니까?</p><form method="post" action="{{ route('mypage.point.convert') }}">@csrf<input type="hidden" name="shop_channel_id" id="point-convert-shop"><div class="btm_btn"><button type="submit" class="btn_submit">확인</button><button type="button" class="col3 close_btn">취소</button></div></form></div></div></div></div>
<div class="popup_bx" id="point-shops-modal" role="dialog" aria-modal="true" aria-labelledby="point-shops-title"><div class="pop_w"><div class="pop_inner"><div class="pop_con w800"><button class="close_btn close1" type="button" aria-label="채널 목록 닫기">닫기</button><h2 id="point-shops-title" class="ttl01">Shop 채널 목록</h2><p class="member-help-text">포인트는 적립된 채널에서 사용할 수 있습니다. 채널별 잔액을 확인해 주세요.</p>
@foreach($shopChannelsByVendor as $vendorId=>$channels)<div class="point-vendor-shops member-table-scroll" data-vendor="{{ $vendorId }}" hidden><div class="tb01"><table><thead><tr><th>No</th><th>Shop 채널명</th><th>QR 코드</th><th>바로가기</th></tr></thead><tbody>
@foreach($channels as $channel)<tr><td>{{ $loop->iteration }}</td><td class="member-channel-name"><strong>{{ $channel['name'] }}</strong><small>{{ $channel['code'] }} · 잔액 {{ number_format($channel['points']) }} P</small></td><td><button type="button" class="member-qr-open point-qr-open" data-name="{{ $channel['name'] }}" data-url="{{ $channel['url'] }}" data-image="{{ $channel['large_qr'] }}" aria-label="{{ $channel['name'] }} QR 확대"><img src="{{ $channel['qr'] }}" alt="{{ $channel['name'] }} QR" width="72" height="72"></button></td><td><a href="{{ $channel['url'] }}" class="member-list-primary">바로가기</a></td></tr>@endforeach
</tbody></table></div></div>@endforeach
<div class="btm_btn"><button type="button" class="btn_submit close_btn">창닫기</button></div></div></div></div></div>
<div class="popup_bx member-qr-overlay" id="point-qr-modal" role="dialog" aria-modal="true" aria-labelledby="point-qr-title"><div class="pop_w"><div class="pop_inner"><div class="pop_con w560 member-qr-modal"><button type="button" class="close_btn close1" aria-label="QR 닫기">닫기</button><h2 id="point-qr-title"></h2><p id="point-qr-url"></p><img id="point-qr-image" alt="확대 QR 코드" width="240" height="240"><div class="btm_btn"><button type="button" class="btn_submit close_btn">닫기</button></div></div></div></div></div>
@endsection
@push('scripts')
<script>
const openPointModal=id=>{document.getElementById(id).style.display='block';document.querySelector('#'+id+' .close_btn').focus();};
document.querySelectorAll('.point-convert-open').forEach(button=>button.addEventListener('click',()=>{document.getElementById('point-convert-amount').textContent=button.dataset.points;document.getElementById('point-convert-shop').value=button.dataset.shop;openPointModal('point-convert-modal');}));
document.querySelectorAll('.point-shop-open').forEach(button=>button.addEventListener('click',()=>{document.querySelectorAll('.point-vendor-shops').forEach(section=>{section.hidden=section.dataset.vendor!==button.dataset.vendor;});openPointModal('point-shops-modal');}));
document.querySelectorAll('.point-qr-open').forEach(button=>button.addEventListener('click',()=>{document.getElementById('point-qr-title').textContent=button.dataset.name;document.getElementById('point-qr-url').textContent=button.dataset.url;document.getElementById('point-qr-image').src=button.dataset.image;openPointModal('point-qr-modal');}));
document.querySelectorAll('#point-convert-modal .close_btn,#point-shops-modal .close_btn,#point-qr-modal .close_btn').forEach(button=>button.addEventListener('click',()=>{button.closest('.popup_bx').style.display='none';}));
</script>
@endpush
