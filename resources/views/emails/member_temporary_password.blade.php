<!DOCTYPE html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Me9 Market 임시비밀번호</title></head>
<body style="margin:0;background:#f7f3fc;color:#483354;font-family:Arial,'Apple SD Gothic Neo',sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0"><tr><td style="padding:45px 20px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;margin:auto;background:#fff;border:1px solid #e4d9ef;border-radius:8px;"><tr><td style="padding:38px 32px;">
            <img src="{{ $logoUrl }}" width="189" height="45" alt="Me9 Market" style="display:block;margin-bottom:35px;">
            <h1 style="font-size:24px;font-weight:600;line-height:1.6;margin:0 0 20px;">Me9 Market에서<br>임시비밀번호가 발급되었습니다.</h1>
            <p style="font-size:14px;line-height:1.9;color:#9781a7;margin:0 0 25px;">아래 임시비밀번호로 로그인해 주세요.</p>
            <div style="background:#f3edf9;border:1px solid #e1d3ee;border-radius:5px;text-align:center;padding:25px 15px;margin-bottom:22px;"><p style="font-size:13px;color:#9b81ad;margin:0 0 13px;">임시비밀번호</p><strong data-temporary-password style="font:600 23px/1.5 monospace;letter-spacing:1px;color:#7551e9;overflow-wrap:anywhere;">{{ $temporaryPassword }}</strong></div>
            <p style="font-size:13px;color:#927ba4;line-height:1.9;margin:0 0 28px;">유효시간: {{ $expiresAt }}까지 (발급 후 60분)<br>로그인 후 회원정보에서 새 비밀번호로 변경해 주세요.</p>
            <a data-mail-login href="{{ $loginUrl }}" target="_blank" rel="noopener noreferrer" style="display:block;background:#7551e9;border-radius:4px;padding:16px 20px;text-align:center;color:#fff;text-decoration:none;font-size:15px;">Me9 market 로그인 바로가기 ↗</a>
        </td></tr></table>
        <p style="max-width:560px;margin:22px auto 0;font-size:12px;color:#b39fc0;line-height:1.8;text-align:center;">© Me9 Market</p>
    </td></tr></table>
</body></html>
