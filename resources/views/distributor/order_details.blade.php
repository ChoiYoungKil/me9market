<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Me9 Market - 발주 상세 관리</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <style>
        :root {
            --bg-color: #0b0f19;
            --sidebar-bg: #111827;
            --card-bg: rgba(17, 24, 39, 0.7);
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
            min-height: 100vh;
        }

        /* Sidebar Navigation */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border);
            padding: 30px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-sizing: border-box;
        }

        .sidebar-logo {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 800;
            background: var(--gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 40px;
        }

        .menu-list {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .menu-item a {
            display: flex;
            align-items: center;
            padding: 12px 16px;
            border-radius: 12px;
            color: var(--text-muted);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s;
        }

        .menu-item.active a, .menu-item a:hover {
            color: #fff;
            background: rgba(59, 130, 246, 0.15);
            border-left: 3px solid var(--primary);
        }

        /* Main Content Container */
        .main-content {
            flex: 1;
            padding: 40px;
            box-sizing: border-box;
            overflow-y: auto;
        }

        .header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            margin: 0;
            font-family: 'Outfit', sans-serif;
            font-size: 28px;
            font-weight: 700;
        }

        /* Details Card */
        .details-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 35px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            margin-bottom: 30px;
        }

        .details-section-title {
            font-size: 18px;
            font-weight: 700;
            border-bottom: 1px solid var(--border);
            padding-bottom: 10px;
            margin-bottom: 20px;
            color: #fff;
        }

        .grid-details {
            display: grid;
            grid-template-columns: 150px 1fr;
            row-gap: 15px;
            font-size: 14px;
            margin-bottom: 35px;
        }

        .detail-label {
            color: var(--text-muted);
            font-weight: 600;
        }

        .detail-value {
            color: #f3f4f6;
        }

        .form-control {
            width: 100%;
            max-width: 400px;
            padding: 10px 14px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(10, 15, 26, 0.6);
            color: #fff;
            font-size: 14px;
            outline: none;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: var(--primary);
        }

        .form-control.w150 {
            max-width: 150px;
        }

        .form-control.w250 {
            max-width: 250px;
        }

        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn:hover {
            background: var(--primary-hover);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--border);
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <div class="sidebar-logo">Partner Portal</div>
            <ul class="menu-list">
                <li class="menu-item {{ $order['can_edit_shipping'] ? 'active' : '' }}">
                    <a href="{{ route('distributor.orders.pending') }}">📦 발주 대기 목록</a>
                </li>
                <li class="menu-item {{ !$order['can_edit_shipping'] ? 'active' : '' }}">
                    <a href="{{ route('distributor.orders.completed') }}">✅ 발주 완료 목록</a>
                </li>
            </ul>
        </div>
        <div style="border-top: 1px solid var(--border); padding-top: 20px;">
            <div style="font-size: 12px; color: var(--text-muted); margin-bottom: 10px;">
                {{ Session::get('distributor_name', '공급처 파트너') }}
            </div>
            <a href="{{ route('distributor.logout') }}" style="color: #f87171; text-decoration: none; font-size: 13px; font-weight: 600;">로그아웃</a>
        </div>
    </div>

    <!-- Main -->
    <div class="main-content">
        <div class="header-bar">
            <h1 class="page-title">발주 상세 정보 관리</h1>
            <a href="{{ $order['can_edit_shipping'] ? route('distributor.orders.pending') : route('distributor.orders.completed') }}" class="btn btn-secondary">&larr; 목록으로 돌아가기</a>
        </div>

        @if(session('flash_message_success'))
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.25); color: #34d399; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 600;">
                {{ session('flash_message_success') }}
            </div>
        @endif
        @if(session('flash_message_error'))
            <div style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.25); color: #f87171; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 600;">
                {{ session('flash_message_error') }}
            </div>
        @endif

        <div class="details-card">
            <form action="{{ route('distributor.order.update', ['id' => $order['id']]) }}" method="POST">
                @csrf
                
                <!-- 주문 상품 정보 -->
                <div class="details-section-title">발주 상품 정보</div>
                <div class="grid-details">
                    <div class="detail-label">주문코드</div>
                    <div class="detail-value" style="font-weight: bold; color: #60a5fa;">{{ $order['order_id'] }}</div>

                    <div class="detail-label">채널명</div>
                    <div class="detail-value">{{ $order['channel_name'] }}</div>

                    <div class="detail-label">상품코드 / 상품명</div>
                    <div class="detail-value">[{{ $order['product_code'] }}] <strong>{{ $order['product_name'] }}</strong></div>

                    <div class="detail-label">옵션 / 수량</div>
                    <div class="detail-value">{{ $order['option'] }} / <strong>{{ $order['quantity'] }}개</strong></div>
                </div>

                <!-- 배송지 정보 수정 -->
                <div class="details-section-title">배송 정보 설정</div>
                <div class="grid-details">
                    <div class="detail-label">수령인</div>
                    <div class="detail-value">
                        <input type="text" name="receiver" value="{{ $order['receiver'] }}" class="form-control w250" required>
                    </div>

                    <div class="detail-label">우편번호</div>
                    <div class="detail-value">
                        <input type="text" name="zipcode" value="{{ $order['zipcode'] }}" class="form-control w150" required>
                    </div>

                    <div class="detail-label">배송지 주소</div>
                    <div class="detail-value">
                        <input type="text" name="address" value="{{ $order['address'] }}" class="form-control" required>
                    </div>

                    <div class="detail-label">발송 상태</div>
                    <div class="detail-value">
                        <span style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; padding: 4px 10px; border-radius: 6px; font-weight: bold; font-size: 13px;">
                            {{ $order['status'] }}
                        </span>
                    </div>

                    <div class="detail-label">택배사 선택</div>
                    <div class="detail-value">
                        <select name="courier" class="form-control w250">
                            <option value="">선택</option>
                            <option value="CJ대한통운" {{ $order['courier'] == 'CJ대한통운' ? 'selected' : '' }}>CJ대한통운</option>
                            <option value="우체국택배" {{ $order['courier'] == '우체국택배' ? 'selected' : '' }}>우체국택배</option>
                            <option value="한진택배" {{ $order['courier'] == '한진택배' ? 'selected' : '' }}>한진택배</option>
                            <option value="롯데택배" {{ $order['courier'] == '롯데택배' ? 'selected' : '' }}>롯데택배</option>
                        </select>
                    </div>

                    <div class="detail-label">운송장 번호</div>
                    <div class="detail-value">
                        <input type="text" name="tracking_no" value="{{ $order['tracking_no'] === '-' ? '' : $order['tracking_no'] }}" placeholder="숫자만 입력해 주세요" class="form-control w250">
                    </div>

                    <div class="detail-label">처리 상태</div>
                    <div class="detail-value">
                        <select name="status_code" class="form-control w250">
                            @foreach($order['status_options'] as $code => $label)
                                <option value="{{ $code }}" {{ $order['status_code'] === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Footer 버튼 -->
                <div style="border-top: 1px solid var(--border); padding-top: 25px; display: flex; justify-content: flex-end; gap: 10px;">
                    <button type="submit" class="btn">배송지 및 송장 정보 저장</button>
                    <a href="{{ $order['can_edit_shipping'] ? route('distributor.orders.pending') : route('distributor.orders.completed') }}" class="btn btn-secondary">닫기</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
