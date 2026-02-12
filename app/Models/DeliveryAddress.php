<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;


class DeliveryAddress extends Model
{
    use HasFactory;


    // 대량 할당: https://laravel.com/docs/10.x/eloquent#mass-assignment
    protected $fillable = [
        'user_id', 'name', 'address', 'city', 'state', 'country', 'pincode', 'status', 'mobile'
    ];



    // 현재 인증된(로그인된) 사용자의 모든 배송 주소를 가져옵니다
    public static function deliveryAddresses() {
        $deliveryAddresses = DeliveryAddress::where('user_id', Auth::user()->id)->get()->toArray(); // Retrieving The Authenticated User: https://laravel.com/docs/9.x/authentication#retrieving-the-authenticated-user


        return $deliveryAddresses;
    }

}