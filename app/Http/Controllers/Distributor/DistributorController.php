<?php

namespace App\Http\Controllers\Distributor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DistributorController extends Controller
{
    // Distributor Session Check Middleware style check
    private function checkAuth()
    {
        return Session::has('distributor_id');
    }

    public function login()
    {
        if ($this->checkAuth()) {
            return redirect()->route('distributor.orders.pending');
        }
        return view('distributor.login');
    }

    public function loginSubmit(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Simple auth for testing: any email/password works, default distributor 1
        Session::put('distributor_id', 1);
        Session::put('distributor_name', '주식회사 메인공급처 (Distributor A)');
        Session::put('distributor_email', $request->email);

        return redirect()->route('distributor.orders.pending')->with('flash_message_success', '발주사 로그인 성공!');
    }

    public function logout()
    {
        Session::forget(['distributor_id', 'distributor_name', 'distributor_email']);
        return redirect()->route('distributor.login')->with('flash_message_success', '로그아웃 되었습니다.');
    }

    public function ordersPending()
    {
        if (!$this->checkAuth()) {
            return redirect()->route('distributor.login');
        }

        // Mock Order Items for Testing (RF-04-02-01)
        $orders = [
            [
                'id' => 1,
                'order_id' => 'Me9-Shop-0032022',
                'channel_name' => '메이크인벤토리 Shop',
                'product_code' => 'a0029',
                'product_name' => 'BlueViolet a omnis',
                'option' => 'RD/S',
                'quantity' => 2,
                'status' => '배송대기',
                'receiver' => '홍길동',
                'address' => '00234 서울시 마포구 공덕동 1118-12 B112',
                'request_date' => '2024-10-10 14:22:00'
            ],
            [
                'id' => 2,
                'order_id' => 'Me9-Shop-0032025',
                'channel_name' => '초이마켓 Shop',
                'product_code' => 'b0087',
                'product_name' => 'Comfortable Cotton T-Shirt',
                'option' => 'Black/L',
                'quantity' => 1,
                'status' => '배송대기',
                'receiver' => '이순신',
                'address' => '12093 서울시 영등포구 여의도동 44-5',
                'request_date' => '2024-10-11 09:15:30'
            ]
        ];

        return view('distributor.orders_pending', compact('orders'));
    }

    public function ordersCompleted()
    {
        if (!$this->checkAuth()) {
            return redirect()->route('distributor.login');
        }

        // Mock Completed Order Items for Testing (RF-04-02-02)
        $orders = [
            [
                'id' => 3,
                'order_id' => 'Me9-Shop-0031099',
                'channel_name' => '코리아몰 Shop',
                'product_code' => 'c0012',
                'product_name' => 'Premium Leather Wallet',
                'option' => 'Brown',
                'quantity' => 1,
                'status' => '배송중',
                'receiver' => '김철수',
                'address' => '04928 서울시 강남구 역삼동 700-12',
                'request_date' => '2024-10-08 17:05:00',
                'courier' => 'CJ대한통운',
                'tracking_no' => '123456789012'
            ]
        ];

        return view('distributor.orders_completed', compact('orders'));
    }

    public function orderDetails($id)
    {
        if (!$this->checkAuth()) {
            return redirect()->route('distributor.login');
        }

        // Mock items search based on ID
        $orders = [
            1 => [
                'id' => 1,
                'order_id' => 'Me9-Shop-0032022',
                'channel_name' => '메이크인벤토리 Shop',
                'product_code' => 'a0029',
                'product_name' => 'BlueViolet a omnis',
                'option' => 'RD/S',
                'quantity' => 2,
                'status' => '배송대기',
                'receiver' => '홍길동',
                'address' => '서울시 마포구 공덕동 1118-12 B112',
                'zipcode' => '00234',
                'request_date' => '2024-10-10 14:22:00',
                'courier' => '-',
                'tracking_no' => '-'
            ],
            2 => [
                'id' => 2,
                'order_id' => 'Me9-Shop-0032025',
                'channel_name' => '초이마켓 Shop',
                'product_code' => 'b0087',
                'product_name' => 'Comfortable Cotton T-Shirt',
                'option' => 'Black/L',
                'quantity' => 1,
                'status' => '배송대기',
                'receiver' => '이순신',
                'address' => '서울시 영등포구 여의도동 44-5',
                'zipcode' => '12093',
                'request_date' => '2024-10-11 09:15:30',
                'courier' => '-',
                'tracking_no' => '-'
            ],
            3 => [
                'id' => 3,
                'order_id' => 'Me9-Shop-0031099',
                'channel_name' => '코리아몰 Shop',
                'product_code' => 'c0012',
                'product_name' => 'Premium Leather Wallet',
                'option' => 'Brown',
                'quantity' => 1,
                'status' => '배송중',
                'receiver' => '김철수',
                'address' => '서울시 강남구 역삼동 700-12',
                'zipcode' => '04928',
                'request_date' => '2024-10-08 17:05:00',
                'courier' => 'CJ대한통운',
                'tracking_no' => '123456789012'
            ]
        ];

        $order = $orders[$id] ?? abort(404);

        return view('distributor.order_details', compact('order'));
    }

    public function updateOrder(Request $request, $id)
    {
        // For testing, just return back with a success message
        return redirect()->route('distributor.order.details', $id)->with('flash_message_success', '발주 배송 정보가 성공적으로 수정되었습니다.');
    }

    public function uploadInvoice(Request $request)
    {
        $request->validate([
            'invoice_file' => 'required'
        ]);

        return redirect()->route('distributor.orders.pending')->with('flash_message_success', '송장 엑셀 파일이 업로드되어 발주 처리가 완료되었습니다!');
    }
}
