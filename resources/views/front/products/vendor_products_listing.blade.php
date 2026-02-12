{{-- 모든 벤더 제품 표시 --}} {{-- 이 파일은 front/products/vendor_listing.blade.php에 'include' 됩니다. --}}


<!-- Row-of-Product-Container -->
<div class="row product-container grid-style">



    @foreach ($vendorProducts as $product)
        <div class="product-item col-lg-4 col-md-6 col-sm-6">
            <div class="item">
                <div class="image-container">
                    <a class="item-img-wrapper-link" href="{{ url('product/' . $product['id']) }}">



                        @php
                            $product_image_path = 'front/images/product_images/small/' . $product['product_image'];
                        @endphp


                        @if (!empty($product['product_image']) && file_exists($product_image_path)) {{-- 제품 이미지가 데이터베이스 테이블과
                            파일 시스템(서버) 모두에 존재하는 경우 --}}
                            <img class="img-fluid" src="{{ asset($product_image_path) }}" alt="Product">
                        @else {{-- 더미 이미지 표시 --}}
                            <img class="img-fluid" src="{{ asset('front/images/product_images/small/no-image.png') }}"
                                alt="Product">
                        @endif



                    </a>
                    <div class="item-action-behaviors">
                        <a class="item-quick-look" data-toggle="modal" href="#quick-view">Quick Look</a>
                        <a class="item-mail" href="javascript:void(0)">Mail</a>
                        <a class="item-addwishlist" href="javascript:void(0)">Add to Wishlist</a>
                        <a class="item-addCart" href="javascript:void(0)">Add to Cart</a>
                    </div>
                </div>
                <div class="item-content">
                    <div class="what-product-is">
                        <ul class="bread-crumb">
                            <li class="has-separator">
                                <a href="shop-v1-root-category.html">{{ $product['product_code'] }}</a>
                            </li>
                            <li class="has-separator">
                                <a href="listing.html">{{ $product['product_color'] }}</a>
                            </li>
                            <li>
                                <a href="listing.html">{{ $product['brand']['name'] }}</a>
                            </li>
                        </ul>
                        <h6 class="item-title">
                            <a href="single-product.html">{{ $product['product_name'] }}</a>
                        </h6>
                        <div class="item-description">
                            <p>{{ $product['description'] }}</p>
                        </div>
                    </div>



                    {{-- 제품의 최종 가격을 결정하기 위해 Product.php 모델의 정적 getDiscountPrice() 메서드를 호출합니다. 제품은 '카테고리' 할인 또는 '제품' 할인 두 가지로
                    인해 할인을 받을 수 있기 때문입니다. --}}
                    @php
                        $getDiscountPrice = \App\Models\Product::getDiscountPrice($product['id']);
                    @endphp


                    @if ($getDiscountPrice > 0) {{-- 가격에 할인이 있는 경우, 할인 전 가격(원래 가격)과 할인 후 가격(새 가격)을 표시합니다. --}}
                        <div class="price-template">
                            <div class="item-new-price">
                                Rs . {{ $getDiscountPrice }}
                            </div>
                            <div class="item-old-price">
                                Rs . {{ $product['product_price'] }}
                            </div>
                        </div>
                    @else {{-- 가격에 할인이 없는 경우, 원래 가격을 표시합니다. --}}
                        <div class="price-template">
                            <div class="item-new-price">
                                Rs . {{ $product['product_price'] }}
                            </div>
                        </div>
                    @endif



                </div>




                @php
                    $isProductNew = \App\Models\Product::isProductNew($product['id'])
                @endphp
                @if ($isProductNew == 'Yes')
                    <div class="tag new">
                        <span>NEW</span>
                    </div>
                @endif



            </div>
        </div>
    @endforeach



</div>
<!-- Row-of-Product-Container /- -->