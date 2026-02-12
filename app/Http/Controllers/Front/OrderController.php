<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Order;

class OrderController extends Controller
{
    // 사용자 '내 주문(My Orders)' 페이지 렌더링    
    public function orders($id = null) { // 만약 {id?} (옵션 파라미터)가 전달되면 front/orders/order_details.blade.php 페이지로 이동하고, 그렇지 않으면 front/orders/orders.blade.php 페이지로 이동함    
        if (empty($id)) { // 라우트(URL)에 주문 ID가 옵션 파라미터로 전달되지 않은 경우, front/orders/orders.blade.php 페이지로 이동함을 의미함
            // 현재 인증된 사용자의 모든 주문 가져오기
            $orders = Order::with('orders_products')->where('user_id', \Illuminate\Support\Facades\Auth::user()->id)->orderBy('id', 'Desc')->get()->toArray(); // 'orders_products'는 Order.php 모델의 관계 메서드 이름임
            // dd($orders);


            return view('front.orders.orders')->with(compact('orders'));

        } else { // 라우트(URL)에 주문 ID가 옵션 파라미터로 전달된 경우, front/orders/order_details.blade.php 페이지로 이동함을 의미함
            $orderDetails = Order::with('orders_products')->where('id', $id)->first()->toArray();// 'orders_products'는 Order.php 모델의 관계 메서드 이름임
            // dd($orderDetails);


            return view('front.orders.order_details')->with(compact('orderDetails'));
        }

    }

}