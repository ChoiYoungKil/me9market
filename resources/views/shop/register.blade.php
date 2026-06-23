@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
<div class="register-container" style="min-height: calc(100vh - 160px); display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.1) 0%, rgba(168, 85, 247, 0.1) 90%), #0f172a; padding: 40px 20px; font-family: 'Inter', sans-serif;">
    <div class="register-card" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 24px; padding: 40px; width: 100%; max-width: 550px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); color: #f8fafc; animation: fadeInUp 0.6s ease;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <div style="font-size: 28px; font-weight: 800; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-family: 'Outfit', sans-serif; display: inline-block;">Shop 간편 회원가입</div>
            <p style="color: #94a3b8; font-size: 14px; margin-top: 8px;">간편 가입 후 즉시 특가 혜택을 이용해 보세요.</p>
        </div>

        <form action="{{ route('shop.register.submit') }}" method="POST">
            @csrf
            
            <!-- 약관동의 -->
            <div style="background: rgba(15, 23, 42, 0.5); border: 1px solid rgba(255, 255, 255, 0.05); border-radius: 16px; padding: 20px; margin-bottom: 25px;">
                <div style="font-size: 14px; font-weight: 700; color: #cbd5e1; margin-bottom: 15px; display: flex; align-items: center; justify-content: space-between;">
                    <span>이용 약관 동의</span>
                    <label style="display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: normal; cursor: pointer;">
                        <input type="checkbox" id="check_all" style="width: 16px; height: 16px; accent-color: #6366f1;"> 전체 동의
                    </label>
                </div>
                
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: #94a3b8; cursor: pointer;">
                        <input type="checkbox" class="agree-chk" required style="width: 16px; height: 16px; margin-top: 2px; accent-color: #6366f1;">
                        <span>(필수) 쇼핑몰 이용약관 동의 <a href="#" style="color: #818cf8; margin-left: 5px; text-decoration: underline;">[보기]</a></span>
                    </label>
                    <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: #94a3b8; cursor: pointer;">
                        <input type="checkbox" class="agree-chk" required style="width: 16px; height: 16px; margin-top: 2px; accent-color: #6366f1;">
                        <span>(필수) 개인정보 수집 및 이용 동의 <a href="#" style="color: #818cf8; margin-left: 5px; text-decoration: underline;">[보기]</a></span>
                    </label>
                    <label style="display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: #94a3b8; cursor: pointer;">
                        <input type="checkbox" class="agree-chk" style="width: 16px; height: 16px; margin-top: 2px; accent-color: #6366f1;">
                        <span>(선택) 마케팅 정보 수신 동의 (SMS/이메일)</span>
                    </label>
                </div>
            </div>

            <!-- 입력 항목 -->
            <div style="display: flex; flex-direction: column; gap: 15px; margin-bottom: 30px;">
                <div>
                    <label for="name" style="display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">이름</label>
                    <input type="text" name="name" id="name" placeholder="실명을 입력하세요" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(15, 23, 42, 0.6); color: #fff; font-size: 14px; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label for="email" style="display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">이메일 주소</label>
                    <input type="email" name="email" id="email" placeholder="email@example.com" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(15, 23, 42, 0.6); color: #fff; font-size: 14px; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label for="phone" style="display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">휴대폰 번호</label>
                    <input type="tel" name="phone" id="phone" placeholder="010-0000-0000" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(15, 23, 42, 0.6); color: #fff; font-size: 14px; outline: none; box-sizing: border-box;">
                </div>
                <div>
                    <label for="password" style="display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 6px;">비밀번호</label>
                    <input type="password" name="password" id="password" placeholder="6자리 이상 비밀번호" required style="width: 100%; padding: 12px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(15, 23, 42, 0.6); color: #fff; font-size: 14px; outline: none; box-sizing: border-box;">
                </div>
            </div>

            <button type="submit" style="width: 100%; padding: 14px; border-radius: 12px; border: none; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);">
                회원가입 완료
            </button>
        </form>
    </div>
</div>

<script>
$(function(){
    $('#check_all').change(function(){
        $('.agree-chk').prop('checked', $(this).prop('checked'));
    });
    $('.agree-chk').change(function(){
        if($('.agree-chk:checked').length == $('.agree-chk').length) {
            $('#check_all').prop('checked', true);
        } else {
            $('#check_all').prop('checked', false);
        }
    });
});
</script>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.register-card input:focus {
    border-color: #6366f1 !important;
    box-shadow: 0 0 10px rgba(99, 102, 241, 0.3) !important;
}
.register-card button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.5) !important;
}
</style>
@endsection
