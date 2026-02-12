<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// 다중 인증(멀티 인증)을 위한 설정
use Illuminate\Foundation\Auth\User as Authenticatable; 



// class Admin extends Model

class Admin extends Authenticatable 
{
    use HasFactory;


    // 다중 인증 가드 설정 (auth.php 파일에서 'admin' 가드 확인)
    protected $guard = 'admin'; 



    // 관계 정의: 관리자는 특정 입점업체에 속할 수 있음

    public function vendorPersonal() { // admins 테이블과 vendors 테이블의 관계
        return $this->belongsTo('App\Models\Vendor', 'vendor_id'); 
    }

    public function vendorBusiness() { // admins 테이블과 vendors_business_details 테이블의 관계
        return $this->belongsTo('App\Models\VendorsBusinessDetail', 'vendor_id'); 
    }

    public function vendorBank() { // admins 테이블과 vendors_bank_details 테이블의 관계
        return $this->belongsTo('App\Models\VendorsBankDetail', 'vendor_id'); 
    }
}