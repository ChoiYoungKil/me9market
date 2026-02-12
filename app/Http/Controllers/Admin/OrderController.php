<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use App\Models\User;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\OrdersLog;
use App\Models\OrderStatus;
use App\Models\OrderItemStatus;


class OrderController extends Controller
{
    // 참고: 관리자 패널의 주문 관리 섹션에서, 로그인한 사용자가 '판매자(vendor)'인 경우 해당 판매자가 등록한 상품과 관련된 주문만 표시하며, '관리자(admin)'인 경우 모든 주문을 표시함    



    // 관리자 패널의 주문 관리 섹션(admin/orders/orders.blade.php) 렌더링    
    public function orders() {
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'orders');


        // 로그인한 사용자를 확인하여 판매자인 경우 해당 판매자의 주문만, 관리자인 경우 전체 주문을 가져옴
        $adminType = Auth::guard('admin')->user()->type;      
        $vendor_id = Auth::guard('admin')->user()->vendor_id; 


        if ($adminType == 'vendor') { // 로그인한 사용자가 판매자인 경우 상태 확인
            $vendorStatus = Auth::guard('admin')->user()->status; 

            if ($vendorStatus == 0) { // 판매자가 비활성 상태인 경우
                return redirect('admin/update-vendor-details/personal')->with('error_message', '판매자 계정이 아직 승인되지 않았습니다. 개인, 사업자, 은행 정보를 모두 정확히 입력해 주세요.'); 
            }
        }


        if ($adminType == 'vendor') { // 판매자인 경우 해당 판매자의 상품이 포함된 주문만 표시
            $orders = Order::with([ 
                'orders_products' => function($query) use ($vendor_id) { 
                    $query->where('vendor_id', $vendor_id); 
                }
            ])->orderBy('id', 'Desc')->get()->toArray();

        } else { // 관리자인 경우 모든 주문 표시
            $orders = Order::with('orders_products')->orderBy('id', 'Desc')->get()->toArray(); 
        }


        return view('admin.orders.orders')->with(compact('orders'));
    }

    // 주문 상세 보기 페이지(admin/orders/order_details.blade.php) 렌더링
    public function orderDetails($id) {
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'orders');

        
        // 로그인한 사용자가 판매자인 경우 해당 판매자의 상품 상세 내역만, 관리자인 경우 전체 상세 내역을 표시
        $adminType = Auth::guard('admin')->user()->type;      
        $vendor_id = Auth::guard('admin')->user()->vendor_id; 
        
        if ($adminType == 'vendor') { 
            $vendorStatus = Auth::guard('admin')->user()->status; 

            if ($vendorStatus == 0) { 
                return redirect('admin/update-vendor-details/personal')->with('error_message', '판매자 계정이 아직 승인되지 않았습니다. 개인, 사업자, 은행 정보를 모두 정확히 입력해 주세요.'); 
            }
        }


        
        if ($adminType == 'vendor') { // 판매자인 경우 해당 판매자의 상품 주문 내역만 표시
            $orderDetails = Order::with([ 
                'orders_products' => function($query) use ($vendor_id) { 
                    $query->where('vendor_id', $vendor_id); 
                }
            ])->where('id', $id)->first()->toArray(); 

        } else { // 관리자인 경우 모든 주문 상세 내역 표시
            $orderDetails = Order::with('orders_products')->where('id', $id)->first()->toArray(); 
        }


        $userDetails = User::where('id', $orderDetails['user_id'])->first()->toArray();

        // `order_statuses` 테이블에서 활성화된 주문 상태 가져오기 (관리자만 변경 가능)
        $orderStatuses = OrderStatus::where('status', 1)->get()->toArray();

        // `order_item_statuses` 테이블에서 활성화된 개별 품목 상태 가져오기 (판매자 및 관리자 모두 변경 가능)
        $orderItemStatuses = OrderItemStatus::where('status', 1)->get()->toArray();

        // 주문 상태 변경 이력(로그) 가져오기
        $orderLog = OrdersLog::with('orders_products')->where('order_id', $id)->orderBy('id', 'Desc')->get()->toArray(); 


        
        // 장바구니 내 전체 품목 수량 계산
        $total_items = 0;

        foreach ($orderDetails['orders_products'] as $product) {
            $total_items = $total_items + $product['product_qty'];
        }

        // 품목 할인액 계산 (쿠폰 사용 시)
        if ($orderDetails['coupon_amount'] > 0) { 
            $item_discount = round($orderDetails['coupon_amount'] / $total_items, 2); 
        } else {
            $item_discount = 0;
        }


        return view('admin.orders.order_details')->with(compact('orderDetails', 'userDetails', 'orderStatuses', 'orderItemStatuses', 'orderLog', 'item_discount'));
    }

    // 주문 상태 업데이트 (관리자 전용: 대기, 배송 중, 진행 중, 취소 등)
    public function updateOrderStatus(Request $request) {
        if ($request->isMethod('post')) {
            $data = $request->all();
            // dd($data);

            // 자동 배송 처리 (관리자가 택배사 및 운송장 번호를 입력하지 않고 "배송 중"을 선택한 경우):
            // Shiprocket API 등을 통한 자동 배송 프로세스 실행
            if (empty($data['courier_name']) && empty($data['tracking_number']) && $data['order_status'] == 'Shipped') { 
                $getResults = Order::pushOrder($data['order_id']);
                if (!isset($getResults['status']) || (isset($getResults['status']) && $getResults['status'] == false)) { 
                    Session::put('error_message', $getResults['message']); 

                    return redirect()->back(); 
                }
            }


            // `orders` 테이블의 주문 상태 업데이트
            Order::where('id', $data['order_id'])->update(['order_status' => $data['order_status']]);


            // 수동 배송 처리 (관리자가 택배사 및 운송장 번호를 직접 입력한 경우)
            if (!empty($data['courier_name']) && !empty($data['tracking_number'])) { 
                Order::where('id', $data['order_id'])->update([
                    'courier_name'    => $data['courier_name'],
                    'tracking_number' => $data['tracking_number']
                ]);
            }


            // 주문 상태 변경 이력(로그)을 `orders_logs` 테이블에 기록
            $log = new OrdersLog;
            $log->order_id     = $data['order_id'];
            $log->order_status = $data['order_status'];
            $log->save();


            // 주문 상태 업데이트 알림 이메일 발송
            $deliveryDetails = Order::select('mobile', 'email', 'name')->where('id', $data['order_id'])->first()->toArray();
            $orderDetails    = Order::with('orders_products')->where('id', $data['order_id'])->first()->toArray(); 

            
            if (!empty($data['courier_name']) && !empty($data['tracking_number'])) { // 택배사 및 운송장 번호가 있는 경우 이메일에 포함
                $email = $deliveryDetails['email'];

                $messageData = [
                    'email'           => $email,
                    'name'            => $deliveryDetails['name'],
                    'order_id'        => $data['order_id'],
                    'orderDetails'    => $orderDetails,
                    'order_status'    => $data['order_status'],
                    'courier_name'    => $data['courier_name'],
                    'tracking_number' => $data['tracking_number']
                ];

                \Illuminate\Support\Facades\Mail::send('emails.order_status', $messageData, function ($message) use ($email) { 
                    $message->to($email)->subject('주문 상태가 업데이트되었습니다 - me9market.com');
                });

            } else { // 택배사 정보가 없는 경우
                $email = $deliveryDetails['email'];

                $messageData = [
                    'email'        => $email,
                    'name'         => $deliveryDetails['name'],
                    'order_id'     => $data['order_id'],
                    'orderDetails' => $orderDetails,
                    'order_status' => $data['order_status']
                ];
    
                \Illuminate\Support\Facades\Mail::send('emails.order_status', $messageData, function ($message) use ($email) { 
                    $message->to($email)->subject('주문 상태가 업데이트되었습니다 - me9market.com');
                });
            }

            $message = '주문 상태가 성공적으로 업데이트되었습니다!';


            return redirect()->back()->with('success_message', $message);
        }
    }

    // 개별 품목 상태 업데이트 (판매자 및 관리자 모두 가능)
    public function updateOrderItemStatus(Request $request) {
        if ($request->isMethod('post')) {
            $data = $request->all();
            // dd($data);

            // `orders_products` 테이블의 품목 상태 업데이트
            OrdersProduct::where('id', $data['order_item_id'])->update(['item_status' => $data['order_item_status']]);


            // 수동 배송 처리 (판매자 또는 관리자가 품목별 택배사 및 운송장 번호를 직접 입력한 경우)
            if (!empty($data['item_courier_name']) && !empty($data['item_tracking_number'])) { 
                OrdersProduct::where('id', $data['order_item_id'])->update([
                    'courier_name'    => $data['item_courier_name'],
                    'tracking_number' => $data['item_tracking_number']
                ]);
            }


            // `orders_products` 테이블에서 `order_id` 컬럼(`orders` 테이블의 `id` 컬럼에 대한 외래 키) 값을 가져옵니다.
            $getOrderId = OrdersProduct::select('order_id')->where('id', $data['order_item_id'])->first()->toArray();


            // 품목 상태 변경 이력을 `orders_logs` 테이블에 기록
            $log = new OrdersLog;
            $log->order_id      = $getOrderId['order_id'];
            $log->order_item_id = $data['order_item_id'];
            $log->order_status  = $data['order_item_status'];
            $log->save();


            // 품목 상태 업데이트 알림 이메일 발송
            $deliveryDetails = Order::select('mobile', 'email', 'name')->where('id', $getOrderId['order_id'])->first()->toArray();

            // 상태가 업데이트된 특정 품목만 이메일에 포함되도록 설정
            $order_item_id = $data['order_item_id'];
            $orderDetails  = Order::with([ 
                'orders_products' => function($query) use ($order_item_id) { 
                    $query->where('id', $order_item_id); 
                }
            ])->where('id', $getOrderId['order_id'])->first()->toArray(); 


            if (!empty($data['item_courier_name']) && !empty($data['item_tracking_number'])) { // 택배사 정보가 있는 경우
                $email = $deliveryDetails['email'];

                $messageData = [
                    'email'           => $email,
                    'name'            => $deliveryDetails['name'],
                    'order_id'        => $getOrderId['order_id'],
                    'orderDetails'    => $orderDetails,
                    'order_status'    => $data['order_item_status'],
                    'courier_name'    => $data['item_courier_name'],
                    'tracking_number' => $data['item_tracking_number']
                ];

                \Illuminate\Support\Facades\Mail::send('emails.order_item_status', $messageData, function ($message) use ($email) { 
                    $message->to($email)->subject('주문 품목 상태가 업데이트되었습니다 - me9market.com');
                });

            } else { // 택배사 정보가 없는 경우
                $email = $deliveryDetails['email'];

                $messageData = [
                    'email'        => $email,
                    'name'         => $deliveryDetails['name'],
                    'order_id'     => $getOrderId['order_id'],
                    'orderDetails' => $orderDetails,
                    'order_status' => $data['order_item_status']
                ];
    
                \Illuminate\Support\Facades\Mail::send('emails.order_item_status', $messageData, function ($message) use ($email) { 
                    $message->to($email)->subject('주문 품목 상태가 업데이트되었습니다 - me9market.com');
                });
            }

            $message = '주문 품목 상태가 성공적으로 업데이트되었습니다!';


            return redirect()->back()->with('success_message', $message);
        }
    }

    // 주문 인보이스 페이지(HTML) 렌더링
    public function viewOrderInvoice($order_id) { 
        $orderDetails = Order::with('orders_products')->where('id', $order_id)->first()->toArray(); 
        $userDetails = User::where('id', $orderDetails['user_id'])->first()->toArray(); // 주문을 한 사용자 정보


        return view('admin.orders.order_invoice')->with(compact('orderDetails', 'userDetails'));
    }

    // Dompdf 패키지를 사용하여 주문 PDF 인보이스 생성
    public function viewPDFInvoice($order_id) { 
        $orderDetails = Order::with('orders_products')->where('id', $order_id)->first()->toArray(); 
        $userDetails = User::where('id', $orderDetails['user_id'])->first()->toArray(); 


        // 모든 오류를 방지하기 위해 작은따옴표를 제거합니다.
        $invoiceHTML = '
            <!DOCTYPE html>
            <html>
            <head>
                <title>HTML to API - Invoice</title>
                <meta content="width=device-width, initial-scale=1.0" name="viewport">
                <meta http-equiv="content-type" content="text-html; charset=utf-8">
                <style type="text/css">
                    html, body, div, span, applet, object, iframe,
                    h1, h2, h3, h4, h5, h6, p, blockquote, pre,
                    a, abbr, acronym, address, big, cite, code,
                    del, dfn, em, img, ins, kbd, q, s, samp,
                    small, strike, strong, sub, sup, tt, var,
                    b, u, i, center,
                    dl, dt, dd, ol, ul, li,
                    fieldset, form, label, legend,
                    table, caption, tbody, tfoot, thead, tr, th, td,
                    article, aside, canvas, details, embed,
                    figure, figcaption, footer, header, hgroup,
                    menu, nav, output, ruby, section, summary,
                    time, mark, audio, video {
                        margin: 0;
                        padding: 0;
                        border: 0;
                        font: inherit;
                        font-size: 100%;
                        vertical-align: baseline;
                    }
            
                    html {
                        line-height: 1;
                    }
            
                    ol, ul {
                        list-style: none;
                    }
            
                    table {
                        border-collapse: collapse;
                        border-spacing: 0;
                    }
            
                    caption, th, td {
                        text-align: left;
                        font-weight: normal;
                        vertical-align: middle;
                    }
            
                    q, blockquote {
                        quotes: none;
                    }
                    q:before, q:after, blockquote:before, blockquote:after {
                        content: "";
                        content: none;
                    }
            
                    a img {
                        border: none;
                    }
            
                    article, aside, details, figcaption, figure, footer, header, hgroup, main, menu, nav, section, summary {
                        display: block;
                    }
            
                    body {
                        font-family: "Source Sans Pro", sans-serif;
                        font-weight: 300;
                        font-size: 12px;
                        margin: 0;
                        padding: 0;
                    }
                    body a {
                        text-decoration: none;
                        color: inherit;
                    }
                    body a:hover {
                        color: inherit;
                        opacity: 0.7;
                    }
                    body .container {
                        min-width: 500px;
                        margin: 0 auto;
                        padding: 0 20px;
                    }
                    body .clearfix:after {
                        content: "";
                        display: table;
                        clear: both;
                    }
                    body .left {
                        float: left;
                    }
                    body .right {
                        float: right;
                    }
                    body .helper {
                        display: inline-block;
                        height: 100%;
                        vertical-align: middle;
                    }
                    body .no-break {
                        page-break-inside: avoid;
                    }
            
                    header {
                        margin-top: 20px;
                        margin-bottom: 50px;
                    }
                    header figure {
                        float: left;
                        width: 60px;
                        height: 60px;
                        margin-right: 10px;
                        background-color: #8BC34A;
                        border-radius: 50%;
                        text-align: center;
                    }
                    header figure img {
                        margin-top: 13px;
                    }
                    header .company-address {
                        float: left;
                        max-width: 150px;
                        line-height: 1.7em;
                    }
                    header .company-address .title {
                        color: #8BC34A;
                        font-weight: 400;
                        font-size: 1.5em;
                        text-transform: uppercase;
                    }
                    header .company-contact {
                        float: right;
                        height: 60px;
                        padding: 0 10px;
                        background-color: #8BC34A;
                        color: white;
                    }
                    header .company-contact span {
                        display: inline-block;
                        vertical-align: middle;
                    }
                    header .company-contact .circle {
                        width: 20px;
                        height: 20px;
                        background-color: white;
                        border-radius: 50%;
                        text-align: center;
                    }
                    header .company-contact .circle img {
                        vertical-align: middle;
                    }
                    header .company-contact .phone {
                        height: 100%;
                        margin-right: 20px;
                    }
                    header .company-contact .email {
                        height: 100%;
                        min-width: 100px;
                        text-align: right;
                    }
            
                    section .details {
                        margin-bottom: 55px;
                    }
                    section .details .client {
                        width: 50%;
                        line-height: 20px;
                    }
                    section .details .client .name {
                        color: #8BC34A;
                    }
                    section .details .data {
                        width: 50%;
                        text-align: right;
                    }
                    section .details .title {
                        margin-bottom: 15px;
                        color: #8BC34A;
                        font-size: 3em;
                        font-weight: 400;
                        text-transform: uppercase;
                    }
                    section table {
                        width: 100%;
                        border-collapse: collapse;
                        border-spacing: 0;
                        font-size: 0.9166em;
                    }
                    section table .qty, section table .unit, section table .total {
                        width: 15%;
                    }
                    section table .desc {
                        width: 55%;
                    }
                    section table thead {
                        display: table-header-group;
                        vertical-align: middle;
                        border-color: inherit;
                    }
                    section table thead th {
                        padding: 5px 10px;
                        background: #8BC34A;
                        border-bottom: 5px solid #FFFFFF;
                        border-right: 4px solid #FFFFFF;
                        text-align: right;
                        color: white;
                        font-weight: 400;
                        text-transform: uppercase;
                    }
                    section table thead th:last-child {
                        border-right: none;
                    }
                    section table thead .desc {
                        text-align: left;
                    }
                    section table thead .qty {
                        text-align: center;
                    }
                    section table tbody td {
                        padding: 10px;
                        background: #E8F3DB;
                        color: #777777;
                        text-align: right;
                        border-bottom: 5px solid #FFFFFF;
                        border-right: 4px solid #E8F3DB;
                    }
                    section table tbody td:last-child {
                        border-right: none;
                    }
                    section table tbody h3 {
                        margin-bottom: 5px;
                        color: #8BC34A;
                        font-weight: 600;
                    }
                    section table tbody .desc {
                        text-align: left;
                    }
                    section table tbody .qty {
                        text-align: center;
                    }
                    section table.grand-total {
                        margin-bottom: 45px;
                    }
                    section table.grand-total td {
                        padding: 5px 10px;
                        border: none;
                        color: #777777;
                        text-align: right;
                    }
                    section table.grand-total .desc {
                        background-color: transparent;
                    }
                    section table.grand-total tr:last-child td {
                        font-weight: 600;
                        color: #8BC34A;
                        font-size: 1.18181818181818em;
                    }
            
                    footer {
                        margin-bottom: 20px;
                    }
                    footer .thanks {
                        margin-bottom: 40px;
                        color: #8BC34A;
                        font-size: 1.16666666666667em;
                        font-weight: 600;
                    }
                    footer .notice {
                        margin-bottom: 25px;
                    }
                    footer .end {
                        padding-top: 5px;
                        border-top: 2px solid #8BC34A;
                        text-align: center;
                    }
                </style>
            </head>
            
            <body>
                <header class="clearfix">
                    <div class="container">
                        <div class="company-address">
                            <h2 class="title">Multi-vendor E-commerce Application</h2>
                            <p>
                                37 Salah Salem St.<br>
                                Cairo, Egypt
                            </p>
                        </div>
                        <div class="company-contact">
                            <div class="phone left">
                                <span class="circle"><img src="data:image/svg+xml;charset=utf-8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNS4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zOnNrZXRjaD0iaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoL25zIg0KCSB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjkuNzYycHgiIGhlaWdodD0iOS45NThweCINCgkgdmlld0JveD0iLTQuOTkyIDAuNTE5IDkuNzYyIDkuOTU4IiBlbmFibGUtYmFja2dyb3VuZD0ibmV3IC00Ljk5MiAwLjUxOSA5Ljc2MiA5Ljk1OCIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSI+DQo8dGl0bGU+RmlsbCAxPC90aXRsZT4NCjxkZXNjPkNyZWF0ZWQgd2l0aCBTa2V0Y2guPC9kZXNjPg0KPGcgaWQ9IlBhZ2UtMSIgc2tldGNoOnR5cGU9Ik1TUGFnZSI+DQoJPGcgaWQ9IklOVk9JQ0UtMSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTMwMS4wMDAwMDAsIC01NC4wMDAwMDApIiBza2V0Y2g6dHlwZT0iTVNBcnRib2FyZEdyb3VwIj4NCgkJPGcgaWQ9IlpBR0xBVkxKRSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMzAuMDAwMDAwLCAxNS4wMDAwMDApIiBza2V0Y2g6dHlwZT0iTVNMYXllckdyb3VwIj4NCgkJCTxnIGlkPSJLT05UQUtUSSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjY3LjAwMDAwMCwgMzUuMDAwMDAwKSIgc2tldGNoOnR5cGU9Ik1TU2hhcGVHcm91cCI+DQoJCQkJPGcgaWQ9Ik92YWwtMS1feDJCXy1GaWxsLTEiPg0KCQkJCQk8cGF0aCBpZD0iRmlsbC0xIiBmaWxsPSIjOEJDMzRBIiBkPSJNOC43NjUsMTIuMzc1YzAuMDIsMC4xNjItMC4wMjgsMC4zMDMtMC4xNDMsMC40MjJMNy4yNDYsMTQuMTkNCgkJCQkJCWMtMC4wNjIsMC4wNy0wLjE0MywwLjEzMy0wLjI0MywwLjE4MmMtMC4xMDEsMC4wNDktMC4xOTcsMC4wOC0wLjI5NSwwLjA5NGMtMC4wMDcsMC0wLjAyOCwwLTAuMDYyLDAuMDA0DQoJCQkJCQljLTAuMDM0LDAuMDA1LTAuMDgsMC4wMDgtMC4xMzQsMC4wMDhjLTAuMTMxLDAtMC4zNDMtMC4wMjMtMC42MzUtMC4wNjhjLTAuMjkzLTAuMDQ1LTAuNjUxLTAuMTU4LTEuMDc2LTAuMzM2DQoJCQkJCQljLTAuNDI0LTAuMTgyLTAuOTA0LTAuNDUxLTEuNDQyLTAuODA5Yy0wLjUzNi0wLjM1Ny0xLjEwOS0wLjg1Mi0xLjcxNi0xLjQ3OWMtMC40ODEtMC40ODQtMC44OC0wLjk1LTEuMTk4LTEuMzkzDQoJCQkJCQlDMC4xMjgsOS45NS0wLjEyNSw5LjU0MS0wLjMxOSw5LjE2NGMtMC4xOTMtMC4zNzYtMC4zMzgtMC43MTctMC40MzQtMS4wMjNjLTAuMDk3LTAuMzA2LTAuMTYxLTAuNTctMC4xOTUtMC43OTINCgkJCQkJCWMtMC4wMzUtMC4yMjEtMC4wNS0wLjM5NC0wLjA0Mi0wLjUyMWMwLjAwNy0wLjEyNiwwLjAxLTAuMTk3LDAuMDEtMC4yMTFjMC4wMTQtMC4wOTksMC4wNDQtMC4xOTgsMC4wOTMtMC4zMDENCgkJCQkJCWMwLjA0OS0wLjEwMSwwLjEwOC0wLjE4NCwwLjE3Ni0wLjI0N2wxLjM3NS0xLjQwM2MwLjA5Ny0wLjA5OCwwLjIwNi0wLjE0NywwLjMzLTAuMTQ3YzAuMDksMCwwLjE2OSwwLjAyNiwwLjIzOCwwLjA3OQ0KCQkJCQkJQzEuMyw0LjY0OCwxLjM1OSw0LjcxNCwxLjQwNiw0Ljc5MWwxLjEwNiwyLjE0MWMwLjA2MiwwLjExNCwwLjA4LDAuMjM1LDAuMDUyLDAuMzdDMi41MzgsNy40MzYsMi40NzgsNy41NDgsMi4zODksNy42NA0KCQkJCQkJTDEuODgzLDguMTU3QzEuODY5LDguMTcxLDEuODU2LDguMTk0LDEuODQ2LDguMjI2QzEuODM1LDguMjU2LDEuODMsOC4yODMsMS44Myw4LjMwNGMwLjAyNywwLjE0NywwLjA5LDAuMzE3LDAuMTg3LDAuNTA3DQoJCQkJCQljMC4wODIsMC4xNjksMC4yMSwwLjM3NSwwLjM4MiwwLjYxOGMwLjE3MiwwLjI0MywwLjQxNywwLjUyMSwwLjczNCwwLjgzOWMwLjMxMSwwLjMyMiwwLjU4NSwwLjU3NCwwLjgyOCwwLjc1NQ0KCQkJCQkJYzAuMjQsMC4xNzgsMC40NDMsMC4zMDksMC42MDQsMC4zOTVjMC4xNjIsMC4wODUsMC4yODYsMC4xMzUsMC4zNzIsMC4xNTRsMC4xMjgsMC4wMjRjMC4wMTUsMCwwLjAzOC0wLjAwNiwwLjA2Ny0wLjAxNg0KCQkJCQkJYzAuMDMyLTAuMDEsMC4wNTQtMC4wMjEsMC4wNjctMC4wMzdsMC41ODgtMC42MTJjMC4xMjUtMC4xMTIsMC4yNy0wLjE2OCwwLjQzNi0wLjE2OGMwLjExNywwLDAuMjA3LDAuMDIxLDAuMjc3LDAuMDYxaDAuMDENCgkJCQkJCWwxLjk5NSwxLjIwM0M4LjY1MSwxMi4xMiw4LjczNywxMi4yMzQsOC43NjUsMTIuMzc1TDguNzY1LDEyLjM3NXoiLz4NCgkJCQk8L2c+DQoJCQk8L2c+DQoJCTwvZz4NCgk8L2c+DQo8L2c+DQo8L3N2Zz4NCg==" alt=""><span class="helper"></span></span>
                                <span class="helper"></span>
                            </div>
                            <div class="email right">
                                <span class="circle"><img src="data:image/svg+xml;charset=utf-8;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAxNS4xLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjwhRE9DVFlQRSBzdmcgUFVCTElDICItLy9XM0MvL0RURCBTVkcgMS4xLy9FTiIgImh0dHA6Ly93d3cudzMub3JnL0dyYXBoaWNzL1NWRy8xLjEvRFREL3N2ZzExLmR0ZCI+DQo8c3ZnIHZlcnNpb249IjEuMSIgaWQ9IkxheWVyXzEiIHhtbG5zOnNrZXRjaD0iaHR0cDovL3d3dy5ib2hlbWlhbmNvZGluZy5jb20vc2tldGNoL25zIg0KCSB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCIgd2lkdGg9IjE0LjE3M3B4Ig0KCSBoZWlnaHQ9IjE0LjE3M3B4IiB2aWV3Qm94PSIwLjM1NCAtMi4yNzIgMTQuMTczIDE0LjE3MyIgZW5hYmxlLWJhY2tncm91bmQ9Im5ldyAwLjM1NCAtMi4yNzIgMTQuMTczIDE0LjE3MyIgeG1sOnNwYWNlPSJwcmVzZXJ2ZSINCgk+DQo8dGl0bGU+ZW1haWwxOTwvdGl0bGU+DQo8ZGVzYz5DcmVhdGVkIHdpdGggU2tldGNoLjwvZGVzYz4NCjxnIGlkPSJQYWdlLTEiIHNrZXRjaDp0eXBlPSJNU1BhZ2UiPg0KCTxnIGlkPSJJTlZPSUNFLTEiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC00MTcuMDAwMDAwLCAtNTUuMDAwMDAwKSIgc2tldGNoOnR5cGU9Ik1TQXJ0Ym9hcmRHcm91cCI+DQoJCTxnIGlkPSJaQUdMQVZMSkUiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDMwLjAwMDAwMCwgMTUuMDAwMDAwKSIgc2tldGNoOnR5cGU9Ik1TTGF5ZXJHcm91cCI+DQoJCQk8ZyBpZD0iS09OVEFLVEkiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDI2Ny4wMDAwMDAsIDM1LjAwMDAwMCkiIHNrZXRjaDp0eXBlPSJNU1NoYXBlR3JvdXAiPg0KCQkJCTxnIGlkPSJPdmFsLTEtX3gyQl8tZW1haWwxOSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTE3LjAwMDAwMCwgMC4wMDAwMDApIj4NCgkJCQkJPHBhdGggaWQ9ImVtYWlsMTkiIGZpbGw9IiM4QkMzNEEiIGQ9Ik0zLjM1NCwxNC4yODFoMTQuMTczVjUuMzQ2SDMuMzU0VjE0LjI4MXogTTEwLjQ0LDEwLjg2M0w0LjYyNyw2LjAwOGgxMS42MjZMMTAuNDQsMTAuODYzDQoJCQkJCQl6IE04LjEyNSw5LjgxMkw0LjA1LDEzLjIxN1Y2LjQwOUw4LjEyNSw5LjgxMnogTTguNjUzLDEwLjI1M2wxLjc4OCwxLjQ5M2wxLjc4Ny0xLjQ5M2w0LjAyOSwzLjM2Nkg0LjYyNEw4LjY1MywxMC4yNTN6DQoJCQkJCQkgTTEyLjc1NSw5LjgxMmw0LjA3NS0zLjQwM3Y2LjgwOEwxMi43NTUsOS44MTJ6Ii8+DQoJCQkJPC9nPg0KCQkJPC9nPg0KCQk8L2c+DQoJPC9nPg0KPC9nPg0KPC9zdmc+DQo=" alt=""><span class="helper"></span></span>
                                <a href="mailto:company@example.com">company@example.com</a>
                                <span class="helper"></span>
                            </div>
                        </div>
                    </div>
                </header>
            
                <section>
                    <div class="container">
                        <div class="details clearfix">
                            <div class="client left">
                                <p>발송처:</p>
                                <p class="name">' . $orderDetails['name'] . '</p>
                                <p>'
                                    . $orderDetails['address'] . ', ' . $orderDetails['city'] . ', ' . $orderDetails['state'] . ', ' . $orderDetails['country'] . '-' . $orderDetails['pincode'] .
                                '</p>
                                <a href="mailto:' . $orderDetails['email'] . '">' . $orderDetails['email'] . '</a>
                            </div>
                            <div class="data right">
                                <div class="title">주문 번호: ' . $orderDetails['id'] . '</div>
                                <div class="date">
                                    주문 일자: ' . date('Y-m-d h:i:s', strtotime($orderDetails['created_at'])) . '<br>
                                    주문 금액: INR ' . $orderDetails['grand_total'] . '<br>
                                    주문 상태: ' . $orderDetails['order_status'] . '<br>
                                    결제 방법: ' . $orderDetails['payment_method'] . '<br>
                                </div>
                            </div>
                        </div>
            
                        <table border="0" cellspacing="0" cellpadding="0">
                            <thead>
                                <tr>
                                    <th class="desc">상품 코드</th>
                                    <th class="qty">사이즈</th>
                                    <th class="qty">색상</th>
                                    <th class="qty">수량</th>
                                    <th class="unit">단가</th>
                                    <th class="total">합계</th>
                                </tr>
                            </thead>
                            <tbody>';

                            // 소계 계산
                            $subTotal = 0;
                            foreach ($orderDetails['orders_products'] as $product) {
                                // $invoiceHTML 연결
                                $invoiceHTML .= '
                                    <tr>
                                        <td class="desc">' . $product['product_code'] . '</td>
                                        <td class="qty">' . $product['product_size'] . '</td>
                                        <td class="qty">' . $product['product_color'] . '</td>
                                        <td class="qty">' . $product['product_qty'] . '</td>
                                        <td class="unit">INR ' . $product['product_price'] . '</td>
                                        <td class="total">INR ' . $product['product_price'] * $product['product_qty'] . '</td>
                                    </tr>';

                                // 소계 합산
                                $subTotal = $subTotal + ($product['product_price'] * $product['product_qty']);
                            }

                            // $invoiceHTML 연결
                            $invoiceHTML .= '
                            </tbody>
                        </table>
                        <div class="no-break">
                            <table class="grand-total">
                                <tbody>
                                    <tr>
                                        <td class="desc"></td>
                                        <td class="desc"></td>
                                        <td class="desc"></td>
                                        <td class="total" colspan=2>소계</td>
                                        <td class="total">INR ' . $subTotal . '</td>
                                    </tr>
                                    <tr>
                                        <td class="desc"></td>
                                        <td class="desc"></td>
                                        <td class="desc"></td>
                                        <td class="total" colspan=2>배송비</td>
                                        <td class="total">INR 0</td>
                                    </tr>
                                    <tr>
                                        <td class="desc"></td>
                                        <td class="desc"></td>
                                        <td class="desc"></td>
                                        <td class="total" colspan=2>할인액</td>';

                                        if ($orderDetails['coupon_amount'] > 0) {
                                            $invoiceHTML .= '<td class="total">INR '. $orderDetails['coupon_amount'] . '</td>';
                                        } else {
                                            $invoiceHTML .= '<td class="total">INR 0</td>';
                                        }

                                        $invoiceHTML .= '
                                    </tr>
                                    <tr>
                                        <td class="desc"></td>
                                        <td class="desc"></td>
                                        <td class="desc"></td>
                                        <td class="total" colspan="2">총계</td>
                                        <td class="total">INR ' . $orderDetails['grand_total'] . '</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            
                <footer>
                    <div class="container">
                        <div class="thanks">감사합니다!</div>
                        <div class="end">본 인보이스는 전산으로 발행되었으며 서명 및 날인 없이 유효합니다.</div>
                    </div>
                </footer>
            
            </body>
            
            </html>
        ';


        // Dompdf 패키지 사용
        $dompdf = new \Dompdf\Dompdf();
        
        $dompdf->loadHtml($invoiceHTML);

        // 용지 크기 및 방향 설정 (선택 사항)
        $dompdf->setPaper('A4', 'landscape');

        // HTML을 PDF로 렌더링
        $dompdf->render();

        // 생성된 PDF를 브라우저로 출력
        $dompdf->stream();
    }

}