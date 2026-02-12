<?php


// Omnipay PayPal 패키지 사용 ("composer require league/omnipay omnipay/paypal")
// https://github.com/thephpleague/omnipay-paypal.    
// https://github.com/thephpleague/omnipay    
namespace App\Http\Controllers\Front;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

use App\Models\Order;
use App\Models\ProductsAttribute;

use Omnipay\Omnipay;



class PaypalController extends Controller
{
    // 페이팔 결제 게이트웨이 연동



    // Omnipay PayPal 패키지 사용 ("composer require league/omnipay omnipay/paypal")
    private $gateway; // $gateway는 Omnipay\Common\GatewayFactory 인터페이스 객체입니다.


    public function __construct() {
        // 결제 게이트웨이 설정
        $this->gateway = Omnipay::create('PayPal_Rest'); // https://github.com/thephpleague/omnipay-paypal#:~:text=PayPal_Rest%20(Paypal%20Rest%20API)
        // dd($this->gateway);

        $this->gateway->setClientId(env('PAYPAL_CLIENT_ID'));   // 프로젝트의 .env 파일에서 "PayPal Client ID"를 가져옵니다.
        $this->gateway->setSecret(env('PAYPAL_CLIENT_SECRET')); // 프로젝트의 .env 파일에서 "PayPal Secret"을 가져옵니다.
        $this->gateway->setTestMode(true); // 테스트 목적으로만 사용함을 의미합니다.
    }

    // PayPal을 사용한 결제    
    public function pay(Request $request) {
        try {
            $paypal_amount = round(Session::get('grand_total') / 80, 2); // 'grand_total'은 Front/ProductsController.php의 checkout() 메서드에서 세션에 저장되었습니다. (참고: PayPal은 주요 통화만 수락하므로 INR을 USD로 변환하기 위해 80으로 나누었습니다)    

            // 구매 요청 발송
            $response = $this->gateway->purchase(array( // $gateway는 Omnipay\Common\GatewayFactory 인터페이스 객체임 // $response는 PayPal 사이트(API/백엔드)에서 반환됨
                'amount'    => $paypal_amount,
                'currency'  => env('PAYPAL_CURRENCY'), // .env 파일에서 설정한 페이팔 통화를 가져옴
                'returnUrl' => url('success'), 
                'cancelUrl' => url('error')   
            ))->send();
            // dd($response);

            // 응답 처리
            if ($response->isRedirect()) { // $response는 PayPal 사이트(API/백엔드)에서 반환됨
                // 외부 결제 게이트웨이로 리다이렉트
                $response->redirect(); // $response는 PayPal 사이트(API/백엔드)에서 반환됨
            } else {
                // 결제 실패: 고객에게 메시지 표시
                return $response->getMessage(); // $response는 PayPal 사이트(API/백엔드)에서 반환됨 // 메시지는 PayPal 사이트에서 제공됨
            }

        } catch (\Throwable $th) {    // $th 객체는 Throwable 인터페이스를 나타냄
            return $th->getMessage(); // $th 객체는 Throwable 인터페이스를 나타냄
        }
    }

    
    public function success(Request $request) {
        if (!Session::has('order_id')) { // 세션에 'order_id'가 없는 경우
            return view('cart');
        }


        if ($request->input('paymentId') && $request->input('PayerID')) { // 입력 값 가져오기
            $transaction = $this->gateway->completePurchase(array(
                'payer_id'             => $request->input('PayerID'), // 입력 값 가져오기
                'transactionReference' => $request->input('paymentId'), // 입력 값 가져오기
            ));

            $response = $transaction->send(); // $response는 PayPal 사이트(API/백엔드)에서 반환됨

            if ($response->isSuccessful()) { // 결제가 성공하면 결제 상세 정보를 payments 데이터베이스 테이블에 삽입함
                $arr = $response->getData(); // $response는 PayPal 사이트(API/백엔드)에서 반환됨

                // payments 테이블에 결제 상세 정보 삽입
                $payment = new \App\Models\Payment;

                $payment->order_id       = Session::get('order_id'); // 우리 웹사이트 정보
                $payment->user_id        = Auth::user()->id; // 우리 웹사이트의 인증된 사용자 정보
                $payment->payment_id     = $arr['id']; // PayPal 사이트(API/백엔드)에서 반환됨
                $payment->payer_id       = $arr['payer']['payer_info']['payer_id'];    // PayPal 사이트(API/백엔드)에서 반환됨
                $payment->payer_email    = $arr['payer']['payer_info']['email'];       // PayPal 사이트(API/백엔드)에서 반환됨
                $payment->amount         = $arr['transactions'][0]['amount']['total']; // PayPal 사이트(API/백엔드)에서 반환됨
                $payment->currency       = env('PAYPAL_CURRENCY'); // .env 파일에서 선택한 PayPal 통화를 가져옴
                $payment->payment_status = $arr['state']; // PayPal 사이트(API/백엔드)에서 반환됨

                $payment->save();


                // orders 테이블의 order_status 컬럼을 'Paid'로 업데이트    
                $order_id = Session::get('order_id'); // Interacting With The Session: Retrieving Data: https://laravel.com/docs/9.x/session#retrieving-data
                Order::where('id', $order_id)->update(['order_status' => 'Paid']);


                // 사용자에게 PayPal 결제 확인 이메일 발송    
                $orderDetails = Order::with('orders_products')->where('id', $order_id)->first()->toArray(); // Eager Loading 사용: 'orders_products'는 Order 과의 관계 메서드임
                $email = Auth::user()->email; // 인증된 사용자 이메일 가져오기

                // 이메일 뷰에 전달할 데이터/변수
                $messageData = [
                    'email'        => $email,
                    'name'         => Auth::user()->name, 
                    'order_id'     => $order_id,
                    'orderDetails' => $orderDetails
                ];

                \Illuminate\Support\Facades\Mail::send('emails.order', $messageData, function ($message) use ($email) { // 이메일 발송: 'emails.order'는 resources/views/emails 폴더의 order.blade.php 파일임
                    $message->to($email)->subject('Order Paid through PayPal - MultiVendorEcommerceApplication.com.eg');
                });


                // 재고 관리 - 주문 발생 시 재고 감소
                // 재고 관리는 Front/ProductsController.php의 checkout() 메서드와 Front/PaypalController.php의 success() 메서드 두 곳에 작성되었습니다.
                foreach ($orderDetails['orders_products'] as $key => $order) {
                    $getProductStock = ProductsAttribute::getProductStock($order['product_id'], $order['product_size']); // products_attributes 테이블에서 특정 상품 및 사이즈의 재고를 가져옴

                    $newStock = $getProductStock - $order['product_qty']; // 기존 재고에서 주문 수량만큼 차감하여 새로운 재고 계산

                    ProductsAttribute::where([ // products_attributes 테이블에 새로운 재고 수량 업데이트
                        'product_id' => $order['product_id'],
                        'size'       => $order['product_size']
                    ])->update(['stock' => $newStock]);
                }


                // PayPal 결제 후 장바구니를 비웁니다.
                \App\Models\Cart::where('user_id', Auth::user()->id)->delete(); // 장바구니 비우기


                // 완료 페이지 렌더링
                return view('front.paypal.success');

            } else { // 결제 실패 시
                // 결제 실패: 고객에게 메시지 표시
                return $response->getMessage(); // $response는 PayPal 사이트(API/백엔드)에서 반환됨
            }

        } else {
            return '결제가 거절되었습니다!';
        }
    }

    
    public function error() {
        // return '사용자가 결제를 취소했습니다.';

        
        return view('front.paypal.fail');
    }



    // 라라벨용 페이팔 결제 연동 (Front/ProductsController.php의 checkout() 메서드에서 접근함). front/paypal/paypal.blade.php 페이지 렌더링
    public function paypal() {
        if (Session::has('order_id')) { // 주문이 완료된 경우 (Front/ProductsController.php의 checkout() 메서드에서 리다이렉트됨)
            return view('front.paypal.paypal');

        } else { // 주문이 완료되지 않은 경우
            return redirect('cart'); // 사용자를 장바구니(cart.blade.php) 페이지로 리다이렉트
        }
    }

}