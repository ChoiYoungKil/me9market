<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        // Placeholder for cart data
        $cartItems = []; 
        return view('front.cart.index', compact('cartItems'));
    }
}
