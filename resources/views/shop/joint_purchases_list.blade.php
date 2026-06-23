@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
<div class="shop-joint-list-container" style="background: #0f172a; min-height: 100vh; color: #f8fafc; font-family: 'Inter', sans-serif; padding-bottom: 80px;">
    <!-- Shop Top Navigation -->
    <div style="background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100;">
        <div style="font-size: 24px; font-weight: 800; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-family: 'Outfit', sans-serif;">
            <a href="{{ route('shop.channel_main') }}" style="text-decoration: none; color: inherit;">Me9 Shop</a>
        </div>
        <div style="display: flex; gap: 30px; font-weight: 600; font-size: 15px;">
            <a href="{{ route('shop.channel_main') }}" style="color: #cbd5e1; text-decoration: none;">홈</a>
            <a href="{{ route('shop.products_list') }}" style="color: #cbd5e1; text-decoration: none;">상품전체</a>
            <a href="{{ route('shop.joint_purchases_list') }}" style="color: #6366f1; text-decoration: none;">공동구매특가</a>
            <a href="{{ route('shop.notices') }}" style="color: #cbd5e1; text-decoration: none;">공지사항</a>
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <a href="/shop/cart" style="color: #cbd5e1; text-decoration: none; font-size: 14px; position: relative;">
                장바구니 <span style="background: #ef4444; color: #fff; border-radius: 50%; padding: 2px 6px; font-size: 10px; font-weight: 700; margin-left: 2px;">2</span>
            </a>
            <a href="/mypage/dashboard" style="color: #cbd5e1; text-decoration: none; font-size: 14px;">마이페이지</a>
        </div>
    </div>

    <div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <div style="border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 20px; margin-bottom: 30px;">
            <h1 style="font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 800; margin: 0;">🤝 공동구매 특가 마켓</h1>
            <p style="color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;">목표 수량을 달성하면 최고 할인 혜택이 적용되는 실시간 공동구매 상품입니다.</p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 30px;">
            <!-- Card 1 -->
            <div class="jp-card" style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 24px; overflow: hidden; transition: all 0.3s; cursor: pointer;" onclick="location.href='{{ route('shop.joint_purchase_details', 1) }}'">
                <div style="height: 240px; background: #1e293b; display: flex; align-items: center; justify-content: center; position: relative;">
                    <span style="position: absolute; top: 15px; left: 15px; background: #ef4444; color: #fff; padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;">공동구매</span>
                    <span style="position: absolute; top: 15px; right: 15px; background: rgba(0, 0, 0, 0.6); color: #fff; padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;">D-5</span>
                    <div style="font-size: 64px;">👕</div>
                </div>
                <div style="padding: 25px;">
                    <h3 style="font-size: 19px; font-weight: 700; margin: 0 0 15px 0; color: #f8fafc; height: 54px; overflow: hidden;">Comfortable Cotton T-Shirt (Premium Black)</h3>
                    
                    <!-- Progress bar -->
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; font-size: 13px; color: #cbd5e1; margin-bottom: 8px;">
                            <span>실시간 달성률</span>
                            <span style="color: #a855f7; font-weight: 800;">85% (달성 임박!)</span>
                        </div>
                        <div style="background: #334155; height: 10px; border-radius: 10px; overflow: hidden;">
                            <div style="background: linear-gradient(90deg, #6366f1, #a855f7); width: 85%; height: 100%;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: #64748b; margin-top: 6px;">
                            <span>목표 100개</span>
                            <span>현재 참여 85개</span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="text-decoration: line-through; color: #64748b; font-size: 14px;">29,000원</span>
                            <div style="font-size: 20px; font-weight: 800; color: #a855f7;">15,000원 <span style="font-size: 12px; font-weight: normal; color: #cbd5e1;">(48% 특가)</span></div>
                        </div>
                        <span style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white; padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; box-shadow: 0 4px 12px rgba(99,102,241,0.2);">참여하기</span>
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="jp-card" style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 24px; overflow: hidden; transition: all 0.3s; cursor: pointer;" onclick="location.href='{{ route('shop.joint_purchase_details', 2) }}'">
                <div style="height: 240px; background: #1e293b; display: flex; align-items: center; justify-content: center; position: relative;">
                    <span style="position: absolute; top: 15px; left: 15px; background: #ef4444; color: #fff; padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;">공동구매</span>
                    <span style="position: absolute; top: 15px; right: 15px; background: rgba(0, 0, 0, 0.6); color: #fff; padding: 5px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;">D-12</span>
                    <div style="font-size: 64px;">💼</div>
                </div>
                <div style="padding: 25px;">
                    <h3 style="font-size: 19px; font-weight: 700; margin: 0 0 15px 0; color: #f8fafc; height: 54px; overflow: hidden;">Premium Full-Grain Leather Business Wallet</h3>
                    
                    <!-- Progress bar -->
                    <div style="margin-bottom: 20px;">
                        <div style="display: flex; justify-content: space-between; font-size: 13px; color: #cbd5e1; margin-bottom: 8px;">
                            <span>실시간 달성률</span>
                            <span style="color: #a855f7; font-weight: 800;">45%</span>
                        </div>
                        <div style="background: #334155; height: 10px; border-radius: 10px; overflow: hidden;">
                            <div style="background: linear-gradient(90deg, #6366f1, #a855f7); width: 45%; height: 100%;"></div>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: #64748b; margin-top: 6px;">
                            <span>목표 50개</span>
                            <span>현재 참여 22개</span>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <div>
                            <span style="text-decoration: line-through; color: #64748b; font-size: 14px;">89,000원</span>
                            <div style="font-size: 20px; font-weight: 800; color: #a855f7;">54,000원 <span style="font-size: 12px; font-weight: normal; color: #cbd5e1;">(39% 특가)</span></div>
                        </div>
                        <span style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: white; padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 700; box-shadow: 0 4px 12px rgba(99,102,241,0.2);">참여하기</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.jp-card:hover {
    transform: translateY(-5px);
    border-color: rgba(168, 85, 247, 0.4) !important;
    box-shadow: 0 12px 30px rgba(168, 85, 247, 0.2) !important;
}
</style>
@endsection
