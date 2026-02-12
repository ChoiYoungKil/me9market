<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Iyzipay extends Model
{
    use HasFactory;

    // iyzico 결제 게이트웨이 통합 (Laravel과 함께)



    // 이 메소드는 Multi-vendor E-commerce Application\\vendor\\iyzico\\iyzipay-php\\samples\\config.php 파일에서 복사되었습니다
    public static function options()
    {
        $options = new \Iyzipay\Options();



        // API 키:
        $options->setApiKey('sandbox-W7IiunBL5OALo4iibT3r0S3t3fMswzkn');    

        // 비밀 키:
        $options->setSecretKey('sandbox-gVf4cjziwu6FJGrwkeIyBlPlizniaqhw'); 



        $options->setBaseUrl('https://sandbox-api.iyzipay.com');

        return $options;
    }

}