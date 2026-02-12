{{-- 이 페이지는 Admin/OrderController.php 내부의 orders() 메서드에 의해 렌더링됩니다. --}}
@extends('admin.layout.layout')


@section('content')
    <div class="main-panel">
        <div class="content-wrapper">
            <div class="row">
                <div class="col-lg-12 grid-margin stretch-card">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Orders</h4>



                            <div class="table-responsive pt-3">
                                {{-- DataTable --}}
                                <table id="orders" class="table table-bordered"> {{-- DataTable에 여기 id 사용 --}}
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Order Date</th>
                                            <th>Customer Name</th>
                                            <th>Customer Email</th>
                                            <th>Ordered Products</th>
                                            <th>Order Amount</th>
                                            <th>Order Status</th>
                                            <th>Payment Method</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            // dd($orders); // 인증된/로그인한 사용자가 'vendor'(자신에게 속한 제품의 주문만 표시)인지 'admin'(모든 주문 표시)인지 확인
                                        @endphp
                                        @foreach ($orders as $order)
                                            @if ($order['orders_products']) {{-- 'vendor'가 주문한 제품이 있는 경우(vendor 제품이 주문된 경우)
                                                표시합니다. if 조건절 내부의 Admin/OrderController.php에 있는 orders() 메서드의 하위 쿼리를 사용하여 열성
                                                로드(eager load)를 어떻게 제한했는지 확인하세요 --}}
                                                <tr>
                                                    <td>{{ $order['id'] }}</td>
                                                    <td>{{ date('Y-m-d h:i:s', strtotime($order['created_at'])) }}</td>
                                                    <td>{{ $order['name'] }}</td>
                                                    <td>{{ $order['email'] }}</td>
                                                    <td>
                                                        @foreach ($order['orders_products'] as $product)
                                                            {{ $product['product_code'] }} ({{ $product['product_qty'] }})
                                                            <br>
                                                        @endforeach
                                                    </td>
                                                    <td>{{ $order['grand_total'] }}</td>
                                                    <td>{{ $order['order_status'] }}</td>
                                                    <td>{{ $order['payment_method'] }}</td>
                                                    <td>
                                                        <a title="View Order Details"
                                                            href="{{ url('admin/orders/' . $order['id']) }}">
                                                            <i style="font-size: 25px" class="mdi mdi-file-document"></i> {{--
                                                            Skydash 관리자 패널 템플릿의 아이콘 --}}
                                                        </a>
                                                        &nbsp;&nbsp;

                                                        {{-- HTML 송장 보기 --}}
                                                        <a title="View Order Invoice"
                                                            href="{{ url('admin/orders/invoice/' . $order['id']) }}"
                                                            target="_blank">
                                                            <i style="font-size: 25px" class="mdi mdi-printer"></i> {{-- Skydash 관리자
                                                            패널 템플릿의 아이콘 --}}
                                                        </a>
                                                        &nbsp;&nbsp;

                                                        {{-- PDF 송장 보기 --}}
                                                        <a title="Print PDF Invoice"
                                                            href="{{ url('admin/orders/invoice/pdf/' . $order['id']) }}"
                                                            target="_blank">
                                                            <i style="font-size: 25px" class="mdi mdi-file-pdf"></i> {{-- Skydash
                                                            관리자 패널 템플릿의 아이콘 --}}
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endif
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- content-wrapper ends -->
        <!-- partial:../../partials/_footer.html -->
        <footer class="footer">
            <div class="d-sm-flex justify-content-center justify-content-sm-between">
                <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">Copyright © 2022. All rights
                    reserved.</span>
            </div>
        </footer>
        <!-- partial -->
    </div>
@endsection