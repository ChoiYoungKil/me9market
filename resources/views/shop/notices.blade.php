@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
<div class="shop-notice-container" style="background: #0f172a; min-height: 100vh; color: #f8fafc; font-family: 'Inter', sans-serif; padding-bottom: 80px;">
    <!-- Shop Top Navigation -->
    <div style="background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100;">
        <div style="font-size: 24px; font-weight: 800; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-family: 'Outfit', sans-serif;">
            <a href="{{ route('shop.channel_main') }}" style="text-decoration: none; color: inherit;">Me9 Shop</a>
        </div>
        <div style="display: flex; gap: 30px; font-weight: 600; font-size: 15px;">
            <a href="{{ route('shop.channel_main') }}" style="color: #cbd5e1; text-decoration: none;">홈</a>
            <a href="{{ route('shop.products_list') }}" style="color: #cbd5e1; text-decoration: none;">상품전체</a>
            <a href="{{ route('shop.joint_purchases_list') }}" style="color: #cbd5e1; text-decoration: none;">공동구매특가</a>
            <a href="{{ route('shop.notices') }}" style="color: #6366f1; text-decoration: none;">공지사항</a>
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <a href="/shop/cart" style="color: #cbd5e1; text-decoration: none; font-size: 14px; position: relative;">
                장바구니 <span style="background: #ef4444; color: #fff; border-radius: 50%; padding: 2px 6px; font-size: 10px; font-weight: 700; margin-left: 2px;">2</span>
            </a>
            <a href="/mypage/dashboard" style="color: #cbd5e1; text-decoration: none; font-size: 14px;">마이페이지</a>
        </div>
    </div>

    <div style="max-width: 1000px; margin: 40px auto; padding: 0 20px;">
        <div style="border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 20px; margin-bottom: 30px;">
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 800; margin: 0;">📋 Shop 공지사항</h1>
            <p style="color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;">상점 내 공지사항 및 혜택 정보를 안내해 드립니다.</p>
        </div>

        <!-- Accordion Notice List -->
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <!-- Notice 1 -->
            <div class="notice-item" style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; overflow: hidden; transition: all 0.3s;">
                <div class="notice-header" onclick="toggleNotice(1)" style="padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; transition: background 0.3s;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span style="background: rgba(99, 102, 241, 0.2); color: #a5b4fc; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;">중요</span>
                        <span style="font-weight: 600; font-size: 16px; color: #f8fafc;">[안내] 신규 회원 가입 웰컴 포인트 지급 프로모션 안내</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 20px; font-size: 13px; color: #64748b;">
                        <span>2026-06-21</span>
                        <span class="notice-arrow" id="arrow-1" style="transition: transform 0.3s;">▼</span>
                    </div>
                </div>
                <div class="notice-content" id="content-1" style="display: none; padding: 25px; background: rgba(15, 23, 42, 0.4); border-top: 1px solid rgba(255, 255, 255, 0.05); line-height: 1.8; color: #cbd5e1; font-size: 14px;">
                    안녕하세요. Me9 Shop 채널 운영진입니다.<br><br>
                    저희 상점의 공식 오픈을 기념하여, 신규 가입하신 모든 회원님들께 즉시 사용 가능한 <strong>웰컴 포인트 3,000점</strong>을 지급해 드립니다.<br>
                    많은 관심과 혜택 이용 부탁드립니다. 감사합니다.
                </div>
            </div>

            <!-- Notice 2 -->
            <div class="notice-item" style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 16px; overflow: hidden; transition: all 0.3s;">
                <div class="notice-header" onclick="toggleNotice(2)" style="padding: 20px 25px; display: flex; justify-content: space-between; align-items: center; cursor: pointer; transition: background 0.3s;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span style="background: rgba(255,255,255,0.08); color: #cbd5e1; padding: 4px 10px; border-radius: 6px; font-size: 12px; font-weight: 700;">공지</span>
                        <span style="font-weight: 600; font-size: 16px; color: #f8fafc;">[배송] 2026년 단오절 연휴 배송 및 고객센터 휴무 안내</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 20px; font-size: 13px; color: #64748b;">
                        <span>2026-06-18</span>
                        <span class="notice-arrow" id="arrow-2" style="transition: transform 0.3s;">▼</span>
                    </div>
                </div>
                <div class="notice-content" id="content-2" style="display: none; padding: 25px; background: rgba(15, 23, 42, 0.4); border-top: 1px solid rgba(255, 255, 255, 0.05); line-height: 1.8; color: #cbd5e1; font-size: 14px;">
                    안녕하세요. Me9 Shop입니다.<br><br>
                    연휴 기간 동안 배송 업체의 집하가 중단됨에 따라 아래와 같이 배송 일정을 공지합니다.<br>
                    - 배송 중단 기간: 2026년 6월 22일(월) ~ 6월 23일(화)<br>
                    - 순차 배송 재개: 2026년 6월 24일(수) 부터<br>
                    연휴 전 배송을 원하시는 분들은 서둘러 주문을 부탁드립니다. 즐거운 연휴 보내세요!
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleNotice(id) {
    var content = $('#content-' + id);
    var arrow = $('#arrow-' + id);
    
    if (content.is(':visible')) {
        content.stop().slideUp(300);
        arrow.css('transform', 'rotate(0deg)');
    } else {
        content.stop().slideDown(300);
        arrow.css('transform', 'rotate(180deg)');
    }
}
</script>

<style>
.notice-header:hover {
    background: rgba(255, 255, 255, 0.03);
}
</style>
@endsection
