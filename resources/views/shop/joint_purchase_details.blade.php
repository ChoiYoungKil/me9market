@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
<div class="shop-jp-details-container" style="background: #0f172a; min-height: 100vh; color: #f8fafc; font-family: 'Inter', sans-serif; padding-bottom: 80px;">
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

    <!-- Product Layout -->
    <div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <div style="display: flex; flex-direction: row; gap: 50px; flex-wrap: wrap;">
            <!-- Left: Product Image -->
            <div style="flex: 1; min-width: 300px;">
                <div style="background: #1e293b; height: 500px; border-radius: 24px; display: flex; align-items: center; justify-content: center; font-size: 120px; border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 20px 40px rgba(0,0,0,0.3); position: relative;">
                    <span style="position: absolute; top: 20px; left: 20px; background: #ef4444; color: #fff; padding: 6px 14px; border-radius: 10px; font-size: 14px; font-weight: 700;">공동구매 진행중</span>
                    @if($id == 1) 👕 @else 💼 @endif
                </div>
            </div>

            <!-- Right: Joint Purchase Info & Participation Area -->
            <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <span style="background: rgba(168, 85, 247, 0.2); color: #c084fc; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;">D-5 (마감임박)</span>
                        <span style="color: #94a3b8; font-size: 13px;">기간: 2026-06-20 ~ 2026-06-27</span>
                    </div>
                    
                    <h1 style="font-size: 32px; font-weight: 800; margin: 15px 0 10px 0;">
                        @if($id == 1) Comfortable Cotton T-Shirt (Premium Black)
                        @else Premium Full-Grain Leather Business Wallet
                        @endif
                    </h1>

                    <!-- Pricing Table -->
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px;">
                        <span style="text-decoration: line-through; color: #64748b; font-size: 16px;">
                            @if($id == 1) 29,000원 @else 89,000원 @endif
                        </span>
                        <span style="font-size: 30px; font-weight: 800; color: #a855f7;">
                            @if($id == 1) 15,000원 @else 54,000원 @endif
                        </span>
                        <span style="background: #ef4444; color: #fff; padding: 4px 8px; border-radius: 6px; font-size: 13px; font-weight: 700;">
                            @if($id == 1) 48% 할인 @else 39% 할인 @endif
                        </span>
                    </div>

                    <!-- Progress Gauge (Storyboard Slide 216 key feature) -->
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.05); padding: 25px; border-radius: 20px; margin-bottom: 25px;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <span style="font-weight: 700; color: #cbd5e1; font-size: 15px;">실시간 공동구매 모집 현황</span>
                            <span style="color: #a855f7; font-weight: 800; font-size: 18px;">
                                @if($id == 1) 85% 달성 @else 44% 달성 @endif
                            </span>
                        </div>
                        
                        <div style="background: #334155; height: 16px; border-radius: 10px; overflow: hidden; position: relative;">
                            <div style="background: linear-gradient(90deg, #6366f1, #a855f7); width: @if($id == 1) 85% @else 44% @endif; height: 100%; border-radius: 10px;"></div>
                        </div>

                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); text-align: center; margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.08); padding-top: 15px;">
                            <div>
                                <div style="font-size: 11px; color: #64748b; margin-bottom: 4px;">목표 최소 수량</div>
                                <div style="font-weight: 700; color: #fff; font-size: 15px;">@if($id == 1) 100 @else 50 @endif개</div>
                            </div>
                            <div style="border-left: 1px solid rgba(255,255,255,0.08); border-right: 1px solid rgba(255,255,255,0.08);">
                                <div style="font-size: 11px; color: #64748b; margin-bottom: 4px;">현재 참여 수량</div>
                                <div style="font-weight: 700; color: #a855f7; font-size: 15px;">@if($id == 1) 85 @else 22 @endif개</div>
                            </div>
                            <div>
                                <div style="font-size: 11px; color: #64748b; margin-bottom: 4px;">남은 목표 수량</div>
                                <div style="font-weight: 700; color: #ef4444; font-size: 15px;">@if($id == 1) 15 @else 28 @endif개</div>
                            </div>
                        </div>
                    </div>

                    <!-- Option Select & Quantity -->
                    <div style="background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255,255,255,0.05); padding: 20px; border-radius: 20px; margin-bottom: 30px; display: flex; flex-direction: column; gap: 15px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 700; color: #cbd5e1; margin-bottom: 8px;">옵션 선택</label>
                            <select id="option-select" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: #0f172a; color: #fff; font-size: 14px; outline: none;">
                                <option value="">선택해주세요</option>
                                <option value="black">Premium Black / M Size (+0원)</option>
                                <option value="white">Premium Black / L Size (+0원)</option>
                                <option value="custom">Special Edition Color (+2,000원)</option>
                            </select>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; font-weight: 700; color: #cbd5e1;">참여 수량</span>
                            <div style="display: flex; align-items: center; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; overflow: hidden; background: #0f172a;">
                                <button type="button" onclick="changeQty(-1)" style="background: none; border: none; color: #fff; width: 36px; height: 36px; font-size: 18px; cursor: pointer;">-</button>
                                <input type="text" id="qty-input" value="1" readonly style="width: 40px; text-align: center; border: none; background: none; color: #fff; font-weight: bold;">
                                <button type="button" onclick="changeQty(1)" style="background: none; border: none; color: #fff; width: 36px; height: 36px; font-size: 18px; cursor: pointer;">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Button -->
                <div>
                    <button type="button" onclick="participateJp()" style="width: 100%; padding: 18px; border-radius: 14px; border: none; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s; box-shadow: 0 10px 25px rgba(168, 85, 247, 0.4);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">공동구매 참여하기 (특가 결제)</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function changeQty(amount) {
    var input = $('#qty-input');
    var val = parseInt(input.val()) + amount;
    if (val >= 1) {
        input.val(val);
    }
}

function participateJp() {
    var option = $('#option-select').val();
    if(!option) {
        alert('공동구매 옵션을 선택해 주세요!');
        return;
    }
    var qty = $('#qty-input').val();
    alert('공동구매 참여 신청이 완료되었습니다! (목표 수량 달성 시 즉시 자동 발송 처리됩니다)');
    location.href = "{{ route('front.shop.cart.index') }}";
}
</script>
@endsection
