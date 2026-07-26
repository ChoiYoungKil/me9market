@extends('layouts.frontend')

@section('page_type', 'sub')

@section('content')
<style>
    .social-join-input {
        width: 100%;
        padding: 12px;
        border: 1px solid #cbd5e1;
        border-radius: 8px;
        font-size: 14px;
        box-sizing: border-box;
    }

    .social-join-input[readonly] {
        background: #f8fafc;
        color: #64748b;
    }
</style>
<div id="container">
    <div id="contents">
        <div class="row" style="padding: 60px 0;">
            <div class="box box1" style="max-width: 500px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.05);">
                <div style="text-align: center; margin-bottom: 30px;">
                    <div style="background: #10b981; color: white; width: 48px; height: 48px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; margin-bottom: 15px;">✓</div>
                    <h2 style="font-size: 24px; font-weight: 700; color: #111; margin: 0 0 10px 0;">간편 회원 가입 완료</h2>
                    <p style="font-size: 14px; color: #666; margin: 0;">소셜 인증이 완료되었습니다. 서비스 이용 약관에 동의하고 추가 정보를 보완해주세요.</p>
                </div>

                <form action="{{ route('front.member.social_join.submit') }}" method="POST">
                    @csrf
                    <div style="margin-bottom: 20px;">
                        <label for="email" style="display: block; font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px;">연동된 이메일</label>
                        <input type="text" id="email" name="email" value="kakao_user123@kakao.com" readonly class="social-join-input">
                    </div>

                    <div style="margin-bottom: 20px;">
                        <label for="name" style="display: block; font-size: 14px; font-weight: 600; color: #333; margin-bottom: 8px;">이름 (성명)</label>
                        <input type="text" id="name" name="name" placeholder="실명을 입력해 주세요" required class="social-join-input">
                    </div>

                    <div style="margin-bottom: 30px; border-top: 1px solid #f1f5f9; padding-top: 20px;">
                        <h4 style="font-size: 15px; font-weight: 700; color: #111; margin: 0 0 15px 0;">약관 동의</h4>
                        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 12px;">
                            <li style="display: flex; align-items: flex-start; gap: 10px;">
                                <input type="checkbox" id="accept_all" onchange="toggleAllTerms(this)" style="margin-top: 3px; cursor: pointer;">
                                <label for="accept_all" style="font-size: 14px; font-weight: 600; color: #111; cursor: pointer;">전체 약관에 동의합니다.</label>
                            </li>
                            <li style="display: flex; align-items: flex-start; gap: 10px; margin-left: 20px;">
                                <input type="checkbox" name="accept_terms" id="term1" required class="term-chk" style="margin-top: 3px; cursor: pointer;">
                                <label for="term1" style="font-size: 13px; color: #555; cursor: pointer;">이용약관 동의 (필수)</label>
                            </li>
                            <li style="display: flex; align-items: flex-start; gap: 10px; margin-left: 20px;">
                                <input type="checkbox" name="accept_privacy" id="term2" required class="term-chk" style="margin-top: 3px; cursor: pointer;">
                                <label for="term2" style="font-size: 13px; color: #555; cursor: pointer;">개인정보 수집 및 이용 동의 (필수)</label>
                            </li>
                        </ul>
                    </div>

                    <button type="submit" style="width: 100%; background: #6366f1; color: white; padding: 14px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: background 0.2s;">보완가입 완료</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleAllTerms(master) {
        document.querySelectorAll('.term-chk').forEach(c => c.checked = master.checked);
    }
</script>
@endsection
