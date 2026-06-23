@extends('layouts.shop')

@section('page_type', 'main')

@section('content')
<div class="shop-main-container" style="background: #0f172a; min-height: 100vh; color: #f8fafc; font-family: 'Inter', sans-serif;">
    <!-- Shop Top Navigation -->
    <div style="background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100;">
        <div style="font-size: 24px; font-weight: 800; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-family: 'Outfit', sans-serif;">
            <a href="{{ route('shop.channel_main') }}" style="text-decoration: none; color: inherit;">Me9 Shop</a>
        </div>
        <div style="display: flex; gap: 30px; font-weight: 600; font-size: 15px;">
            <a href="{{ route('shop.channel_main') }}" style="color: #6366f1; text-decoration: none;">홈</a>
            <a href="{{ route('shop.products_list') }}" style="color: #cbd5e1; text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#cbd5e1'">상품전체</a>
            <a href="{{ route('shop.joint_purchases_list') }}" style="color: #cbd5e1; text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#cbd5e1'">공동구매특가</a>
            <a href="{{ route('shop.notices') }}" style="color: #cbd5e1; text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#cbd5e1'">공지사항</a>
        </div>
        <div style="display: flex; gap: 15px; align-items: center;">
            <a href="/shop/cart" style="color: #cbd5e1; text-decoration: none; font-size: 14px; position: relative;">
                장바구니 <span style="background: #ef4444; color: #fff; border-radius: 50%; padding: 2px 6px; font-size: 10px; font-weight: 700; margin-left: 2px;">2</span>
            </a>
            <a href="/mypage/dashboard" style="color: #cbd5e1; text-decoration: none; font-size: 14px;">마이페이지</a>
        </div>
    </div>

    @if(session('flash_message_success'))
        <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; padding: 15px 40px; font-size: 14px; font-weight: 600; text-align: center;">
            {{ session('flash_message_success') }}
        </div>
    @endif

    <!-- Hero Banner Carousel/Slide mockup -->
    <div style="background: linear-gradient(135deg, #1e1b4b 0%, #311042 100%); padding: 80px 40px; text-align: center; position: relative; overflow: hidden; border-bottom: 1px solid rgba(255, 255, 255, 0.05);">
        <div style="position: absolute; top: 0; right: 0; width: 400px; height: 400px; background: rgba(99, 102, 241, 0.15); filter: blur(80px); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: 0; left: 0; width: 300px; height: 300px; background: rgba(168, 85, 247, 0.15); filter: blur(80px); border-radius: 50%;"></div>
        
        <div style="max-width: 800px; margin: 0 auto; position: relative; z-index: 2;">
            <span style="background: rgba(99, 102, 241, 0.2); color: #a5b4fc; padding: 6px 16px; border-radius: 30px; font-size: 13px; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;">Channel Member Special Benefit</span>
            <h1 style="font-size: 48px; font-weight: 800; line-height: 1.2; margin: 20px 0 15px 0; font-family: 'Outfit', sans-serif;">공동구매 달성 시 최대 45% 할인</h1>
            <p style="color: #94a3b8; font-size: 18px; margin-bottom: 30px; line-height: 1.6;">회원들이 모일수록 가격은 내려갑니다. 실시간 핫딜 상품들을 지금 확인하세요.</p>
            <a href="{{ route('shop.joint_purchases_list') }}" style="background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: #fff; padding: 15px 35px; border-radius: 12px; font-size: 16px; font-weight: 700; text-decoration: none; display: inline-block; box-shadow: 0 10px 25px rgba(99, 102, 241, 0.4); transition: transform 0.3s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">진행중인 공동구매 보기</a>
        </div>
    </div>

    <!-- Main Sections -->
    <div style="max-width: 1200px; margin: 60px auto; padding: 0 20px;">
        
        <!-- Section 1: Active Joint Purchases -->
        <div style="margin-bottom: 60px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px;">
                <div>
                    <h2 style="font-family: 'Outfit', sans-serif; font-size: 28px; font-weight: 700; margin: 0;">🔥 실시간 인기 공동구매</h2>
                    <p style="color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;">목표 수량을 채우면 초특가 할인 가격이 적용됩니다!</p>
                </div>
                <a href="{{ route('shop.joint_purchases_list') }}" style="color: #818cf8; text-decoration: none; font-size: 14px; font-weight: 600;">전체보기 &rarr;</a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
                <!-- Card 1 -->
                <div class="product-card" style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; overflow: hidden; transition: all 0.3s; cursor: pointer;" onclick="location.href='{{ route('shop.joint_purchase_details', 1) }}'">
                    <div style="height: 220px; background: #1e293b; display: flex; align-items: center; justify-content: center; position: relative;">
                        <span style="position: absolute; top: 15px; left: 15px; background: #ef4444; color: #fff; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 700;">공동구매</span>
                        <span style="position: absolute; top: 15px; right: 15px; background: rgba(0, 0, 0, 0.6); color: #fff; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 700;">D-5</span>
                        <div style="font-size: 48px;">👕</div>
                    </div>
                    <div style="padding: 20px;">
                        <h3 style="font-size: 17px; font-weight: 600; margin: 0 0 10px 0; color: #f8fafc; height: 48px; overflow: hidden;">Comfortable Cotton T-Shirt (Premium Black)</h3>
                        
                        <!-- Progress bar -->
                        <div style="margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; font-size: 12px; color: #94a3b8; margin-bottom: 5px;">
                                <span>현재 달성률</span>
                                <span style="color: #a855f7; font-weight: 700;">85%</span>
                            </div>
                            <div style="background: #334155; height: 8px; border-radius: 10px; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, #6366f1, #a855f7); width: 85%; height: 100%;"></div>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 11px; color: #64748b; margin-top: 5px;">
                                <span>목표 100개</span>
                                <span>현재 85개</span>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="text-decoration: line-through; color: #64748b; font-size: 13px;">29,000원</span>
                                <div style="font-size: 18px; font-weight: 700; color: #a855f7;">15,000원 <span style="font-size: 12px; font-weight: normal; color: #94a3b8;">(48% Off)</span></div>
                            </div>
                            <span style="background: rgba(99, 102, 241, 0.15); color: #a5b4fc; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;">참여하기</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2 -->
                <div class="product-card" style="background: rgba(30, 41, 59, 0.5); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 20px; overflow: hidden; transition: all 0.3s; cursor: pointer;" onclick="location.href='{{ route('shop.joint_purchase_details', 2) }}'">
                    <div style="height: 220px; background: #1e293b; display: flex; align-items: center; justify-content: center; position: relative;">
                        <span style="position: absolute; top: 15px; left: 15px; background: #ef4444; color: #fff; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 700;">공동구매</span>
                        <span style="position: absolute; top: 15px; right: 15px; background: rgba(0, 0, 0, 0.6); color: #fff; padding: 4px 10px; border-radius: 8px; font-size: 12px; font-weight: 700;">D-12</span>
                        <div style="font-size: 48px;">💼</div>
                    </div>
                    <div style="padding: 20px;">
                        <h3 style="font-size: 17px; font-weight: 600; margin: 0 0 10px 0; color: #f8fafc; height: 48px; overflow: hidden;">Premium Full-Grain Leather Business Wallet</h3>
                        
                        <!-- Progress bar -->
                        <div style="margin-bottom: 15px;">
                            <div style="display: flex; justify-content: space-between; font-size: 12px; color: #94a3b8; margin-bottom: 5px;">
                                <span>현재 달성률</span>
                                <span style="color: #a855f7; font-weight: 700;">45%</span>
                            </div>
                            <div style="background: #334155; height: 8px; border-radius: 10px; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, #6366f1, #a855f7); width: 45%; height: 100%;"></div>
                            </div>
                            <div style="display: flex; justify-content: space-between; font-size: 11px; color: #64748b; margin-top: 5px;">
                                <span>목표 50개</span>
                                <span>현재 22개</span>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <span style="text-decoration: line-through; color: #64748b; font-size: 13px;">89,000원</span>
                                <div style="font-size: 18px; font-weight: 700; color: #a855f7;">54,000원 <span style="font-size: 12px; font-weight: normal; color: #94a3b8;">(39% Off)</span></div>
                            </div>
                            <span style="background: rgba(99, 102, 241, 0.15); color: #a5b4fc; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 600;">참여하기</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Hot Normal Products -->
        <div>
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 30px;">
                <div>
                    <h2 style="font-family: 'Outfit', sans-serif; font-size: 28px; font-weight: 700; margin: 0;">✨ 추천 일반 상품 목록</h2>
                    <p style="color: #94a3b8; font-size: 14px; margin: 5px 0 0 0;">회원 전용 상시 특가 판매 상품들입니다.</p>
                </div>
                <a href="{{ route('shop.products_list') }}" style="color: #818cf8; text-decoration: none; font-size: 14px; font-weight: 600;">전체보기 &rarr;</a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
                <!-- Card 3 -->
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

                <!-- Card 4 -->
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
