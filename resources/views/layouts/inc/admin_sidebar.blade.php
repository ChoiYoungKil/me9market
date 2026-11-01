
@php
$current=Session::get('page');
if (request()->is('admin/products')) {
    $current=request()->query('view')==='gallery' ? 'products-gallery' : 'products';
} elseif (request()->is('admin/users')) {
    $current=request()->query('seller_status')==='pending' ? 'seller-review' : 'users';
} elseif (request()->is('admin/add-edit-user','admin/add-edit-user/*')) {
    $current='users';
} elseif (request()->routeIs('admin.users.seller_review')) {
    $current='seller-review';
} elseif (request()->is('admin/admins','admin/admins/*','admin/add-edit-admin','admin/add-edit-admin/*','admin/view-vendor-details/*')) {
    $current='admins';
}
$full=in_array(Auth::guard('admin')->user()?->type,['admin','superadmin'],true);
$groups=[
 ['대시보드 홈','dashboard','admin/dashboard',[]],
 ['회원관리','members',null,[['회원 리스트','users','admin/users'],['판매자 승인 대기','seller-review','admin/users?seller_status=pending'],['관리자/판매자','admins','admin/admins']]],
 ['Shop 채널','channels',null,[['운영중지 승인','shop-channel-closures','admin/shop-channel-closures']]],
 ['상품관리','products',null,[['상품 목록','products','admin/products'],['상품 갤러리','products-gallery','admin/products?view=gallery'],['분류관리','categories','admin/categories'],['상품 설정','configuration',null,[['섹션','sections','admin/sections'],['브랜드','brands','admin/brands'],['필터','filters','admin/filters'],['배너','banners','admin/banners'],['리뷰','ratings','admin/ratings'],['쿠폰','coupons','admin/coupons']]]]],
 ['주문관리','orders','admin/orders',[]],
 ['고객센터','support',null,[['공지사항','notices','admin/notices'],['자주묻는질문','faqs','admin/faqs'],['제휴/문의','contacts','admin/contacts']]],
 ['발주관리','order_managers','admin/order-managers',[]],
 ['포인트관리','channel_points','admin/channel-points',[]],
 ['배송비설정','shipping','admin/shipping-charges',[]],
 ['취소/환불안내','refund_policies','admin/refund-policies',[]],
 ['정산관리','settlements','admin/settlements',[]],
 ['처리 이력','activities','admin/activities',[]],
];
$activeKeys=['members'=>['users','seller-review','admins','add_edit_user','add_edit_admin','view_vendor_details'],'channels'=>['shop-channel-closures'],'products'=>['products','products-gallery','categories','sections','brands','filters','attributes','banners','coupons','ratings'],'support'=>['notices','faqs','contacts'],'channel_points'=>['channel_points','channel-points']];
@endphp
<div id="l_menu"><div class="con_bx"><div class="con1"><div class="con_w"><ul class="dep1_wrap">
@foreach($groups as [$label,$key,$path,$children])
@if($full || in_array($key,['dashboard','products','orders'],true))
@php($active=in_array($current,$activeKeys[$key]??[$key],true))
<li class="dep1 {{ $children?'arrow':'' }} {{ $active?'on':'' }}">
@if($path)<a href="{{ url($path) }}" @if($key==='dashboard') class="admin-dashboard-home" @endif
@if($active) aria-current="page"
@endif>{{ $label }}</a>
@else<a href="{{ url()->current() }}" aria-expanded="{{ $active?'true':'false' }}">{{ $label }}</a>
@endif
@if($children)<ul class="dep2_wrap">
@foreach($children as $child)
@if($full || in_array($child[1],['products','products-gallery','coupons'],true))
<li class="{{ $current===$child[1]?'on':'' }}">
@if($child[2])<a href="{{ url($child[2]) }}" @if($current===$child[1]) aria-current="page" @endif>{{ $child[0] }}</a>
@else<details
@if(in_array($current,['sections','brands','filters','banners','ratings','coupons'],true)) open
@endif><summary>{{ $child[0] }}</summary><ul class="admin-submenu">
@foreach($child[3] as $leaf)<li class="{{ $current===$leaf[1]?'on':'' }}"><a href="{{ url($leaf[2]) }}">{{ $leaf[0] }}</a></li>
@endforeach</ul></details>
@endif</li>
@endif
@endforeach</ul>
@endif</li>
@endif
@endforeach
</ul></div><div class="con_w"><div class="c_ttl">바로가기</div><nav class="admin-shortcuts" aria-label="관리자 바로가기"><a href="{{ url('admin/users?seller_status=pending') }}">판매자 승인 대기</a><a href="{{ url('admin/orders?item_status=cancel_requested') }}">취소 요청</a><a href="{{ url('admin/orders?item_status=return_requested') }}">반품 요청</a><a href="{{ url('admin/orders?item_status=exchange_requested') }}">교환 요청</a><a href="{{ url('admin/shop-channel-closures') }}">채널 운영중지 승인</a><a href="{{ url('admin/content/notices/create') }}">공지 작성</a><a href="{{ route('home') }}">Me9 Market</a><a href="{{ url('admin/update-admin-details') }}">내 정보</a></nav></div></div></div></div>
