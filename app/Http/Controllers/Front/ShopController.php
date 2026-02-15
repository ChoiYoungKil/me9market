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
        return view('front.shop.order_complete');
    }

    public function orderDetails()
    {
        return view('front.shop.order_details');
    }

    public function cancelDetails()
    {
        return view('front.shop.cancel_details');
    }

    public function exchangeDetails()
    {
        return view('front.shop.exchange_details');
    }

    public function returnDetails()
    {
        return view('front.shop.return_details');
    }
}
