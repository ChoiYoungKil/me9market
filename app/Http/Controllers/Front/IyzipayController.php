<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class IyzipayController extends Controller
{
    // 라라벨용 iyzico 결제 게이트웨이 연동
    // https://github.com/iyzico/iyzipay-php



    // 라라벨용 iyzico 결제 연동 (Front/ProductsController.php의 checkout() 메서드에서 접근함). front/iyzipay/iyzipay.blade.php 페이지 렌더링
    public function iyzipay() {
        if (Session::has('order_id')) { // 주문이 완료된 경우 
            return view('front.iyzipay.iyzipay');

        } else { // 주문이 완료되지 않은 경우
            return redirect('cart'); // 장바구니 페이지로 리다이렉트
        }
    }

    // iyzipay 결제 진행 (주문 상세 정보와 함께 iyzico 결제 게이트웨이로 리다이렉트)    
    public function pay() {
        // order_id는 Front/ProductsController.php의 checkout() 메서드에서 세션에 저장됨
        $orderDetails = \App\Models\Order::with('orders_products')->where('id', Session::get('order_id'))->first()->toArray(); 

        $nameArr = explode(' ', $orderDetails['name']); // 성과 이름을 분리하여 결제 서비스로 전달
        $options = \App\Models\Iyzipay::options();



        // iyzico 결제 요청 생성
        $request = new \Iyzipay\Request\CreatePayWithIyzicoInitializeRequest();
        $request->setLocale(\Iyzipay\Model\Locale::TR);
        $request->setConversationId(Session::get('order_id')); 
        $request->setPrice(Session::get('grand_total')); 
        $request->setPaidPrice(Session::get('grand_total')); 
        $request->setCurrency(\Iyzipay\Model\Currency::TL);
        $request->setBasketId(Session::get('order_id')); 
        $request->setPaymentGroup(\Iyzipay\Model\PaymentGroup::PRODUCT);
        $request->setCallbackUrl("https://www.merchant.com/callback");
        $request->setEnabledInstallments(array(2, 3, 6, 9));
        $buyer = new \Iyzipay\Model\Buyer();
        $buyer->setId($orderDetails['user_id']); 
        $buyer->setName($nameArr[0]); 
        $buyer->setSurname($nameArr[1] ?? 'Not set'); 
        $buyer->setGsmNumber("+905350000000"); // 더미 데이터
        $buyer->setEmail($orderDetails['email']); 
        $buyer->setIdentityNumber("74300864791"); // 더미 데이터
        $buyer->setLastLoginDate(""); 
        $buyer->setRegistrationDate(""); 
        $buyer->setRegistrationAddress($orderDetails['address']); 
        $buyer->setIp(""); 
        $buyer->setCity($orderDetails['city']); 
        $buyer->setCountry($orderDetails['country']); 
        $buyer->setZipCode($orderDetails['pincode']); 
        $request->setBuyer($buyer); 
        $shippingAddress = new \Iyzipay\Model\Address(); 
        $shippingAddress->setContactName($orderDetails['name']); 
        $shippingAddress->setCity($orderDetails['city']); 
        $shippingAddress->setCountry($orderDetails['country']); 
        $shippingAddress->setAddress($orderDetails['address']); 
        $shippingAddress->setZipCode($orderDetails['pincode']); 
        $request->setShippingAddress($shippingAddress); 
        $billingAddress = new \Iyzipay\Model\Address(); 
        $billingAddress->setContactName($orderDetails['name']); 
        $billingAddress->setCity($orderDetails['city']); 
        $billingAddress->setCountry($orderDetails['country']); 
        $billingAddress->setAddress($orderDetails['address']); 
        $billingAddress->setZipCode($orderDetails['pincode']); 
        $request->setBillingAddress($billingAddress); 
        $basketItems = array(); 
        $firstBasketItem = new \Iyzipay\Model\BasketItem(); 
        $firstBasketItem->setId(Session::get('order_id')); 
        $firstBasketItem->setName("주문 번호: " . Session::get('order_id')); 
        $firstBasketItem->setCategory1("Multi-vendor E-commerce Application Product"); 
        $firstBasketItem->setCategory2("");
        $firstBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL); 
        $firstBasketItem->setPrice(Session::get('grand_total')); 
        $basketItems[0] = $firstBasketItem; 

        /*
            $secondBasketItem = new \Iyzipay\Model\BasketItem(); // dummy data
            $secondBasketItem->setId("BI102"); // dummy data
            $secondBasketItem->setName("Game code"); // dummy data
            $secondBasketItem->setCategory1("Game"); // dummy data
            $secondBasketItem->setCategory2("Online Game Items"); // dummy data
            $secondBasketItem->setItemType(\Iyzipay\Model\BasketItemType::VIRTUAL); // dummy data
            $secondBasketItem->setPrice("0.5"); // dummy data
            $basketItems[1] = $secondBasketItem; // dummy data
            $thirdBasketItem = new \Iyzipay\Model\BasketItem(); // dummy data
            $thirdBasketItem->setId("BI103"); // dummy data
            $thirdBasketItem->setName("Usb"); // dummy data
            $thirdBasketItem->setCategory1("Electronics"); // dummy data
            $thirdBasketItem->setCategory2("Usb / Cable"); // dummy data
            $thirdBasketItem->setItemType(\Iyzipay\Model\BasketItemType::PHYSICAL); // dummy data
            $thirdBasketItem->setPrice("0.2"); // dummy data
            $basketItems[2] = $thirdBasketItem; // dummy data
        */

        $request->setBasketItems($basketItems); 
        # 요청 생성
        $payWithIyzicoInitialize = \Iyzipay\Model\PayWithIyzicoInitialize::create($request, $options); 



        // JSON 응답 디버깅
        // dd($payWithIyzicoInitialize);
        /* echo '<pre>', var_dump($payWithIyzicoInitialize), '</pre>';
        exit; */

        // JSON 응답(문자열/텍스트)을 PHP 배열로 변환
        $paymentResponse = (array) $payWithIyzicoInitialize; // Type Casting: https://www.php.net/manual/en/language.types.type-juggling.php#language.types.typecasting
        // dd($paymentResponse);
        /* echo '<pre>', var_dump($paymentResponse), '</pre>';
        exit; */

        foreach ($paymentResponse as $key => $response) {
            $response_decode = json_decode($response);

            $pay_url = $response_decode->payWithIyzicoPageUrl;

            break; // 첫 번째 반복 직후 루프 종료
        }


        // 사용자를 결제 페이지로 리다이렉트
        return redirect($pay_url);
    }

}