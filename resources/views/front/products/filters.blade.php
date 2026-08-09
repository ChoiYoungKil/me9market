{{-- 리스팅 페이지('listing.blade.php')에 포함되는 필터 사이드바입니다 --}}
@php

    $productFilters = \App\Models\ProductsFilter::productFilters(); // 모든(활성/enabled) 필터 가져오기
    // dd($productFilters);
@endphp



<!-- Shop-Left-Side-Bar-Wrapper -->
<div class="col-lg-3 col-md-3 col-sm-12">
    <!-- Fetch-Categories-from-Root-Category  -->
    <div class="fetch-categories">
        <h3 class="title-name">Browse Categories</h3>
        <!-- Level 1 -->
        <h3 class="fetch-mark-category">
            <a href="listing.html">T-Shirts
                <span class="total-fetch-items">(5)</span>
            </a>
        </h3>
        <ul>
            <li>
                <a href="shop-v3-sub-sub-category.html">Casual T-Shirts
                    <span class="total-fetch-items">(3)</span>
                </a>
            </li>
            <li>
                <a href="listing.html">Formal T-Shirts
                    <span class="total-fetch-items">(2)</span>
                </a>
            </li>
        </ul>
        <!-- //end Level 1 -->
        <!-- Level 2 -->
        <h3 class="fetch-mark-category">
            <a href="listing.html">Shirts
                <span class="total-fetch-items">(5)</span>
            </a>
        </h3>
        <ul>
            <li>
                <a href="shop-v3-sub-sub-category.html">Casual Shirts
                    <span class="total-fetch-items">(3)</span>
                </a>
            </li>
            <li>
                <a href="listing.html">Formal Shirts
                    <span class="total-fetch-items">(2)</span>
                </a>
            </li>
        </ul>
        <!-- //end Level 2 -->
    </div>
    <!-- Fetch-Categories-from-Root-Category  /- -->



    {{-- 검색 폼이 front/layout/header.blade.php에서 사용되지 않는 경우. 검색 폼을 사용하는 경우 필터는 숨겨지고 작동하지 않습니다. --}}
    @if (!isset($_REQUEST['search']))

        <!-- Filters -->
        <!-- Filter-Size -->


        {{-- Size, price, color, brand 등도 동적 필터이지만, 다른 동적 필터처럼 관리되지 않고 각각의 데이터베이스 테이블에서 관리됩니다. (예: 'size' 필터는
        `products_attributes` 테이블, 'color' 및 'price' 필터는 `products` 테이블, 'brand' 필터는 `brands` 테이블) --}}
        {{-- 첫째: 'size' 필터 (products_attributes 데이터베이스 테이블에서). URL에 따라 관련 제품 'size' 필터 값을 올바르게 표시합니다. (예: 'men' 카테고리의 경우
        small, medium, large, XL 등, 'mobiles' 카테고리의 경우 64GB-4GB, 128GB-6GB 등) --}}
        @php
            $getSizes = \App\Models\ProductsFilter::getSizes($url); // URL에 따라 제품 사이즈 가져오기 (관련된 적절한 'size' 필터 값을 표시하기 위해 (small, medium... 또는 64GB-4GB, 128GB-6GB... 등))    // $url은 Front/ProductsController.php에서 전달됩니다.
            // dd($getSizes);
        @endphp


        <div class="facet-filter-associates">
            <h3 class="title-name">Size</h3>
            <form class="facet-form" action="{{ url()->current() }}" method="get">
                <div class="associate-wrapper">




                    {{-- Size, price, color, brand 등도 동적 필터이지만, 다른 동적 필터처럼 관리되지 않고 각각의 데이터베이스 테이블에서 관리됩니다. (예: 'size' 필터는
                    `products_attributes` 테이블, 'color' 및 'price' 필터는 `products` 테이블, 'brand' 필터는 `brands` 테이블) --}}
                    {{-- 첫째: 'size' 필터 (products_attributes 데이터베이스 테이블에서). URL에 따라 관련 제품 'size' 필터 값을 올바르게 표시합니다. --}}
                    @foreach ($getSizes as $key => $size) {{-- URL에 따라 관련 제품 'size' 필터 값을 올바르게 표시 (예: 'men' 카테고리의 경우 small,
                        medium... 'mobiles' 카테고리의 경우 64GB-4GB...) --}}
                        <input type="checkbox" class="check-box size" id="size{{ $key }}" name="size[]" value="{{ $size }}">
                        {{-- 주의!!: "name" 속성의 대괄호 []를 확인하세요!! --}} {{-- 필터링을 위해 jQuery에서 사용할 수 있도록 $size를 'CSS 클래스'로 출력 --}}
                        {{-- 체크박스 <input> 필드의 "name" 속성에 대괄호 []를 사용하여 체크된 'size' 필터 값들이 배열로 제출되도록 합니다. 그렇지 않으면 AJAX가 <form> 제출
                            없이 <input> 값을 전송합니다. --}}
                            <label class="label-text" for="size{{ $key }}">{{ $size }}
                                {{-- <span class="total-fetch-items">(2)</span> --}}
                            </label>
                    @endforeach



                </div>
            </form>
        </div>
        <!-- Filter-Size -->




        <!-- Filter-Color -->


        {{-- Size, price, color, brand 등도 동적 필터이지만, 다른 동적 필터처럼 관리되지 않고 각각의 데이터베이스 테이블에서 관리됩니다. --}}
        {{-- 둘째: 'color' 필터 (products 데이터베이스 테이블에서). URL에 따라 관련 제품 'color' 필터 값을 올바르게 표시합니다. (예: 'men' 카테고리의 경우 red, blue...
        'mobiles' 카테고리의 경우 grey, black...) --}}
        @php
            $getColors = \App\Models\ProductsFilter::getColors($url); // URL에 따라 제품 색상 가져오기 (관련된 적절한 'color' 필터 값을 표시하기 위해)    // $url은 Front/ProductsController.php에서 전달됩니다.
            // dd($getColors);
        @endphp
        <div class="facet-filter-associates">
            <h3 class="title-name">Color</h3>
            <form class="facet-form" action="{{ url()->current() }}" method="get">
                <div class="associate-wrapper">




                    {{-- Size, price, color, brand 등도 동적 필터이지만, 다른 동적 필터처럼 관리되지 않고 각각의 데이터베이스 테이블에서 관리됩니다. --}}
                    {{-- 둘째: 'color' 필터 (products 데이터베이스 테이블에서). URL에 따라 관련 제품 'color' 필터 값을 올바르게 표시합니다. --}}
                    @foreach ($getColors as $key => $color) {{-- URL에 따라 관련 제품 'color' 필터 값을 올바르게 표시 --}}
                        <input type="checkbox" class="check-box color" id="color{{ $key }}" name="color[]" value="{{ $color }}">
                        {{-- 주의!!: "name" 속성의 대괄호 []를 확인하세요!! --}} {{-- 필터링을 위해 jQuery에서 사용할 수 있도록 $color를 'CSS 클래스'로 출력 --}}
                        {{-- 체크박스 <input> 필드의 "name" 속성에 대괄호 []를 사용하여 체크된 'color' 필터 값들이 배열로 제출되도록 합니다. --}}
                        <label class="label-text" for="color{{ $key }}">{{ $color }}
                            {{-- <span class="total-fetch-items">(1)</span> --}}
                        </label>
                    @endforeach



                </div>
            </form>
        </div>
        <!-- Filter-Color /- -->


        <!-- Filter-Brand -->


        {{-- Size, price, color, brand 등도 동적 필터이지만, 다른 동적 필터처럼 관리되지 않고 각각의 데이터베이스 테이블에서 관리됩니다. --}}
        {{-- 넷째: 'brand' 필터 (products 및 brands 데이터베이스 테이블에서). URL에 따라 관련 제품 'brand' 필터 값을 올바르게 표시합니다. (예: 'men' 카테고리의 경우 LC
        Waikiki, Concrete... 'mobiles' 카테고리의 경우 iPhone, Xiaomi...) --}}
        @php
            $getBrands = \App\Models\ProductsFilter::getBrands($url); // URL에 따라 제품 브랜드 가져오기 (관련된 적절한 'brand' 필터 값을 표시하기 위해)    // $url은 Front/ProductsController.php에서 전달됩니다.
            // dd($getColors);
        @endphp
        <div class="facet-filter-associates">
            <h3 class="title-name">Brand</h3>
            <form class="facet-form" action="{{ url()->current() }}" method="get">
                <div class="associate-wrapper">




                    {{-- Size, price, color, brand 등도 동적 필터이지만, 다른 동적 필터처럼 관리되지 않고 각각의 데이터베이스 테이블에서 관리됩니다. --}}
                    {{-- 넷째: 'brand' 필터 (products 및 brands 데이터베이스 테이블에서). URL에 따라 관련 제품 'brand' 필터 값을 올바르게 표시합니다. --}}
                    @foreach ($getBrands as $key => $brand) {{-- URL에 따라 관련 제품 'brand' 필터 값을 올바르게 표시 --}}
                        <input type="checkbox" class="check-box brand" id="brand{{ $key }}" name="brand[]"
                            value="{{ $brand['id'] }}"> {{-- 주의!!: "name" 속성의 대괄호 []를 확인하세요!! --}} {{-- 필터링을 위해 jQuery에서 사용할 수
                        있도록 $brand를 'CSS 클래스'로 출력 --}} {{-- 체크박스 <input> 필드의 "name" 속성에 대괄호 []를 사용하여 체크된 'brand' 필터 값들이 배열로
                        제출되도록 합니다. --}}
                        <label class="label-text" for="brand{{ $key }}">{{ $brand['name'] }}
                            {{-- <span class="total-fetch-items">(0)</span> --}}
                        </label>
                    @endforeach
                </div>
            </form>
        </div>
        <!-- Filter-Brand /- -->



        <!-- Filter-Price -->


        {{-- Size, price, color, brand 등도 동적 필터이지만, 다른 동적 필터처럼 관리되지 않고 각각의 데이터베이스 테이블에서 관리됩니다. --}}
        {{-- 셋째: 'price' 필터 (products 데이터베이스 테이블에서). URL에 따라 관련 제품 'price' 필터 값을 올바르게 표시합니다. --}}
        <div class="facet-filter-associates">
            <h3 class="title-name">Price</h3>
            <form class="facet-form" action="{{ url()->current() }}" method="get">
                <div class="associate-wrapper">


                    {{-- 셋째: 'price' 필터 --}}
                    @php
                        // 우리가 원하는 가격 범위 배열
                        $prices = array('0-1000', '1000-2000', '2000-5000', '5000-10000', '10000-100000');
                    @endphp

                    @foreach ($prices as $key => $price)
                        <input type="checkbox" class="check-box price" id="price{{ $key }}" name="price[]" value="{{ $price }}">
                        {{-- 주의!!: "name" 속성의 대괄호 []를 확인하세요!! --}} {{-- 필터링을 위해 jQuery에서 사용할 수 있도록 $price를 'CSS 클래스'로 출력 --}}
                        {{-- 체크박스 <input> 필드의 "name" 속성에 대괄호 []를 사용하여 체크된 'price' 필터 값들이 배열로 제출되도록 합니다. --}}
                        <label class="label-text" for="price{{ $key }}">EGP {{ $price }}
                        </label>
                    @endforeach
                </div>
            </form>
        </div>
        <!-- Filter-Price /- -->




        {{-- 동적 필터 --}}
        <!-- Filter -->
        @foreach ($productFilters as $filter) {{-- $productFilters는 이 파일 상단에서 전달됨 --}}
            @php
                // dd($filter);

                // `products_filters` 테이블의 모든 필터에 대해, filterAvailable() 메서드를 사용하여 필터의 `cat_ids`를 가져온 후, 현재 카테고리 ID가 ($categoryDetails 변수와 URL에 따라) 필터의 `cat_ids`에 존재하는지 확인합니다. 존재하면 필터를 표시하고, 아니면 표시하지 않습니다.
                $filterAvailable = \App\Models\ProductsFilter::filterAvailable($filter['id'], $categoryDetails['categoryDetails']['id']); // $categoryDetails는 Front/ProductsController의 listing() 메서드에서 전달되었습니다.
            @endphp

            @if ($filterAvailable == 'Yes') {{-- 현재 카테고리에 필터가 있는 경우 --}}
                @if (count($filter['filter_values']) > 0) {{-- 필터 값(`filter_value`)이 있는 필터(`filter_name`)만 표시합니다. (예: `Operating
                    System` 필터에 '4GB', '6GB' 등의 필터 값이 없으면 표시하지 않음) --}}
                    <div class="facet-filter-associates">
                        <h3 class="title-name">{{ $filter['filter_name'] }}</h3> {{-- 예: 'Screen Size' --}}
                        {{-- 참고: 백엔드로 <form>을 제출하는 데는 두 가지 방법이 있습니다. 첫째, <button type="submit">을 사용하는 일반적인 방법, 둘째, <input> 필드의 "value"
                                속성을 전송하여 AJAX를 사용하는 방법 --}}
                                <form class="facet-form" action="{{ url()->current() }}" method="get"> {{-- "action" 속성이 없으면 <form> 데이터를 동일한 페이지로 제출하고,
                                        "method" 속성이 없으면 기본 "method"인 "GET"을 사용합니다 --}}
                                        <div class="associate-wrapper">
                                            @foreach ($filter['filter_values'] as $value) {{-- $value는 'filter value'를 의미함 --}}
                                                {{-- <input type="checkbox" class="check-box" id="{{ $value['filter_value'] }}"> --}}

                                                {{-- 동적 필터를 작동시키는 두 가지 방법을 사용했습니다: jQuery를 사용한 정적 방법, 그리고 관리자 패널에서 동적으로 --}}
                                                {{-- 첫 번째 방법: jQuery를 사용한 정적 방법. front/custom.js 확인 --}}
                                                <input type="checkbox" class="check-box {{ $filter['filter_column'] }}"
                                                    id="{{ $value['filter_value'] }}" name="{{ $filter['filter_column'] }}[]"
                                                    value="{{ $value['filter_value'] }}"> {{-- 주의!!: "name" 속성의 대괄호 []를 확인하세요!! --}} {{--
                                                필터링을 위해 jQuery에서 사용할 수 있도록 필터 이름을 'CSS 클래스'로 출력하고, "name" (배열로!! 대괄호 [] 주의!!! 예: 'fabric' =>
                                                ['cotton', 'polyester']) 및 "value" HTML 속성도 추가합니다. --}} {{-- 체크박스 <input> 필드의 "name" 속성에 대괄호
                                                []를 사용했으므로 체크된 <input> 필드는 배열로 제출됩니다. (예: 'fabric' => ['cotton', 'polyester']) 그렇지 않으면 AJAX가
                                                <form> 제출 없이 <input> 값을 전송합니다. --}}
                                                    <label class="label-text"
                                                        for="{{ $value['filter_value'] }}">{{ ucwords($value['filter_value']) }}
                                                        {{-- <span class="total-fetch-items">(0)</span> --}}
                                                    </label>
                                            @endforeach
                                        </div>
                                    </form>
                    </div>
                @endif
            @endif

        @endforeach
        <!-- Filter -->

    @endif


</div>
<!-- Shop-Left-Side-Bar-Wrapper /- -->
