@extends('layouts.admin')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">상품 관리</div>
                    <ul class="dep">
                        <li>홈</li>
                        <li>상품 관리</li>
                        <li>상품 리스트</li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <div class="list_top1">
                            <div class="count">총 <strong>{{ count($products) }}</strong> 개</div>
                            <div class="r_btn_w ml20">
                                <a href="{{ url('admin/add-edit-product') }}" class="btn02">상품 추가</a>
                            </div>
                        </div>

                        @if (Session::has('success_message'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-top: 10px;">
                                <strong>성공:</strong> {{ Session::get('success_message') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="tb01 ovS">
                            <table class="table table-bordered">
                                <colgroup>
                                    <col width="60px">
                                    <col width="200px">
                                    <col width="120px">
                                    <col width="100px">
                                    <col width="120px">
                                    <col width="150px">
                                    <col width="150px">
                                    <col width="100px">
                                    <col width="80px">
                                    <col width="200px">
                                </colgroup>
                                <thead>
                                    <tr>
                                        <th>번호</th>
                                        <th>상품명</th>
                                        <th>상품코드</th>
                                        <th>색상</th>
                                        <th>이미지</th>
                                        <th>분류</th>
                                        <th>섹션</th>
                                        <th>등록자</th>
                                        <th>상태</th>
                                        <th>관리</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(count($products) > 0)
                                        @foreach ($products as $product)
                                            <tr>
                                                <td>{{ $product['id'] }}</td>
                                                <td style="text-align: left;">{{ $product['product_name'] }}</td>
                                                <td>{{ $product['product_code'] }}</td>
                                                <td>{{ $product['product_color'] }}</td>
                                                <td>
                                                    @if (!empty($product['product_image']))
                                                        <img style="width:80px; height:80px; object-fit: cover;"
                                                            src="{{ asset('front/images/product_images/small/' . $product['product_image']) }}">
                                                    @else
                                                        <img style="width:80px; height:80px; object-fit: cover;"
                                                            src="{{ asset('front/images/product_images/small/no-image.png') }}">
                                                    @endif
                                                </td>
                                                <td>{{ $product['category']['category_name'] ?? '카테고리 없음' }}</td>
                                                <td>{{ $product['section']['name'] ?? '섹션 없음' }}</td>
                                                <td>
                                                    @if ($product['admin_type'] == 'vendor')
                                                        <a target="_blank"
                                                            href="{{ url('admin/view-vendor-details/' . $product['admin_id']) }}">판매자</a>
                                                    @else
                                                        {{ $product['admin_type'] == 'admin' ? '관리자' : '서브관리자' }}
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($product['status'] == 1)
                                                        <a class="updateProductStatus" id="product-{{ $product['id'] }}"
                                                            product_id="{{ $product['id'] }}" href="javascript:void(0)">
                                                            <span style="color:green">활성</span>
                                                        </a>
                                                    @else
                                                        <a class="updateProductStatus" id="product-{{ $product['id'] }}"
                                                            product_id="{{ $product['id'] }}" href="javascript:void(0)">
                                                            <span style="color:red">비활성</span>
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a title="상품 수정" href="{{ url('admin/add-edit-product/' . $product['id']) }}"
                                                        class="btn02">수정</a>
                                                    <a title="속성 추가" href="{{ url('admin/add-edit-attributes/' . $product['id']) }}"
                                                        class="btn02">속성</a>
                                                    <a title="이미지 추가" href="{{ url('admin/add-images/' . $product['id']) }}"
                                                        class="btn02">이미지</a>
                                                    <a href="JavaScript:void(0)" class="btn02 confirmDelete" module="product"
                                                        moduleid="{{ $product['id'] }}" style="color: red;">삭제</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan="10" class="no_data">등록된 상품이 없습니다.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            // Update Product Status
            $(".updateProductStatus").click(function () {
                var status = $(this).text().trim();
                var product_id = $(this).attr("product_id");

                $.ajax({
                    type: 'post',
                    url: '/admin/update-product-status',
                    data: { status: status, product_id: product_id },
                    success: function (resp) {
                        if (resp['status'] == 0) {
                            $("#product-" + product_id).html("<span style='color:red'>비활성</span>");
                        } else if (resp['status'] == 1) {
                            $("#product-" + product_id).html("<span style='color:green'>활성</span>");
                        }
                    }, error: function () {
                        alert("오류가 발생했습니다.");
                    }
                });
            });

            // Confirm Delete
            $(".confirmDelete").click(function (e) {
                var module = $(this).attr('module');
                var moduleid = $(this).attr('moduleid');
                if (!confirm("정말로 삭제하시겠습니까?")) {
                    return false;
                }
                window.location.href = "/admin/delete-product/" + moduleid;
            });
        });
    </script>
@endsection