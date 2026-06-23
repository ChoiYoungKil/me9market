@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
<div class="shop-details-container" style="background: #0f172a; min-height: 100vh; color: #f8fafc; font-family: 'Inter', sans-serif; padding-bottom: 80px;">
    <!-- Shop Top Navigation -->
    <div style="background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(12px); border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding: 15px 40px; display: flex; justify-content: space-between; align-items: center; position: sticky; top: 0; z-index: 100;">
        <div style="font-size: 24px; font-weight: 800; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-family: 'Outfit', sans-serif;">
            <a href="{{ route('shop.channel_main') }}" style="text-decoration: none; color: inherit;">Me9 Shop</a>
        </div>
        <div style="display: flex; gap: 30px; font-weight: 600; font-size: 15px;">
            <a href="{{ route('shop.channel_main') }}" style="color: #cbd5e1; text-decoration: none;">홈</a>
            <a href="{{ route('shop.products_list') }}" style="color: #cbd5e1; text-decoration: none;">상품전체</a>
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

    <!-- Product Layout -->
    <div style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
        <div style="display: flex; flex-direction: row; gap: 50px; flex-wrap: wrap;">
            <!-- Left: Product Image -->
            <div style="flex: 1; min-width: 300px;">
                <div style="background: #1e293b; height: 500px; border-radius: 24px; display: flex; align-items: center; justify-content: center; font-size: 120px; border: 1px solid rgba(255, 255, 255, 0.08); box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
                    @if($id == 1) 🎧 @elseif($id == 2) ⌚ @elseif($id == 3) ⌨️ @else 💼 @endif
                </div>
            </div>

            <!-- Right: Product Purchase Area -->
            <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <span style="background: rgba(99, 102, 241, 0.2); color: #a5b4fc; padding: 6px 12px; border-radius: 8px; font-size: 12px; font-weight: 700;">추천 인기 상품</span>
                    <h1 style="font-size: 32px; font-weight: 800; margin: 15px 0 10px 0;">
                        @if($id == 1) Active Noise Cancelling Wireless Headphones
                        @elseif($id == 2) Smart Sports Watch (GPS + Heart Rate Tracker)
                        @elseif($id == 3) Mechanical Backlit Gaming Keyboard (Blue Switch)
                        @else Premium Full-Grain Leather Wallet
                        @endif
                    </h1>
                    
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px;">
                        <span style="text-decoration: line-through; color: #64748b; font-size: 16px;">
                            @if($id == 1) 149,000원 @elseif($id == 2) 229,000원 @elseif($id == 3) 79,000원 @else 89,000원 @endif
                        </span>
                        <span style="font-size: 28px; font-weight: 800; color: #6366f1;">
                            @if($id == 1) 99,000원 @elseif($id == 2) 159,000원 @elseif($id == 3) 49,000원 @else 59,000원 @endif
                        </span>
                        <span style="background: #ef4444; color: #fff; padding: 4px 8px; border-radius: 6px; font-size: 13px; font-weight: 700;">할인특가</span>
                    </div>

                    <p style="color: #94a3b8; font-size: 15px; line-height: 1.6; margin-bottom: 30px; border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 25px;">
                        최상의 퀄리티와 감각적인 디자인을 갖춘 프리미엄 셀렉션 제품입니다. 한정 수량 특가 혜택으로 만나보실 수 있습니다.
                    </p>

                    <!-- Options & Quantity Selector -->
                    <div style="display: flex; flex-direction: column; gap: 20px; background: rgba(30, 41, 59, 0.4); border: 1px solid rgba(255, 255, 255, 0.05); padding: 25px; border-radius: 20px; margin-bottom: 30px;">
                        <div>
                            <label style="display: block; font-size: 13px; font-weight: 700; color: #cbd5e1; margin-bottom: 8px;">옵션 선택</label>
                            <select id="option-select" style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.1); background: #0f172a; color: #fff; font-size: 14px; outline: none;">
                                <option value="">선택해주세요</option>
                                <option value="black">Space Gray / Matte Black (+0원)</option>
                                <option value="silver">Chrono Silver / Pearl White (+2,000원)</option>
                                <option value="gold">Nebula Gold / Luxury Bronze (+5,000원)</option>
                            </select>
                        </div>

                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 14px; font-weight: 700; color: #cbd5e1;">구매 수량</span>
                            <div style="display: flex; align-items: center; border: 1px solid rgba(255,255,255,0.1); border-radius: 8px; overflow: hidden; background: #0f172a;">
                                <button type="button" onclick="changeQty(-1)" style="background: none; border: none; color: #fff; width: 36px; height: 36px; font-size: 18px; cursor: pointer;">-</button>
                                <input type="text" id="qty-input" value="1" readonly style="width: 40px; text-align: center; border: none; background: none; color: #fff; font-weight: bold;">
                                <button type="button" onclick="changeQty(1)" style="background: none; border: none; color: #fff; width: 36px; height: 36px; font-size: 18px; cursor: pointer;">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div style="display: flex; gap: 15px;">
                    <button type="button" onclick="addToCart()" style="flex: 1; padding: 16px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.1); background: rgba(255, 255, 255, 0.05); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.1)'" onmouseout="this.style.background='rgba(255,255,255,0.05)'">장바구니 담기</button>
                    <button type="button" onclick="buyNow()" style="flex: 2; padding: 16px; border-radius: 14px; border: none; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">바로 구매하기</button>
                </div>
            </div>
        </div>

        <!-- Info / Q&A / Notice Tabs -->
        <div style="margin-top: 80px;">
            <div style="display: flex; border-bottom: 1px solid rgba(255, 255, 255, 0.08); margin-bottom: 30px;">
                <button class="tab-btn active" onclick="switchDetailTab('info')" style="background: none; border: none; padding: 15px 30px; font-size: 16px; font-weight: 700; color: #6366f1; border-bottom: 2px solid #6366f1; cursor: pointer; transition: all 0.3s;">상세정보</button>
                <button class="tab-btn" onclick="switchDetailTab('qa')" style="background: none; border: none; padding: 15px 30px; font-size: 16px; font-weight: 700; color: #94a3b8; cursor: pointer; transition: all 0.3s;">상품문의 (Q&A)</button>
                <button class="tab-btn" onclick="switchDetailTab('policy')" style="background: none; border: none; padding: 15px 30px; font-size: 16px; font-weight: 700; color: #94a3b8; cursor: pointer; transition: all 0.3s;">배송/교환/반품 안내</button>
            </div>

            <!-- Tab Contents -->
            <div id="tab-info" class="tab-content" style="padding: 20px; line-height: 1.8; color: #cbd5e1;">
                <p>본 상품은 공식 공급사를 통하여 정품 수입/공급되는 제품입니다. 뛰어난 내구성과 최적화된 성능을 선사합니다.</p>
                <div style="margin-top: 30px; border-radius: 16px; background: rgba(30, 41, 59, 0.3); padding: 30px; border: 1px solid rgba(255, 255, 255, 0.05);">
                    <h3 style="color: #fff; margin-top: 0;">제품 상세 고시 스펙</h3>
                    <ul style="list-style: square; padding-left: 20px; display: flex; flex-direction: column; gap: 8px;">
                        <li>제조사: 주식회사 메인공급처 (Distributor A)</li>
                        <li>소재/품명: 상세설명 참조</li>
                        <li>A/S 보증기간: 구입 후 1년 무상 제공</li>
                    </ul>
                </div>
            </div>

            <div id="tab-qa" class="tab-content" style="display: none; padding: 20px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <div style="font-weight: 700;">상품 문의 총 2건</div>
                    <button style="background: #6366f1; border: none; color: #fff; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; font-weight: 600;">문의하기</button>
                </div>
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    <div style="background: rgba(30, 41, 59, 0.3); border: 1px solid rgba(255,255,255,0.05); padding: 20px; border-radius: 12px;">
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: #64748b; margin-bottom: 8px;">
                            <span>답변완료 | 김*철</span>
                            <span>2026-06-15</span>
                        </div>
                        <div style="font-weight: 600; color: #cbd5e1; margin-bottom: 10px;">Q. 배송은 보통 며칠 걸리나요?</div>
                        <div style="color: #94a3b8; font-size: 14px; padding-left: 15px; border-left: 2px solid #6366f1;">A. 영업일 기준 보통 2~3일 이내 출고 완료되어 받아보실 수 있습니다.</div>
                    </div>
                </div>
            </div>

            <div id="tab-policy" class="tab-content" style="display: none; padding: 20px; line-height: 1.8; color: #cbd5e1;">
                <h3 style="color: #fff; margin-top: 0;">배송정보</h3>
                <p>- 기본 배송료는 3,000원입니다. (5만원 이상 무료배송)<br>- 도서 산간 지역은 별도의 추가금액이 발생할 수 있습니다.</p>
                <h3 style="color: #fff; margin-top: 20px;">교환/반품 안내</h3>
                <p>- 반품/교환은 상품 수령 후 7일 이내에 신청 가능합니다.<br>- 고객 변심에 의한 반품/교환의 경우 왕복 배송비는 고객 부담입니다.</p>
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

function switchDetailTab(tabName) {
    $('.tab-content').hide();
    $('#tab-' + tabName).show();
    
    // Set active tab button style
    $('.tab-btn').css({
        'color': '#94a3b8',
        'border-bottom': 'none'
    }).removeClass('active');
    
    event.target.style.color = '#6366f1';
    event.target.style.borderBottom = '2px solid #6366f1';
}

function addToCart() {
    var option = $('#option-select').val();
    if(!option) {
        alert('필수 옵션을 선택해 주세요!');
        return;
    }
    var qty = $('#qty-input').val();
    alert('장바구니에 상품 ' + qty + '개가 성공적으로 담겼습니다!');
}

function buyNow() {
    var option = $('#option-select').val();
    if(!option) {
        alert('필수 옵션을 선택해 주세요!');
        return;
    }
    // Redirect to cart/order page or mock checkout
    location.href = "{{ route('front.shop.cart.index') }}";
}
</script>
@endsection
