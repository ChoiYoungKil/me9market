<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Me9 Market - 발주완료 목록</title>
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

        /* Actions Bar */
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        /* Table CSS */
        .table-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th, td {
            padding: 16px;
            border-bottom: 1px solid var(--border);
            font-size: 13px;
        }

        th {
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.05em;
            background: rgba(255, 255, 255, 0.02);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.01);
        }

        .badge-status {
            background: rgba(16, 185, 129, 0.15);
            color: #10b981;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }

        .btn {
            background: var(--primary);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12px;
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
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <div class="sidebar-logo">Partner Portal</div>
            <ul class="menu-list">
                <li class="menu-item">
                    <a href="{{ route('distributor.orders.pending') }}">📦 발주 대기 목록</a>
                </li>
                <li class="menu-item active">
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
            <h1 class="page-title">발주 완료 목록</h1>
        </div>

        @if(session('flash_message_success'))
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.25); color: #34d399; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 600;">
                {{ session('flash_message_success') }}
            </div>
        @endif

        <div class="actions-bar">
            <span style="font-weight: 700; font-size: 16px;">완료 목록 (총 {{ count($orders) }}건)</span>
        </div>

        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>주문코드</th>
                        <th>채널</th>
                        <th>상품코드</th>
                        <th>상품명</th>
                        <th>옵션</th>
                        <th>수량</th>
                        <th>수령인</th>
                        <th>택배사</th>
                        <th>송장번호</th>
                        <th>발송일시</th>
                        <th>상태</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td style="font-weight: 700; color: #60a5fa;">{{ $order['order_id'] }}</td>
                            <td>{{ $order['channel_name'] }}</td>
                            <td style="font-family: monospace;">{{ $order['product_code'] }}</td>
                            <td style="font-weight: 600;">{{ $order['product_name'] }}</td>
                            <td>{{ $order['option'] }}</td>
                            <td style="text-align: center; font-weight: 700;">{{ $order['quantity'] }}</td>
                            <td>{{ $order['receiver'] }}</td>
                            <td>{{ $order['courier'] }}</td>
                            <td style="font-family: monospace; font-weight: 600; color: #34d399;">{{ $order['tracking_no'] }}</td>
                            <td style="color: var(--text-muted);">{{ $order['shipped_date'] }}</td>
                            <td>
                                <span class="badge-status">{{ $order['status'] }}</span>
                            </td>
                            <td>
                                <a href="{{ route('distributor.order.details', ['id' => $order['id']]) }}" class="btn">상세관리</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" style="text-align: center; color: var(--text-muted); padding: 40px;">발주 완료 주문상품이 없습니다.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>
