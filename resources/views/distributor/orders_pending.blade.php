<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Me9 Market - 발주대기 관리</title>
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

        /* Stat Grid */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .stat-title {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 600;
        }

        .stat-val {
            font-size: 28px;
            font-weight: 800;
            font-family: 'Outfit', sans-serif;
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
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
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

        .btn-secondary {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid var(--border);
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.15);
        }

        /* Overlay Modals (Required for Slide 240) */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(10, 15, 26, 0.85);
            backdrop-filter: blur(8px);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-card {
            background: #111827;
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 30px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .modal-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        .modal-close {
            background: none;
            border: none;
            color: var(--text-muted);
            font-size: 20px;
            cursor: pointer;
        }

        .modal-body {
            margin-bottom: 25px;
        }

        .modal-footer {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <div>
            <div class="sidebar-logo">Partner Portal</div>
            <ul class="menu-list">
                <li class="menu-item active">
                    <a href="{{ route('distributor.orders.pending') }}">📦 발주 대기 목록</a>
                </li>
                <li class="menu-item">
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
            <h1 class="page-title">발주 대기 관리</h1>
            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="openInvoiceModal()" class="btn">📂 일괄 송장 엑셀 등록</button>
            </div>
        </div>

        @if(session('flash_message_success'))
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.25); color: #34d399; padding: 15px; border-radius: 12px; margin-bottom: 25px; font-size: 14px; font-weight: 600;">
                {{ session('flash_message_success') }}
            </div>
        @endif

        <!-- Stat summaries -->
        <div class="stat-grid">
            <div class="stat-card">
                <span class="stat-title">배송 대기 (발주접수)</span>
                <span class="stat-val" style="color: #3b82f6;">2 건</span>
            </div>
            <div class="stat-card">
                <span class="stat-title">배송 중 (송장입력완료)</span>
                <span class="stat-val" style="color: #a855f7;">1 건</span>
            </div>
            <div class="stat-card">
                <span class="stat-title">배송 완료</span>
                <span class="stat-val" style="color: #10b981;">48 건</span>
            </div>
        </div>

        <!-- Orders Table -->
        <div class="actions-bar">
            <span style="font-weight: 700; font-size: 16px;">대기 목록 (총 {{ count($orders) }}건)</span>
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
                        <th>배송지</th>
                        <th>접수일시</th>
                        <th>상태</th>
                        <th>관리</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($orders as $order)
                        <tr>
                            <td style="font-weight: 700; color: #60a5fa;">{{ $order['order_id'] }}</td>
                            <td>{{ $order['channel_name'] }}</td>
                            <td style="font-family: monospace;">{{ $order['product_code'] }}</td>
                            <td style="font-weight: 600;">{{ $order['product_name'] }}</td>
                            <td>{{ $order['option'] }}</td>
                            <td style="text-align: center; font-weight: 700;">{{ $order['quantity'] }}</td>
                            <td>{{ $order['receiver'] }}</td>
                            <td style="max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $order['address'] }}">{{ $order['address'] }}</td>
                            <td style="color: var(--text-muted);">{{ $order['request_date'] }}</td>
                            <td>
                                <span class="badge-status">{{ $order['status'] }}</span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 5px;">
                                    <a href="{{ route('distributor.order.details', ['id' => $order['id']]) }}" class="btn">상세관리</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Upload Bulk Invoice Modal (Slide 240 key requirement) -->
    <div id="invoice-modal" class="modal">
        <div class="modal-card">
            <div class="modal-header">
                <h3 class="modal-title">일괄 송장 엑셀 파일 업로드</h3>
                <button type="button" onclick="closeInvoiceModal()" class="modal-close">&times;</button>
            </div>
            <form action="{{ route('distributor.orders.upload_invoice') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p style="color: var(--text-muted); font-size: 13px; line-height: 1.5; margin-bottom: 20px;">
                        발주 대기중인 주문 목록을 엑셀 폼에 맞게 편집하여 한 번에 운송장을 일괄 등록할 수 있습니다. 템플릿 양식을 확인해 주세요.
                    </p>
                    <div style="margin-bottom: 15px;">
                        <a href="#" style="color: #60a5fa; font-size: 13px; font-weight: 600; text-decoration: underline;">엑셀 업로드 템플릿 다운로드</a>
                    </div>
                    <div style="background: rgba(0,0,0,0.2); border: 1px dashed rgba(255,255,255,0.1); border-radius: 12px; padding: 25px; text-align: center; cursor: pointer;">
                        <input type="file" name="invoice_file" id="invoice_file" required style="display: none;" onchange="$('#file-selected-text').text(this.files[0].name)">
                        <label for="invoice_file" style="cursor: pointer;">
                            <div style="font-size: 32px; margin-bottom: 8px;">📊</div>
                            <div id="file-selected-text" style="font-size: 14px; font-weight: 600; color: #cbd5e1;">여기를 클릭하여 파일 선택</div>
                            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">CSV or XLSX 파일만 가능</div>
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closeInvoiceModal()" class="btn btn-secondary">취소</button>
                    <button type="submit" class="btn">송장 일괄 적용</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openInvoiceModal() {
            $('#invoice-modal').css('display', 'flex');
        }

        function closeInvoiceModal() {
            $('#invoice-modal').hide();
        }
    </script>
</body>
</html>
