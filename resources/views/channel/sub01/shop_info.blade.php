@extends('layouts.channel')
@php
    $dep1_id='01'; $dep1_tit='Shop채널관리';
    $keywords=app(\App\Services\ShopChannelConfiguration::class)->keywords($shop->keywords);
    $banners=$shop->banner_images; if(is_string($banners))$banners=json_decode($banners,true); $banners=$banners?:[];
    $templates=$shop->purchase_sms_templates??[];
@endphp
@section('page_type','sub')
@section('content')
<div id="container_w"><div id="contents"><div class="channel-shop" id="channel-shop-detail">
<header><p>MY CHANNEL</p><h1>Shop채널 상세페이지</h1><p>{{ $shop->channel_name }}</p></header>
@if(session('success_message'))<p class="shop-feedback" role="status">{{ session('success_message') }}</p>@endif
<nav class="shop-tabs" aria-label="Shop 채널 상세 메뉴">
<a href="{{ route('channel.shop_info',['id'=>$shop->id]) }}" aria-current="page">Shop채널 정보</a>
<a href="{{ route('channel.shop_product01',['shop_id'=>$shop->id]) }}">판매상품</a>
<a href="{{ route('channel.shop_community',['shop_id'=>$shop->id]) }}">커뮤니티</a>
</nav>
<section class="shop-panel"><h2>Shop 채널 기본정보</h2><dl class="shop-details">
<div><dt>Shop 채널코드</dt><dd>{{ $shop->channel_code }}</dd></div>
<div><dt>등록일</dt><dd>{{ $shop->created_at?->format('Y-m-d H:i') }}</dd></div>
<div><dt>채널상태</dt><dd>{{ $shop->status==1?'운영':'중지' }}</dd></div>
<div><dt>공개여부</dt><dd>{{ $shop->is_public?'공개':'비공개' }} @unless($shop->is_public)<span> · 접속가능인원 {{ $shop->privateAccesses->count() }}명</span> <button type="button" data-detail-private>인원관리</button>@endunless</dd></div>
<div><dt>채널명</dt><dd>{{ $shop->channel_name }}</dd></div>
<div><dt>구매권한</dt><dd>{{ $shop->is_member_only?'회원전용':'비회원 구매가능' }}</dd></div>
<div><dt>채널사용주기</dt><dd>@if($shop->use_period_type)기간제 / {{ $shop->start_at?->format('Y-m-d H시') }} ~ {{ $shop->end_at?->format('Y-m-d H시') }}@else무기한@endif</dd></div>
<div><dt>카피라이트</dt><dd>{{ $shop->copyright?:'-' }}</dd></div>
<div><dt>그룹 keyword</dt><dd><div class="keyword-tags">@forelse($keywords as $keyword)<span class="keyword-tag">#{{ $keyword }}</span>@empty-@endforelse</div></dd></div>
</dl></section>
<section class="shop-panel"><h2>Shop 채널 PG 정보</h2><dl class="shop-details">
<div><dt>자사 PG 사용여부</dt><dd>{{ $shop->use_own_pg?'사용':'사용하지 않음' }}</dd></div>
@if($shop->use_own_pg)
<div><dt>자사 PG 모듈</dt><dd>{{ ['inicis'=>'KG 이니시스','kcp'=>'NHN KCP','toss'=>'토스페이먼츠'][$shop->pg_provider]??'-' }}</dd></div>
<div><dt>상점 ID / 사이트 코드</dt><dd>{{ $shop->pg_merchant_id?'등록됨':'미등록' }} / {{ $shop->pg_site_code?'등록됨':'미등록' }}</dd></div>
<div><dt>Client Key / Secret Key</dt><dd>******** / ********</dd></div>
@endif
</dl></section>
<section class="shop-panel"><h2>OG TAG</h2><dl class="shop-details">
<div><dt>OG 사용여부</dt><dd>{{ $shop->use_og?'사용':'미사용' }}</dd></div>
@if($shop->use_og)
<div><dt>Title</dt><dd>{{ $shop->og_title }}</dd></div><div><dt>Description</dt><dd>{{ $shop->og_description }}</dd></div>
<div><dt>Image</dt><dd class="image-preview">@if($shop->og_image)<a href="{{ asset($shop->og_image) }}" target="_blank" rel="noopener"><img src="{{ asset($shop->og_image) }}" alt="OG 이미지"></a>@else등록된 이미지가 없습니다.@endif</dd></div>
@endif
</dl></section>
<section class="shop-panel"><h2>이미지</h2><dl class="shop-details">
<div><dt>로고 이미지</dt><dd class="image-preview">@if($shop->use_logo && $shop->logo_image)<a href="{{ asset($shop->logo_image) }}" target="_blank" rel="noopener"><img src="{{ asset($shop->logo_image) }}" alt="채널 로고 이미지"></a>@else미사용 · 채널명 표시@endif</dd></div>
<div><dt>메인 배너 이미지</dt><dd>@if($shop->use_banner)<div class="banner-previews">@forelse($banners as $banner)<figure><a href="{{ asset($banner) }}" target="_blank" rel="noopener"><img src="{{ asset($banner) }}" alt="배너 이미지 {{ $loop->iteration }}"></a><figcaption>#{{ $loop->iteration }}</figcaption></figure>@empty등록된 이미지가 없습니다.@endforelse</div>@else미사용@endif</dd></div>
</dl></section>
<section class="shop-panel"><h2>구매현황 문자알림</h2><dl class="shop-details">
<div><dt>문자알림 사용여부</dt><dd>{{ $shop->use_purchase_sms?'사용':'미사용' }}</dd></div>
@if($shop->use_purchase_sms)
@foreach(['purchase'=>'상품구매알림','purchase_confirmed'=>'상품구매확정알림','cancel'=>'상품취소알림','return'=>'상품반품알림'] as $key=>$label)
<div><dt>{{ $label }}</dt><dd><p class="sms-text">{{ $templates[$key]??'' }}</p><small>{{ mb_strlen($templates[$key]??'') }} / 300</small></dd></div>
@endforeach
@endif
</dl></section>
<section class="shop-panel"><h2>Shop 채널 (모니터링) 관리자 정보</h2><dl class="shop-details">
<div><dt>Shop 채널 관리자 여부</dt><dd>{{ $shop->use_admin?'사용':'미사용' }}</dd></div>
@if($shop->use_admin)
<div><dt>Shop 채널 관리자 성명</dt><dd>{{ $shop->admin_name }}</dd></div>
<div><dt>로그인 ID</dt><dd>{{ $shop->admin_login_id }}</dd></div>
<div><dt>로그인 PW</dt><dd>********</dd></div>
<div><dt>정산방법</dt><dd>{{ $shop->settlement_type==2?'판매 개당 금액':'판매금액 대비 % 지급' }}</dd></div>
<div><dt>정산요율</dt><dd>{{ number_format((float)$shop->settlement_rate,$shop->settlement_type==2?0:2) }} {{ $shop->settlement_type==2?'원 / 판매 개당':'%' }}</dd></div>
@endif
</dl></section>
@unless($shop->is_public)
<dialog id="detail-private-dialog"><h2>{{ $shop->channel_code }} 비공개 접속인원관리</h2><p>접속가능인원 {{ $shop->privateAccesses->count() }}명</p><p class="shop-help">명단 변경은 정보수정의 인원관리에서 진행할 수 있습니다.</p>
<div class="table-scroll"><table><thead><tr><th>No</th><th>Shop 채널코드</th><th>핸드폰 번호</th><th>입장 코드</th><th>등록일</th></tr></thead><tbody>
@forelse($shop->privateAccesses as $access)<tr><td>{{ $loop->iteration }}</td><td>{{ $shop->channel_code }}</td><td>{{ $access->phone }}</td><td>{{ $access->entry_code }}</td><td>{{ $access->created_at?->format('Y-m-d H:i') }}</td></tr>@empty<tr><td colspan="5">등록된 접속인원이 없습니다.</td></tr>@endforelse
</tbody></table></div><div class="shop-actions"><a href="{{ route('channel.info_update',$shop->id) }}">정보수정</a><button type="button" data-detail-close class="secondary">닫기</button></div></dialog>
@endunless
<div class="shop-actions"><a href="{{ route('channel.info_update',$shop->id) }}">정보수정</a><a href="{{ route('channel.shop.open',$shop->id) }}" target="_blank" rel="noopener">Shop채널보기</a><a href="{{ route('channel.shop_list') }}" class="secondary">목록</a></div>
</div></div></div>
@endsection
@push('styles')<link rel="stylesheet" href="{{ asset('channel_assets/css/shop-configuration.css') }}">@endpush
@push('scripts')<script src="{{ asset('channel_assets/js/shop-detail.js') }}"></script>@endpush
