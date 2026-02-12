<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function cart()
    {
        return view('front.shop.cart');
    }

    public function order()
    {
        return view('front.shop.order_form');
    }

    public function orderComplete()
    {
        // return view('front.shop.order_confirm'); // 구현 예정
        return "주문 완료 페이지 (구현 예정)";
    }

    public function orderDetails()
    {
        // return view('front.shop.order_details'); // 구현 예정
        return "주문 상세 페이지 (구현 예정)";
    }

    public function cancelDetails()
    {
        return "취소 상세 페이지 (구현 예정)";
    }

    public function exchangeDetails()
    {
        return "교환 상세 페이지 (구현 예정)";
    }

    public function returnDetails()
    {
        return "반품 상세 페이지 (구현 예정)";
    }
}
