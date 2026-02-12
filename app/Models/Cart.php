<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;


class Cart extends Model
{
    use HasFactory;


    
    // 장바구니 아이템(carts)과 상품(products)의 관계 (모든 장바구니 아이템은 하나의 상품에 속함)
    public function product() { 
        return $this->belongsTo('App\Models\Product', 'product_id'); 
    }



    // 특정 사용자의 장바구니 아이템 가져오기 (인증된 사용자는 user_id로, 비인증 사용자는 session_id로 식별)
    public static function getCartItems() { 
        // 로그인 여부에 따라 사용자 장바구니 아이템 가져오기
        if (Auth::check()) { // 로그인한 사용자의 경우 user_id를 사용하여 아이템 조회
            $getCartItems = Cart::with([ 
                'product' => function ($query) { 
                    $query->select('id', 'category_id', 'product_name', 'product_code', 'product_color', 'product_image', 'product_weight'); 
                }
            ])->orderBy('id', 'Desc')->where([ 
                'user_id'    => Auth::user()->id 
            ])->get()->toArray();

        } else { // 로그인하지 않은 사용자의 경우 session_id를 사용하여 아이템 조회
            $getCartItems = Cart::with([ 
                'product' => function ($query) { 
                    $query->select('id', 'category_id', 'product_name', 'product_code', 'product_color', 'product_image', 'product_weight'); 
                }
            ])->orderBy('id', 'Desc')->where([ 
                'session_id' => Session::get('session_id') 
            ])->get()->toArray();
        }


        return $getCartItems;
    }

}