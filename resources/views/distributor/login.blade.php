<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Me9 Market - 발주처 파트너 포털 로그인</title>
    <!-- Outfit & Inter Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(17, 24, 39, 0.75);
            --text-color: #f3f4f6;
            --text-muted: #9ca3af;
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --border: rgba(255, 255, 255, 0.06);
            --gradient: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background-image: 
                radial-gradient(at 0% 0%, rgba(59, 130, 246, 0.12) 0px, transparent 50%),
                radial-gradient(at 100% 100%, rgba(139, 92, 246, 0.12) 0px, transparent 50%);
        }

        .login-card {
            background: var(--card-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 440px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo {
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 800;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
            display: inline-block;
        }

        .subtitle {
            font-size: 14px;
            color: var(--text-muted);
            margin-bottom: 30px;
        }

        .form-group {
            text-align: left;
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #d1d5db;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background: rgba(10, 15, 26, 0.6);
            color: #fff;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
        }

        .btn-submit {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 12px;
            background: var(--gradient);
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 10px 15px -3px rgba(59, 130, 246, 0.3);
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 20px -3px rgba(59, 130, 246, 0.5);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.25);
            color: #f87171;
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: left;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.25);
            color: #34d399;
            padding: 12px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            text-align: left;
        }

        .hint {
            margin-top: 25px;
            font-size: 12px;
            color: var(--text-muted);
            border-top: 1px solid var(--border);
            padding-top: 15px;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="logo">Me9 Partner Portal</div>
        <div class="subtitle">발주처 전용 배송 및 송장관리 시스템</div>

        @if(session('flash_message_error'))
            <div class="alert-error">
                {{ session('flash_message_error') }}
            </div>
        @endif

        @if(session('flash_message_success'))
            <div class="alert-success">
                {{ session('flash_message_success') }}
            </div>
        @endif

        <form action="{{ route('distributor.login.submit') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="email">담당자 이메일</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="partner@domain.com" required value="{{ old('email', 'partner@main.com') }}">
            </div>
            <div class="form-group">
                <label for="password">비밀번호</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required value="123456">
            </div>

            <button type="submit" class="btn-submit">발주처 시스템 로그인</button>
        </form>

        <div class="hint">
            * 테스트 아이디는 아무 이메일/비밀번호나 입력해도 즉시 로그인 가능합니다.
        </div>
    </div>

</body>
</html>
