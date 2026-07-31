<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'mobile',
        'email',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'commission',
        'status',
        'use_own_pg',
        'pg_provider',
        'pg_merchant_id',
        'pg_site_code',
        'pg_client_key',
        'pg_secret_key',
    ];

    protected $casts = [
        'use_own_pg' => 'boolean',
        'pg_merchant_id' => 'encrypted',
        'pg_site_code' => 'encrypted',
        'pg_client_key' => 'encrypted',
        'pg_secret_key' => 'encrypted',
    ];


    // 입점업체(vendors)와 입점업체 상세 정보(vendors_business_details)의 관계
    public function vendorbusinessdetails() {    
        return $this->belongsTo('App\Models\VendorsBusinessDetail', 'id', 'vendor_id'); 
    }



    
    // 입점업체 ID로 상점명을 가져오는 메소드
    public static function getVendorShop($vendorid) { 
        $getVendorShop = \App\Models\VendorsBusinessDetail::select('shop_name')->where('vendor_id', $vendorid)->first()->toArray();


        return $getVendorShop['shop_name'];
    }

    // vendors 테이블의 commission 컬럼에서 입점업체의 수수료율 가져오기
    public static function getVendorCommission($vendor_id) {
        $getVendorCommission = Vendor::select('commission')->where('id', $vendor_id)->first()->toArray();


        return $getVendorCommission['commission'];
    }

}
