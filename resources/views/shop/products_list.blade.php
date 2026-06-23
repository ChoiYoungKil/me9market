@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
<div class="shop-products-container" style="background: #0f172a; min-height: 100vh; color: #f8fafc; font-family: 'Inter', sans-serif; padding-bottom: 80px;">
    <!-- Shop Top Navigation -->
    <div style="background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100;">
        <div style="font-size: 24px; font-weight: 800; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-family: 'Outfit', sans-serif;">
            <a href="{{ route('shop.channel_main') }}" style="text-decoration: none; color: inherit;">Me9 Shop</a>
        </div>
        <div style="display: flex; gap: 30px; font-weight: 600; font-size: 15px;">
            <a href="{{ route('shop.channel_main') }}" style="color: #cbd5e1; text-decoration: none;">홈</a>
            <a href="{{ route('shop.products_list') }}" style="color: #6366f1; text-decoration: none;">상품전체</a>
            <a href="{{ route('shop.joint_purchases_list') }}" style="color: #cbd5e1; text-decoration: none;">공동구매특가</a>
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
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 20px; margin-bottom: 30px;">
            <div>
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 800; margin: 0;">전체 상품 리스트</h1>
                <p style="color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;">상시 할인 혜택이 주어지는 전 상품 목록입니다.</p>
            </div>
            
            <div style="display: flex; gap: 10px; align-items: center;">
                <select style="background: #1e293b; color: #fff; border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 8px; padding: 8px 16px; font-size: 14px; outline: none;">
                    <option>최신등록순</option>
                    <option>가격낮은순</option>
                    <option>가격높은순</option>
                    <option>인기순</option>
                </select>
            </div>
        </div>

        <!-- Product Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
            <!-- Card 1 -->
            <div class="product-card" style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; overflow: hidden; transition: all 0.3s; cursor: pointer;" onclick="location.href='{{ route('shop.product_details', 1) }}'">
                <div style="height: 220px; background: #1e293b; display: flex; align-items: center; justify-content: center; position: relative;">
                    <span style="position: absolute; top: 15px; left: 15px; background: #6366f1; color: #fff; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 700;">상시특가</span>
                    <div style="font-size: 48px;">🎧</div>
                </div>
                <div style="padding: 20px;">
                    <h3 style="font-size: 17px; font-weight: 600; margin: 0 0 10px 0; color: #f8fafc; height: 48px; overflow: hidden;">Active Noise Cancelling Wireless Headphones</h3>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                        <div>
                            <span style="text-decoration: line-through; color: #64748b; font-size: 13px;">149,000원</span>
                            <div style="font-size: 18px; font-weight: 700; color: #6366f1;">99,000원 <span style="font-size: 12px; font-weight: normal; color: #94a3b8;">(33% Off)</span></div>
                        </div>
                        <span style="background: rgba(99, 102, 241, 0.15); color: #a5b4fc; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;">상세보기</span>
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="product-card" style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; overflow: hidden; transition: all 0.3s; cursor: pointer;" onclick="location.href='{{ route('shop.product_details', 2) }}'">
                <div style="height: 220px; background: #1e293b; display: flex; align-items: center; justify-content: center; position: relative;">
                    <span style="position: absolute; top: 15px; left: 15px; background: #6366f1; color: #fff; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 700;">상시특가</span>
                    <div style="font-size: 48px;">⌚</div>
                </div>
                <div style="padding: 20px;">
                    <h3 style="font-size: 17px; font-weight: 600; margin: 0 0 10px 0; color: #f8fafc; height: 48px; overflow: hidden;">Smart Sports Watch (GPS + Heart Rate Tracker)</h3>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                        <div>
                            <span style="text-decoration: line-through; color: #64748b; font-size: 13px;">229,000원</span>
                            <div style="font-size: 18px; font-weight: 700; color: #6366f1;">159,000원 <span style="font-size: 12px; font-weight: normal; color: #94a3b8;">(30% Off)</span></div>
                        </div>
                        <span style="background: rgba(99, 102, 241, 0.15); color: #a5b4fc; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;">상세보기</span>
                    </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="product-card" style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; overflow: hidden; transition: all 0.3s; cursor: pointer;" onclick="location.href='{{ route('shop.product_details', 3) }}'">
                <div style="height: 220px; background: #1e293b; display: flex; align-items: center; justify-content: center; position: relative;">
                    <span style="position: absolute; top: 15px; left: 15px; background: #6366f1; color: #fff; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 700;">상시특가</span>
                    <div style="font-size: 48px;">⌨️</div>
                </div>
                <div style="padding: 20px;">
                    <h3 style="font-size: 17px; font-weight: 600; margin: 0 0 10px 0; color: #f8fafc; height: 48px; overflow: hidden;">Mechanical Backlit Gaming Keyboard (Blue Switch)</h3>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                        <div>
                            <span style="text-decoration: line-through; color: #64748b; font-size: 13px;">79,000원</span>
                            <div style="font-size: 18px; font-weight: 700; color: #6366f1;">49,000원 <span style="font-size: 12px; font-weight: normal; color: #94a3b8;">(38% Off)</span></div>
                        </div>
                        <span style="background: rgba(99, 102, 241, 0.15); color: #a5b4fc; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;">상세보기</span>
                    </div>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="product-card" style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; overflow: hidden; transition: all 0.3s; cursor: pointer;" onclick="location.href='{{ route('shop.product_details', 4) }}'">
                <div style="height: 220px; background: #1e293b; display: flex; align-items: center; justify-content: center; position: relative;">
                    <span style="position: absolute; top: 15px; left: 15px; background: #6366f1; color: #fff; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 700;">상시특가</span>
                    <div style="font-size: 48px;">💼</div>
                </div>
                <div style="padding: 20px;">
                    <h3 style="font-size: 17px; font-weight: 600; margin: 0 0 10px 0; color: #f8fafc; height: 48px; overflow: hidden;">Premium Full-Grain Leather Wallet</h3>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
                        <div>
                            <span style="text-decoration: line-through; color: #64748b; font-size: 13px;">89,000원</span>
                            <div style="font-size: 18px; font-weight: 700; color: #6366f1;">59,000원 <span style="font-size: 12px; font-weight: normal; color: #94a3b8;">(33% Off)</span></div>
                        </div>
                        <span style="background: rgba(99, 102, 241, 0.15); color: #a5b4fc; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;">상세보기</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.product-card:hover {
    transform: translateY(-5px);
    border-color: rgba(99, 102, 241, 0.4) !important;
    box-shadow: 0 12px 30px rgba(99, 102, 241, 0.2) !important;
}
</style>
@endsection
