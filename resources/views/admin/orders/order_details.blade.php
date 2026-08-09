
{{-- 이 페이지는 Admin/OrderController.php 내부의 orderDetails() 메서드에 의해 렌더링됩니다. --}}
@extends('admin.layout.layout')


@section('content')
    <div class="main-panel">
        <div class="content-wrapper">


            {{-- 라라벨 유효성 검사 오류 표시: https://laravel.com/docs/9.x/validation#quick-displaying-the-validation-errors --}}    
            {{-- 세션에 항목이 존재하는지 확인(has() 메서드 사용): https://laravel.com/docs/9.x/session#determining-if-an-item-exists-in-the-session --}}
            @if (Session::has('error_message')) <!-- AdminController.php, updateAdminPassword() 메서드 확인 -->
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <strong>Error:</strong> {{ Session::get('error_message') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif



            {{-- 라라벨 유효성 검사 오류 표시: https://laravel.com/docs/9.x/validation#quick-displaying-the-validation-errors --}}    
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{-- <strong>Error:</strong> {{ Session::get('error_message') }} --}}

                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach

                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif


            {{-- 유효성 검사 오류 표시: https://laravel.com/docs/9.x/validation#quick-displaying-the-validation-errors 및 https://laravel.com/docs/9.x/blade#validation-errors --}} 
            {{-- 세션에 항목이 존재하는지 확인(has() 메서드 사용): https://laravel.com/docs/9.x/session#determining-if-an-item-exists-in-the-session --}}
            {{-- 관리자 비밀번호 업데이트 성공 시 Bootstrap 성공 메시지: --}}
            {{-- 성공 메시지 표시 --}}
            @if (Session::has('success_message')) <!-- Front/VendorController.php의 vendorRegister() 메서드 확인 -->
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <strong>Success:</strong> {{ Session::get('success_message') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif



            <div class="row">
                <div class="col-md-12 grid-margin">
                    <div class="row">
                        <div class="col-12 col-xl-8 mb-4 mb-xl-0">
                            <h3 class="font-weight-bold">Order Details</h3>
                            <h6 class="font-weight-normal mb-0"><a href="{{ url('admin/orders') }}">Back to Orders</a></h6>
                        </div>
                        <div class="col-12 col-xl-4">
                            <div class="justify-content-end d-flex">
                                <div class="dropdown flex-md-grow-1 flex-xl-grow-0">
                                    <button class="btn btn-sm btn-light bg-white dropdown-toggle" type="button" id="dropdownMenuDate2" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                    <i class="mdi mdi-calendar"></i> Today (10 Jan 2021)
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuDate2">
                                        <a class="dropdown-item" href="{{ url()->current() }}">January - March</a>
                                        <a class="dropdown-item" href="{{ url()->current() }}">March - June</a>
                                        <a class="dropdown-item" href="{{ url()->current() }}">June - August</a>
                                        <a class="dropdown-item" href="{{ url()->current() }}">August - November</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Order Details</h4>
                            <div class="form-group" style="height: 15px">
                                <label style="font-weight: 550">Order ID: </label>
                                <label>#{{ $orderDetails['id'] }}</label>
                            </div>
                            <div class="form-group" style="height: 15px">
                                <label style="font-weight: 550">Order Date: </label>
                                <label>{{ date('Y-m-d h:i:s', strtotime($orderDetails['created_at'])) }}</label>
                            </div>
                            <div class="form-group" style="height: 15px">
                                <label style="font-weight: 550">Order Status: </label>
                                <label>{{ $orderDetails['order_status'] }}</label>
                            </div>
                            <div class="form-group" style="height: 15px">
                                <label style="font-weight: 550">Order Total: </label>
                                <label>EGP{{ $orderDetails['grand_total'] }}</label>
                            </div>
                            <div class="form-group" style="height: 15px">
                                <label style="font-weight: 550">Shipping Charges: </label>
                                <label>EGP{{ $orderDetails['shipping_charges'] }}</label>
                            </div>

                            @if (!empty($orderDetails['coupon_code']))
                                <div class="form-group" style="height: 15px">
                                    <label style="font-weight: 550">Coupon Code: </label>
                                    <label>{{ $orderDetails['coupon_code'] }}</label>
                                </div>
                                <div class="form-group" style="height: 15px">
                                    <label style="font-weight: 550">Coupon Amount: </label>
                                    <label>EGP{{ $orderDetails['coupon_amount'] }}</label>
                                </div>                                
                            @endif

                            <div class="form-group" style="height: 15px">
                                <label style="font-weight: 550">Payment Method: </label>
                                <label>{{ $orderDetails['payment_method'] }}</label>
                            </div>
                            <div class="form-group" style="height: 15px">
                                <label style="font-weight: 550">Payment Gateway: </label>
                                <label>{{ $orderDetails['payment_gateway'] }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Customer Details</h4>
                            <div class="form-group" style="height: 15px">
                                <label style="font-weight: 550">Name: </label>
                                <label>{{ $userDetails['name'] }}</label>
                            </div>

                            @if (!empty($userDetails['address']))
                                <div class="form-group" style="height: 15px">
                                    <label style="font-weight: 550">Address: </label>
                                    <label>{{ $userDetails['address'] }}</label>
                                </div>
                            @endif

                            @if (!empty($userDetails['city']))
                                <div class="form-group" style="height: 15px">
                                    <label style="font-weight: 550">City: </label>
                                    <label>{{ $userDetails['city'] }}</label>
                                </div>
                            @endif

                            @if (!empty($userDetails['state']))
                                <div class="form-group" style="height: 15px">
                                    <label style="font-weight: 550">State: </label>
                                    <label>{{ $userDetails['state'] }}</label>
                                </div>
                            @endif
                            
                            @if (!empty($userDetails['country']))
                                <div class="form-group" style="height: 15px">
                                    <label style="font-weight: 550">Country: </label>
                                    <label>{{ $userDetails['country'] }}</label>
                                </div>
                            @endif
                            
                            @if (!empty($userDetails['pincode']))
                                <div class="form-group" style="height: 15px">
                                    <label style="font-weight: 550">Pincode: </label>
                                    <label>{{ $userDetails['pincode'] }}</label>
                                </div>
                            @endif

                            <div class="form-group" style="height: 15px">
                                <label style="font-weight: 550">Mobile: </label>
                                <label>{{ $userDetails['mobile'] }}</label>
                            </div>
                            <div class="form-group" style="height: 15px">
                                <label style="font-weight: 550">Email: </label>
                                <label>{{ $userDetails['email'] }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Delivery Address</h4>
                            <div class="form-group" style="height: 15px">
                                <label style="font-weight: 550">Name: </label>
                                <label>{{ $orderDetails['name'] }}</label>
                            </div>

                            @if (!empty($orderDetails['address']))
                                <div class="form-group" style="height: 15px">
                                    <label style="font-weight: 550">Address: </label>
                                    <label>{{ $orderDetails['address'] }}</label>
                                </div>
                            @endif

                            @if (!empty($orderDetails['city']))
                                <div class="form-group" style="height: 15px">
                                    <label style="font-weight: 550">City: </label>
                                    <label>{{ $orderDetails['city'] }}</label>
                                </div>
                            @endif

                            @if (!empty($orderDetails['state']))
                                <div class="form-group" style="height: 15px">
                                    <label style="font-weight: 550">State: </label>
                                    <label>{{ $orderDetails['state'] }}</label>
                                </div>
                            @endif
                            
                            @if (!empty($orderDetails['country']))
                                <div class="form-group" style="height: 15px">
                                    <label style="font-weight: 550">Country: </label>
                                    <label>{{ $orderDetails['country'] }}</label>
                                </div>
                            @endif
                            
                            @if (!empty($orderDetails['pincode']))
                                <div class="form-group" style="height: 15px">
                                    <label style="font-weight: 550">Pincode: </label>
                                    <label>{{ $orderDetails['pincode'] }}</label>
                                </div>
                            @endif

                            <div class="form-group" style="height: 15px">
                                <label style="font-weight: 550">Mobile: </label>
                                <label>{{ $orderDetails['mobile'] }}</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Update Order Status</h4>  {{-- 'vendor'가 아닌 'admin'만 결정 --}}

                            {{-- 'admin'만 일반 "주문 상태 업데이트" 기능을 허용하고, 'vendor'는 제한('vendor'는 이 페이지 하단의 주문한 제품 항목 상태만 업데이트 가능) --}} 
                            @if (Auth::guard('admin')->user()->type != 'vendor') {{-- 인증된/로그인한 사용자가 'admin'인 경우 "주문 상태 업데이트" 기능 허용 --}} {{-- 특정 Guard 인스턴스 액세스: https://laravel.com/docs/9.x/authentication#accessing-specific-guard-instances --}} {{-- 인증된 사용자 검색: https://laravel.com/docs/9.x/authentication#retrieving-the-authenticated-user --}}
                                
                                {{-- 참고: 'order_statuses' 테이블에는 'orders' 테이블에서 'admin'만 업데이트할 수 있는 모든 종류의 주문 상태(예: 보류 중, 진행 중, 배송됨, 취소됨 등)가 포함되어 있습니다. 'order_statuses' 테이블의 'name' 열은 'New', 'Pending', 'Canceled', 'In Progress', 'Shipped', 'Partially Shipped', 'Delivered', 'Partially Delivered', 'Paid'일 수 있습니다. 'Partially Shipped': 하나의 주문에 여러 공급업체의 제품이 있고 한 공급업체는 제품을 고객에게 배송했지만 다른 공급업체(들)는 배송하지 않은 경우! 'Partially Delivered': 하나의 주문에 여러 공급업체의 제품이 있고 한 공급업체는 제품을 고객에게 배송하고 완료했지만 다른 공급업체(들)는 배송하지 않은 경우!    // 'order_item_statuses' 테이블에는 'orders_products' 테이블에서 'vendor'와 'admin' 모두 업데이트할 수 있는 모든 종류의 주문 상태(예: 보류 중, 진행 중, 배송됨, 취소됨 등)가 포함되어 있습니다. --}}
                                <form action="{{ url('admin/update-order-status') }}" method="post">  {{-- 'vendor'가 아닌 'admin'만 결정. 이는 'vendor'와 'admin' 모두 업데이트할 수 있는 '주문 항목 상태'와 대조됩니다. --}}
                                    @csrf {{-- CSRF 요청 방지: https://laravel.com/docs/9.x/csrf#preventing-csrf-requests --}}

                                    <input type="hidden" name="order_id" value="{{ $orderDetails['id'] }}">

                                    <select name="order_status" id="order_status" required>
                                        <option value="" selected>Select</option>
                                        @foreach ($orderStatuses as $status)
                                            <option value="{{ $status['name'] }}"  @if (!empty($orderDetails['order_status']) && $orderDetails['order_status'] == $status['name']) selected @endif>{{ $status['name'] }}</option>
                                        @endforeach
                                    </select>

                                    {{-- // 참고: 배송 프로세스에는 "수동"과 "자동"의 두 가지 유형이 있습니다. "수동"은 소규모 비즈니스와 같은 경우로, 택배 기사가 배송을 위해 주문을 픽업하기 위해 소유자 창고에 도착하고 소규모 비즈니스 소유자가 택배 기사로부터 배송 세부 정보(예: 택배사 이름, 운송장 번호 등)를 가져와 관리자 패널의 "주문 상태 업데이트" 섹션('admin' 수행) 또는 "항목 상태 업데이트" 섹션('vendor' 또는 'admin' 수행)에 직접 입력하는 경우입니다(admin/orders/order_details.blade.php에서). "자동" 배송 프로세스를 사용하면 타사 API를 통합하고 주문이 배송 파트너에게 직접 전달되며 업데이트는 택배사 측에서 이루어지고 주문은 고객에게 자동으로 배송됩니다. --}}
                                    <input type="text" name="courier_name"    id="courier_name"    placeholder="Courier Name">    {{-- 이 입력 필드는 'Shipped' <option>이 선택된 경우에만 나타납니다. admin/js/custom.js 확인 --}}
                                    <input type="text" name="tracking_number" id="tracking_number" placeholder="Tracking Number"> {{-- 이 입력 필드는 'Shipped' <option>이 선택된 경우에만 나타납니다. admin/js/custom.js 확인 --}}

                                    <button type="submit">Update</button>
                                </form>
                                <br>

                                {{-- admin/orders/order_details.blade.php에 "주문 상태 업데이트" 기록/로그 표시     --}}
                                @foreach ($orderLog as $key => $log)
                                    @php
                                        // echo '<pre>', var_dump($log), '</pre>';
                                        // echo '<pre>', var_dump($log['orders_products']), '</pre>';
                                        // echo '<pre>', var_dump($key), '</pre>';
                                        // echo '<pre>', var_dump($log['orders_products'][$key]), '</pre>';
                                        // echo '<pre>', var_dump($log['orders_products'][$key]['product_code']), '</pre>';
                                    @endphp

                                    <strong>{{ $log['order_status'] }}</strong>

                                    {{-- Shiprocket API 통합 --}}
                                    @if ($orderDetails['is_pushed'] == 1) {{-- 주문이 Shiprocket으로 푸시된 경우 상태 표시 --}}
                                        <span style="color: green">(Order Pushed to Shiprocket)</span>
                                    @endif

                                    {{-- 참고: 배송 프로세스에는 "수동"과 "자동"의 두 가지 유형이 있습니다. "수동"은 소규모 비즈니스와 같은 경우로, 택배 기사가 배송을 위해 주문을 픽업하기 위해 소유자 창고에 도착하고 소규모 비즈니스 소유자가 택배 기사로부터 배송 세부 정보(예: 택배사 이름, 운송장 번호 등)를 가져와 관리자 패널의 "주문 상태 업데이트" 섹션('admin' 수행) 또는 "항목 상태 업데이트" 섹션('vendor' 또는 'admin' 수행)에 직접 입력하는 경우입니다(admin/orders/order_details.blade.php에서). "자동" 배송 프로세스를 사용하면 타사 API를 통합하고 주문이 배송 파트너에게 직접 전달되며 업데이트는 택배사 측에서 이루어지고 주문은 고객에게 자동으로 배송됩니다. --}}

                                    {{-- admin/orders/order_details.blade.php의 "주문 상태 업데이트" 섹션에서 미리 본 주문 상태가 "항목 상태 업데이트" 섹션(`vendor` 또는 `admin`이 업데이트 가능)에서 업데이트되었는지(`order_item_id` 열이 0이 아닌 경우("주문 상태 업데이트" 섹션에서 `admin`만 업데이트하는 경우 0임)) 또는 "주문 상태 업데이트" 섹션(`admin`만 업데이트 가능)에서 업데이트되었는지 확인합니다. Admin/OrderController.php의 updateOrderItemStatus() 메서드 확인     --}}
                                    @if (isset($log['order_item_id']) && $log['order_item_id'] > 0) {{-- "항목 상태" 섹션이 'vendor' 또는 'admin'에 의해 업데이트된 경우, `orders_logs` 테이블의 `order_item_id` 열은 `orders_products` 테이블의 `id` 열을 참조(외래 키)하고, 그렇지 않으면 0 값을 가집니다('admin'인 경우). Admin/OrderController.php의 updateOrderItemStatus() 메서드 확인 --}}
                                        @php
                                            $getItemDetails = \App\Models\OrdersLog::getItemDetails($log['order_item_id']);
                                        @endphp
                                        - for item {{ $getItemDetails['product_code'] }}

                                        @if (!empty($getItemDetails['courier_name']))
                                            <br>
                                            <span>Courier Name: {{ $getItemDetails['courier_name'] }}</span>
                                        @endif

                                        @if (!empty($getItemDetails['tracking_number']))
                                            <br>
                                            <span>Tracking Number: {{ $getItemDetails['tracking_number'] }}</span>
                                        @endif

                                    @endif

                                    <br>
                                    {{ date('Y-m-d h:i:s', strtotime($log['created_at'])) }}
                                    <br>
                                    <hr>
                                @endforeach

                            @else {{-- 인증된/로그인한 사용자가 'vendor'인 경우 "주문 상태 업데이트" 기능을 제한합니다. --}}
                                This feature is restricted.
                            @endif

                        </div>
                    </div>
                </div>

                
                <div class="col-md-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Ordered Products</h4>

                            <div class="table-responsive">
                                {{-- Order products info table --}}
                                <table class="table table-striped table-borderless">
                                    <tr class="table-danger">
                                        <th>Product Image</th>
                                        <th>Code</th>
                                        <th>Name</th>
                                        <th>Size</th>
                                        <th>Color</th>
                                        <th>Unit Price</th> 
                                        <th>Product Qty</th>
                                        <th>Total Price</th> 

                                        
                                        @if (\Illuminate\Support\Facades\Auth::guard('admin')->user()->type != 'vendor') {{-- 인증된/로그인한 사용자가 'vendor'가 아닌 'admin', 'superadmin' 또는 'subadmin'인 경우 --}} {{-- 특정 Guard 인스턴스 액세스: https://laravel.com/docs/9.x/authentication#accessing-specific-guard-instances --}}
                                            <th>Product by</th>
                                        @endif

                                        
                                        
                                        <th>Commission</th> {{-- 웹사이트 소유자에게 판매된 모든 제품에 대해 벤더 수수료율을 지불해야 합니다. --}}
                                        <th>Final Amount</th> {{-- 수수료율을 지불(공제)한 후 벤더의 수익 --}}

                                        <th>Item Status</th> {{-- 'vendor'와 'admin' 모두 업데이트할 수 있습니다. 이는 'admin'만 업데이트할 수 있는 '주문 상태 업데이트'와 대조됩니다. --}} 
                                        {{-- 참고: 'order_statuses' 테이블에는 'orders' 테이블에서 'admin'만 업데이트할 수 있는 모든 종류의 주문 상태(예: 보류 중, 진행 중, 배송됨, 취소됨 등)가 포함되어 있습니다. 'order_statuses' 테이블의 'name' 열은 'New', 'Pending', 'Canceled', 'In Progress', 'Shipped', 'Partially Shipped', 'Delivered', 'Partially Delivered', 'Paid'일 수 있습니다. 'Partially Shipped': 하나의 주문에 여러 공급업체의 제품이 있고 한 공급업체는 제품을 고객에게 배송했지만 다른 공급업체(들)는 배송하지 않은 경우! 'Partially Delivered': 하나의 주문에 여러 공급업체의 제품이 있고 한 공급업체는 제품을 고객에게 배송하고 완료했지만 다른 공급업체(들)는 배송하지 않은 경우!    // 'order_item_statuses' 테이블에는 'orders_products' 테이블에서 'vendor'와 'admin' 모두 업데이트할 수 있는 모든 종류의 주문 상태(예: 보류 중, 진행 중, 배송됨, 취소됨 등)가 포함되어 있습니다. --}}
                                    </tr>

                                    @foreach ($orderDetails['orders_products'] as $product)
                                        <tr>
                                            <td>
                                                @php
                                                    $getProductImage = \App\Models\Product::getProductImage($product['product_id']);
                                                @endphp

                                                <a target="_blank" href="{{ url('product/' . $product['product_id']) }}">
                                                    <img src="{{ asset('front/images/product_images/small/' . $getProductImage) }}">
                                                </a>
                                            </td>
                                            <td>{{ $product['product_code'] }}</td>
                                            <td>{{ $product['product_name'] }}</td>
                                            <td>{{ $product['product_size'] }}</td>
                                            <td>{{ $product['product_color'] }}</td>
                                            <td>{{ $product['product_price'] }}</td> 
                                            <td>{{ $product['product_qty'] }}</td>
                                            <td>
                                                
                                                @if ($product['vendor_id'] > 0) {{-- 제품이 'admin'이 아닌 'vendor'에 속하는 경우 --}}

                                                    
                                                    @if ($orderDetails['coupon_amount'] > 0) {{-- 쿠폰 코드가 사용된 경우 --}}

                                                        @if (\App\Models\Coupon::couponDetails($orderDetails['coupon_code'])['vendor_id'] > 0) {{-- 쿠폰 코드가 사용되었고 이 쿠폰 코드가 'admin'이 아닌 'vendor'에 속하는 경우('coupons' 테이블에서 'vendor_id' 열이 1이면 쿠폰 코드가 'admin'이 아닌 'vendor'에 의해 추가되었음을 의미하고, 'vendor_id' 열이 0이면 쿠폰 코드가 'vendor'가 아닌 'admin'에 의해 추가되었음을 의미함) --}}
                                                            @php
                                                                // dd(\App\Models\Coupon::couponDetails($orderDetails['coupon_code'])['vendor_id']);    
                                                            @endphp
                                                            
                                                        {{ $total_price = ($product['product_price'] * $product['product_qty']) - $item_discount }}
                                                        @else {{-- 쿠폰 코드가 사용되었고 이 쿠폰 코드가 'vendor'가 아닌 'admin'에 속하는 경우('coupons' 테이블에서 'vendor_id' 열이 1이면 쿠폰 코드가 'admin'이 아닌 'vendor'에 의해 추가되었음을 의미하고, 'vendor_id' 열이 0이면 쿠폰 코드가 'vendor'가 아닌 'admin'에 의해 추가되었음을 의미함) --}}
                                                            {{ $total_price = $product['product_price'] * $product['product_qty'] }}
                                                        @endif
                                                    
                                                    @else {{-- 쿠폰 코드가 사용되지 않은 경우 --}}
                                                        {{ $total_price = $product['product_price'] * $product['product_qty'] }}
                                                    @endif

                                                @else {{-- 제품이 'vendor'가 아닌 'admin'에 속하는 경우 --}}
                                                    {{ $total_price = $product['product_price'] * $product['product_qty'] }}
                                                @endif
                                            </td> {{-- 총 가격 = 단가 * 수량 --}} 

                                            
                                            @if (\Illuminate\Support\Facades\Auth::guard('admin')->user()->type != 'vendor') {{-- 인증된/로그인한 사용자가 'vendor'가 아닌 'admin', 'superadmin' 또는 'subadmin'인 경우 --}} {{-- 특정 Guard 인스턴스 액세스: https://laravel.com/docs/9.x/authentication#accessing-specific-guard-instances --}}
                                                @if ($product['vendor_id'] > 0) {{-- 제품이 'vendor'에 속하는 경우 --}}
                                                    <td>
                                                        <a href="/admin/view-vendor-details/{{ $product['admin_id'] }}" target="_blank">Vendor</a>
                                                    </td>
                                                @else
                                                    <td>Admin</td>
                                                @endif
                                            @endif

                                            
                                            
                                            @if ($product['vendor_id'] > 0) {{-- 제품이 'vendor'에 속하는 경우 --}}
                                                <td>{{ $commission = round($total_price * $product['commission'] / 100, 2) }}</td> 
                                                <td>{{ $total_price - $commission }}</td>
                                            @else
                                                <td>0</td>
                                                <td>{{ $total_price }}</td>
                                            @endif

                                            
                                            <td>
                                                
                                                {{-- 참고: 'order_statuses' 테이블에는 'orders' 테이블에서 'admin'만 업데이트할 수 있는 모든 종류의 주문 상태(예: 보류 중, 진행 중, 배송됨, 취소됨 등)가 포함되어 있습니다. 'order_statuses' 테이블의 'name' 열은 'New', 'Pending', 'Canceled', 'In Progress', 'Shipped', 'Partially Shipped', 'Delivered', 'Partially Delivered', 'Paid'일 수 있습니다. 'Partially Shipped': 하나의 주문에 여러 공급업체의 제품이 있고 한 공급업체는 제품을 고객에게 배송했지만 다른 공급업체(들)는 배송하지 않은 경우! 'Partially Delivered': 하나의 주문에 여러 공급업체의 제품이 있고 한 공급업체는 제품을 고객에게 배송하고 완료했지만 다른 공급업체(들)는 배송하지 않은 경우!    // 'order_item_statuses' 테이블에는 'orders_products' 테이블에서 'vendor'와 'admin' 모두 업데이트할 수 있는 모든 종류의 주문 상태(예: 보류 중, 진행 중, 배송됨, 취소됨 등)가 포함되어 있습니다. --}}
                                                <form action="{{ url('admin/update-order-item-status') }}" method="post">  {{-- 'vendor'와 'admin' 모두 업데이트할 수 있습니다. 이는 'admin'만 업데이트할 수 있는 '주문 상태 업데이트'와 대조됩니다. --}}
                                                    @csrf {{-- CSRF 요청 방지: https://laravel.com/docs/9.x/csrf#preventing-csrf-requests --}}

                                                    <input type="hidden" name="order_item_id" value="{{ $product['id'] }}">

                                                    <select id="order_item_status" name="order_item_status" required>
                                                        <option value="">Select</option>
                                                        @foreach ($orderItemStatuses as $status)
                                                            <option value="{{ $status['name'] }}"  @if (!empty($product['item_status']) && $product['item_status'] == $status['name']) selected @endif>{{ $status['name'] }}</option>
                                                        @endforeach
                                                    </select>

                                                    {{-- // 참고: 배송 프로세스에는 "수동"과 "자동"의 두 가지 유형이 있습니다. "수동"은 소규모 비즈니스와 같은 경우로, 택배 기사가 배송을 위해 주문을 픽업하기 위해 소유자 창고에 도착하고 소규모 비즈니스 소유자가 택배 기사로부터 배송 세부 정보(예: 택배사 이름, 운송장 번호 등)를 가져와 관리자 패널의 "주문 상태 업데이트" 섹션('admin' 수행) 또는 "항목 상태 업데이트" 섹션('vendor' 또는 'admin' 수행)에 직접 입력하는 경우입니다(admin/orders/order_details.blade.php에서). "자동" 배송 프로세스를 사용하면 타사 API를 통합하고 주문이 배송 파트너에게 직접 전달되며 업데이트는 택배사 측에서 이루어지고 주문은 고객에게 자동으로 배송됩니다. --}}
                                                    <input class="w160" type="text" name="item_courier_name"    id="item_courier_name"    placeholder="Item Courier Name"    @if (!empty($product['courier_name']))    value="{{ $product['courier_name'] }}"    @endif> {{-- 이 입력 필드는 'Shipped' <option>이 선택된 경우에만 나타납니다. admin/js/custom.js 확인 --}}
                                                    <input class="w160" type="text" name="item_tracking_number" id="item_tracking_number" placeholder="Item Tracking Number" @if (!empty($product['tracking_number'])) value="{{ $product['tracking_number'] }}" @endif> {{-- 이 입력 필드는 'Shipped' <option>이 선택된 경우에만 나타납니다. admin/js/custom.js 확인 --}}

                                                    <button type="submit">Update</button>
                                                </form>
                                            </td>
                                        </tr>         
                                    @endforeach
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>


        </div>
        <!-- content-wrapper ends -->

        {{-- Footer --}}
        @include('admin.layout.footer')
        <!-- partial -->
    </div>
@endsection
