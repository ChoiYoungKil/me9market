@extends('layouts.shop')

@section('page_type', 'main')

@section('content')
<div class="gate-container" style="min-height: calc(100vh - 160px); display: flex; align-items: center; justify-content: center; background: radial-gradient(circle at 10% 20%, rgba(99, 102, 241, 0.15) 0%, rgba(168, 85, 247, 0.15) 90%), #0f172a; padding: 20px; font-family: 'Inter', sans-serif;">
    <div class="gate-card" style="background: rgba(30, 41, 59, 0.7); backdrop-filter: blur(20px); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 24px; padding: 40px 30px; width: 100%; max-width: 450px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); text-align: center; color: #f8fafc; animation: fadeInUp 0.6s ease;">
        <div class="logo-area" style="margin-bottom: 30px;">
            <div style="font-size: 32px; font-weight: 800; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-family: 'Outfit', sans-serif; display: inline-block;">Me9 Shop Channel</div>
            <p style="color: #94a3b8; font-size: 14px; margin-top: 10px;">본 쇼핑몰은 승인된 멤버만 접속 가능한 폐쇄형 분양몰입니다.</p>
        </div>

        @if(session('flash_message_error'))
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; padding: 12px; border-radius: 12px; margin-bottom: 20px; font-size: 13px; text-align: left;">
                {{ session('flash_message_error') }}
            </div>
        @endif

        <form action="{{ route('shop.gate.submit') }}" method="POST">
            @csrf
            <div style="margin-bottom: 20px; text-align: left;">
                <label for="entry_code" style="display: block; font-size: 13px; font-weight: 600; color: #cbd5e1; margin-bottom: 8px;">입장 코드 (비밀번호)</label>
                <input type="password" name="entry_code" id="entry_code" placeholder="발급받은 입장 코드를 입력하세요" required style="width: 100%; padding: 12px 16px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(15, 23, 42, 0.6); color: #fff; font-size: 15px; outline: none; box-sizing: border-box; transition: all 0.3s;">
            </div>

            <button type="submit" style="width: 100%; padding: 14px; border-radius: 12px; border: none; background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; transition: all 0.3s; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.3);">
                인증 및 상점 입장
            </button>
        </form>

        <div style="margin-top: 25px; padding-top: 20px; border-top: 1px solid rgba(255, 255, 255, 0.08); font-size: 12px; color: #64748b;">
            <span style="background: rgba(99, 102, 241, 0.2); color: #a5b4fc; padding: 3px 8px; border-radius: 6px; font-weight: bold; margin-right: 5px;">테스트 안내</span>
            입장 코드는 <strong style="color: #cbd5e1; text-decoration: underline;">me9</strong> 입니다.
        </div>
    </div>
</div>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}
.gate-card input:focus {
    border-color: #6366f1 !important;
    box-shadow: 0 0 10px rgba(99, 102, 241, 0.3) !important;
}
.gate-card button:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(99, 102, 241, 0.5) !important;
}
</style>
@endsection
