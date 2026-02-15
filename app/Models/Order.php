<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    // 주문(Orders) 모듈을 위해 `orders`와 `orders_products` 두 개의 테이블을 사용합니다.
    // `orders` 테이블은 주문의 주요 정보(배송지, 쿠폰, 배송비, 결제 수단 등)를 저장하고,
    // `orders_products` 테이블은 주문 상품의 상세 정보(상품명, 코드, 색상, 사이즈, 가격 등)를 저장합니다.
    // 하나의 주문은 여러 개의 주문 상품을 가질 수 있는 일대다(One-to-Many) 관계입니다.



    // 주문(orders) 테이블과 주문 상품(orders_products) 테이블의 관계 (하나의 주문은 여러 주문 상품을 가짐)
    public function orders_products() {    
        return $this->hasMany('App\Models\OrdersProduct', 'order_id'); 
    }

    public function claims()
    {
        return $this->hasMany(OrderClaim::class);
    }





    // Shiprocket API 연동을 위해 생성된 관계 메소드
    public function order_items() {    
        return $this->hasMany('App\Models\OrdersProduct', 'order_id'); 
    }

    // Shiprocket API 연동: 주문 정보를 Shiprocket 시스템으로 전송
    public static function pushOrder($order_id) { // this method is called from the pushOrder() method in API/APIController.php and from updateOrderStatus() method in Admin/OrderController.php
        $orderDetails = Order::with('order_items')->where('id', $order_id)->first()->toArray(); // Eager Loading: https://laravel.com/docs/9.x/eloquent-relationships#eager-loading    // 'order_items' is the relationship method name in Order.php model
        // dd($orderDetails);

        // Shiprocket JSON 요청 준비: API 문서의 키 이름에 데이터 매핑
        $orderDetails['order_id']              = $orderDetails['id'];         // 'order_id'                is the Shiprocket's JSON request key/name, while 'id'         is our `orders` table column name
        $orderDetails['order_date']            = $orderDetails['created_at']; // 'order_date'              is the Shiprocket's JSON request key/name, while 'created_at' is our `orders` table column name
        $orderDetails['pickup_location']       = "Test";    // We created a Pickup Location (We called it "Test") while creating the Shiprocket account for the first time.
        $orderDetails['channel_id']            = "1855855"; // We generated a channel_id using the Channels API
        $orderDetails['comment']               = 'Test Order';
        $orderDetails['billing_customer_name'] = $orderDetails['name'];       // 'billing_customer_name'   is the Shiprocket's JSON request key/name, while 'name'       is our `orders` table column name
        $orderDetails['billing_last_name']     = '';
        $orderDetails['billing_address']       = $orderDetails['address'];    // 'billing_address'         is the Shiprocket's JSON request key/name, while 'address'    is our `orders` table column name
        $orderDetails['billing_address_2']     = '';
        $orderDetails['billing_city']          = $orderDetails['city'];       // 'billing_city'            is the Shiprocket's JSON request key/name, while 'city'       is our `orders` table column name
        $orderDetails['billing_pincode']       = $orderDetails['pincode'];    // 'billing_pincode'         is the Shiprocket's JSON request key/name, while 'pincode'    is our `orders` table column name
        $orderDetails['billing_state']         = $orderDetails['state'];      // 'billing_state'           is the Shiprocket's JSON request key/name, while 'state'      is our `orders` table column name
        $orderDetails['billing_country']       = $orderDetails['country'];    // 'billing_country'         is the Shiprocket's JSON request key/name, while 'country'    is our `orders` table column name
        $orderDetails['billing_email']         = $orderDetails['email'];      // 'billing_email'           is the Shiprocket's JSON request key/name, while 'email'      is our `orders` table column name
        $orderDetails['billing_phone']         = (int) $orderDetails['mobile']; // Type Casting: https://www.php.net/manual/en/language.types.type-juggling.php#language.types.typecasting     // 'billing_phone'           is the Shiprocket's JSON request key/name, while 'mobile'     is our `orders` table column name

        $orderDetails['shipping_is_billing']   = true; // 배송지와 청구지 동일 여부

        $orderDetails['shipping_customer_name'] = $orderDetails['name'];       // 'shipping_customer_name' is the Shiprocket's JSON request key/name, while 'name'       is our `orders` table column name
        $orderDetails['shipping_last_name']     = '';
        $orderDetails['shipping_address']       = $orderDetails['address'];    // 'shipping_address'       is the Shiprocket's JSON request key/name, while 'address'    is our `orders` table column name
        $orderDetails['shipping_address_2']     = '';
        $orderDetails['shipping_city']          = $orderDetails['city'];       // 'shipping_city'          is the Shiprocket's JSON request key/name, while 'city'       is our `orders` table column name
        $orderDetails['shipping_pincode']       = $orderDetails['pincode'];    // 'shipping_pincode'       is the Shiprocket's JSON request key/name, while 'pincode'    is our `orders` table column name
        $orderDetails['shipping_state']         = $orderDetails['state'];      // 'shipping_state'         is the Shiprocket's JSON request key/name, while 'state'      is our `orders` table column name
        $orderDetails['shipping_country']       = $orderDetails['country'];    // 'shipping_country'       is the Shiprocket's JSON request key/name, while 'country'    is our `orders` table column name
        $orderDetails['shipping_email']         = $orderDetails['email'];      // 'shipping_email'         is the Shiprocket's JSON request key/name, while 'email'      is our `orders` table column name
        $orderDetails['shipping_phone']         = (int) $orderDetails['mobile']; // Type Casting: https://www.php.net/manual/en/language.types.type-juggling.php#language.types.typecasting    // 'shipping_phone'         is the Shiprocket's JSON request key/name, while 'mobile'     is our `orders` table column name

        foreach ($orderDetails['order_items'] as $key => $item) {                         // 'order_items'   is the Shiprocket's JSON request key/name    // 'order_items' is the Relationship method name in Order.php model    // $key    denotes the 1st order, 2nd order, 3rd order, ...etc
            $orderDetails['order_items'][$key]['name']          = $item['product_name'];  // 'name'          is the Shiprocket's JSON request key/name, while 'product_name'  is our `orders_products` table column name
            $orderDetails['order_items'][$key]['sku']           = $item['product_code'];  // 'sku'           is the Shiprocket's JSON request key/name, while 'product_code'  is our `orders_products` table column name
            $orderDetails['order_items'][$key]['units']         = $item['product_qty'];   // 'units'         is the Shiprocket's JSON request key/name, while 'product_qty'   is our `orders_products` table column name
            $orderDetails['order_items'][$key]['selling_price'] = $item['product_price']; // 'selling_price' is the Shiprocket's JSON request key/name, while 'product_price' is our `orders_products` table column name
            $orderDetails['order_items'][$key]['discount']      = '';                     // 'discount'      is the Shiprocket's JSON request key/name
            $orderDetails['order_items'][$key]['tax']           = '';                     // 'tax'           is the Shiprocket's JSON request key/name
            $orderDetails['order_items'][$key]['hsn']           = '';                     // 'hsn'           is the Shiprocket's JSON request key/name
        }

        // $orderDetails['payment_method']      = '';                           // 'payment_method'      is the Shiprocket's JSON request key/name
        // $orderDetails['payment_method']      = 'prepaid';
        $orderDetails['shipping_charges']    = 0;                            // 'shipping_charges'    is the Shiprocket's JSON request key/name
        $orderDetails['giftwrap_charges']    = 0;                            // 'giftwrap_charges'    is the Shiprocket's JSON request key/name
        $orderDetails['transaction_charges'] = 0;                            // 'transaction_charges' is the Shiprocket's JSON request key/name
        $orderDetails['total_discount']      = 0;                            // 'total_discount'      is the Shiprocket's JSON request key/name
        $orderDetails['sub_total']           = $orderDetails['grand_total']; // 'sub_total'           is the Shiprocket's JSON request key/name, while 'grand_total' is our `orders` table column name
        $orderDetails['length']              = 1;                            // 'length'              is the Shiprocket's JSON request key/name
        $orderDetails['breadth']             = 1;                            // 'breadth'             is the Shiprocket's JSON request key/name
        $orderDetails['height']              = 1;                            // 'height'              is the Shiprocket's JSON request key/name
        $orderDetails['weight']              = 1;                            // 'weight'              is the Shiprocket's JSON request key/name


        // dd($orderDetails);
        
        // Shiprocket API 호출 전 테스트를 위해 데이터를 JSON으로 변환
        $orderDetails = json_encode($orderDetails);
        // dd($orderDetails);



        // PHP cURL을 사용하여 Shiprocket API로 주문 전송
        // 1단계: 액세스 토큰 생성
        $c = curl_init(); // Initialize a cURL session (The cURL Handle)
        $url = 'https://apiv2.shiprocket.in/v1/external/auth/login'; // Shiprocket API endpoint (URL/link) to generate an Access Token: https://apiv2.shiprocket.in/v1/external/auth/login . Check: https://apidocs.shiprocket.in/#c148860e-26ab-4737-a8be-23589f681b03

        // 참고: curl_setopt_array()를 사용하면 여러 설정을 한 번에 할 수 있습니다.
        curl_setopt($c, CURLOPT_URL, $url); // The URL to fetch. This can also be set when initializing a session with curl_init()    // curl_setopt(): Set an option for a cURL transfer
        curl_setopt($c, CURLOPT_POST, 1); // From the Shiprocket API Documentation, the Access Token generation API endpoint is accesses through a 'POST' HTTP request    // true (i.e. 1) to do a regular HTTP POST. This POST is the normal application/x-www-form-urlencoded kind, most commonly used by HTML forms.
        curl_setopt($c, CURLOPT_POSTFIELDS, 'email=stackdevelopers2@gmail.com&password=123456'); // We pass in our POST fields required for the Authentication API to generate the Access Token: our Shiprocket E-mail and Password    // The full data to post in a HTTP "POST" operation.    // Note: Passing an array (i.e.     [ 'name' => 'John', 'age' => 23 ]     ) to CURLOPT_POSTFIELDS will encode the data as "multipart/form-data", while passing a URL-encoded string (e.g.     "name=John&age=23"     ) will encode the data as application/x-www-form-urlencoded. Check     https://www.php.net/manual/en/function.curl-setopt.php#:~:text=Passing%20an%20array%20to%20CURLOPT_POSTFIELDS%20will%20encode%20the%20data%20as%20multipart/form%2Ddata%2C%20while%20passing%20a%20URL%2Dencoded%20string%20will%20encode%20the%20data%20as%20application/x%2Dwww%2Dform%2Durlencoded.     AND     https://www.php.net/manual/en/function.curl-setopt.php#:~:text=If%20value%20is%20an%20array%2C%20the%20Content%2DType%20header%20will%20be%20set%20to%20multipart/form%2Ddata
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1); // true (i.e. 1) to return the transfer as a string of the return value of curl_exec() instead of outputting it directly.

        $server_output = curl_exec($c); // 서버로부터 받은 JSON 응답 (액세스 토큰 포함)
        // dd($server_output); // The server's JSON response (the generated Access Token)
        /*
            Server's JSON Response Example:

            "{
                "id":1621230,"first_name":"API","last_name":"USER","email":"stackdevelopers2@gmail.com","company_id":1595375,"created_at":"2021-07-06 20:56:12","token":"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjE2MjEyMzAsImlzcyI6Imh0dHBzOi8vYXBpdjIuc2hpcHJvY2tldC5pbi92MS9leHRlcm5hbC9hdXRoL2xvZ2luIiwiaWF0IjoxNjgxNjcyOTcwLCJleHAiOjE2ODI1MzY5NzAsIm5iZiI6MTY4MTY3Mjk3MCwianRpIjoiZlpSazV0MFpneEQ4RzNTZSJ9.rOwcxnZzfsEg0pCuSNmKAV_aPVcMm2ohSjBWJhUIk5I"
            }"
        */

        curl_close($c); // Close a cURL session


        // Convert the server response from JSON to PHP array
        $server_output = json_decode($server_output, true); // https://www.php.net/manual/en/function.json-decode.php#:~:text=RFC%207159.-,associative,-When%20true%2C%20JSON
        // dd($server_output); // The server's PHP array response (the generated Access Token)
        /*
            Server's PHP array Response Example:

            [
                "id"         => 1621230,
                "first_name" => "API",
                "last_name"  => "USER",
                "email"      => "stackdevelopers2@gmail.com",
                "company_id" => 1595375,
                "created_at" => "2021-07-06 20:56:12",
                "token"      => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjE2MjEyMzAsImlzcyI6Imh0dHBzOi8vYXBpdjIuc2hpcHJvY2tldC5pbi92MS9leHRlcm5hbC9hdXRoL2xvZ2luIiwiaWF0IjoxNjgxNjczNjY2LCJ"
            ]
        */

        // 2단계: 주문 정보를 Shiprocket API로 전송 (주문 생성)
        $url = 'https://apiv2.shiprocket.in/v1/external/orders/create/adhoc'; // Shiprocket API endpoint (URL/link) to create an order: https://apiv2.shiprocket.in/v1/external/orders/create/adhoc . Check: https://apidocs.shiprocket.in/#247e58f3-37f3-4dfb-a4bb-b8f6ab6d41ec
        $c = curl_init($url); // Initialize a cURL session (The cURL Handle)

        curl_setopt($c, CURLOPT_POST, 1); // From the Shiprocket API Documentation, the Create an Order API endpoint is accesses through a 'POST' HTTP request    // true (i.e. 1) to do a regular HTTP POST. This POST is the normal application/x-www-form-urlencoded kind, most commonly used by HTML forms.
        curl_setopt($c, CURLOPT_POSTFIELDS, $orderDetails); // We pass in our POST fields required for creating the Order in Shiprocket (We pass in our Order Details that we've fetched earlier from our database tables: `orders` and `orders_products`)    // The full data to post in a HTTP "POST" operation.    // Note: Passing an array (i.e.     [ 'name' => 'John', 'age' => 23 ]     ) to CURLOPT_POSTFIELDS will encode the data as "multipart/form-data", while passing a URL-encoded string (e.g.     "name=John&age=23"     ) will encode the data as application/x-www-form-urlencoded. Check     https://www.php.net/manual/en/function.curl-setopt.php#:~:text=Passing%20an%20array%20to%20CURLOPT_POSTFIELDS%20will%20encode%20the%20data%20as%20multipart/form%2Ddata%2C%20while%20passing%20a%20URL%2Dencoded%20string%20will%20encode%20the%20data%20as%20application/x%2Dwww%2Dform%2Durlencoded.     AND     https://www.php.net/manual/en/function.curl-setopt.php#:~:text=If%20value%20is%20an%20array%2C%20the%20Content%2DType%20header%20will%20be%20set%20to%20multipart/form%2Ddata
        curl_setopt($c, CURLOPT_RETURNTRANSFER, 1); // true (i.e. 1) to return the transfer as a string of the return value of curl_exec() instead of outputting it directly.
        curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0); // 2 to verify that a Common Name field or a Subject Alternate Name field in the SSL peer certificate matches the provided hostname. 0 to not check the names. 1 should not be used. In production environments the value of this option should be kept at 2 (default value).
        curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0); // false (i.e. 0) to stop cURL from verifying the peer's certificate. Alternate certificates to verify against can be specified with the CURLOPT_CAINFO option or a certificate directory can be specified with the CURLOPT_CAPATH option.
        curl_setopt($c, CURLOPT_HTTPHEADER, [ 
            'Content-Type: application/json',
            'Authorization: Bearer ' . $server_output['token'] . '' 
        ]);

        $result = curl_exec($c); // 서버로부터 받은 JSON 응답
        // dd($result); // The server's JSON response
        /*
            Server's JSON Response Example:

            "{
                "order_id":335443365,"shipment_id":334820645,"status":"NEW","status_code":1,"onboarding_completed_now":0,"awb_code":"","courier_company_id":"","courier_name""
            }"
        */

        curl_close($c); // Close a cURL session

        
        // 서버 응답을 JSON에서 PHP 배열로 변환
        $result = json_decode($result, true); 
        // dd($result); // The server's PHP array response (the generated Access Token)
        /*
            Server's PHP array Response Example:

            [
                "order_id"                 => 335443365,
                "shipment_id"              => 334820645,
                "status"                   => "NEW",
                "status_code"              => 1,
                "onboarding_completed_now" => 0,
                "awb_code"                 => "",
                "courier_company_id"       => "",
                "courier_name"             => ""
            ]
        */


        // 주문이 Shiprocket으로 성공적으로 전송된 경우, orders 테이블의 is_pushed 컬럼을 1로 업데이트
        if (isset($result['status_code']) && $result['status_code'] == 1) {
            Order::where('id', $order_id)->update(['is_pushed' => 1]); // $order_id was pass in from the early BEGINNING from the pushOrder() method in API/APIController.php

            $status  = true;
            $message = '주문이 성공적으로 전송되었습니다.';

        } else { // 전송 실패 시
            $status  = false;
            $message = '주문 전송에 실패했습니다. 관리자에게 문의하세요.';
        }


        return [
            'status'  => $status,
            'message' => $message
        ];
    }

}