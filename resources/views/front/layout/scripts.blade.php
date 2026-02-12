@php
    // 참고: 이 파일 내용의 대부분은 PHP 코드를 JavaScript 코드 내에서 작성하여 동적 필터를 동적으로 작동시키기 위해(두 번째 방법) front/js/custom.js에서 이동되었습니다. 이 파일은 front/layout/layout.blade.php에 include 됩니다.    // 참고: 설정을 위해 .php 파일(확장자가 .php인 파일) 내에서 PHP 코드를 작성해야 합니다! (이 파일은 .php 확장자를 가집니다!) (또 다른 방법은 AJAX 호출을 사용하여 $productFilters를 가져오는 것입니다!)
    // 이 파일은 front/layout/layout.blade.php에 include 됩니다.
    // 정적이 아닌 동적으로 동적 필터 작동 (두 번째 방법)    



    $productFilters = \App\Models\ProductsFilter::productFilters(); // 모든(활성/enabled) 필터 가져오기    // (또 다른 방법은 AJAX 호출을 사용하여 $productFilters를 가져오는 것입니다!)
    // dd($productFilters);
@endphp


<script> // 참고: 이 파일은 .php 확장자를 가지므로 자바스크립트를 작성하기 위해 <script> 태그를 사용해야 합니다.
    // 웹사이트 프론트엔드 섹션에 jQuery 사용:
    $(document).ready(function () {

        // AJAX를 사용하지 않는 정렬 필터 (HTML <form>과 jQuery 사용) front/products/listing.blade.php



        // AJAX를 사용한 정렬 필터 front/products/listing.blade.php. ajax_products_listing.blade.php 확인 (listing.blade.php 페이지에 include 됨)
        $('#sort').on('change', function () { // listing.blade.php의 <selec> 박스 선택
            var sort = $('#sort').val(); // 'sort' name HTML 속성의 <select> 박스 값 가져오기
            var url = $('#url').val(); // 'url' name HTML 속성의 <input> 필드 값 가져오기 ($url은 Front/ProductsController.php의 listing() 메소드에서 뷰(listing.blade.php)로 전달됨)


            // 'fabric' 동적 필터 값(filters.blade.php의 ':checked' 체크박스 <input> 필드 값)을 정렬 필터 'sort'와 함께 전송


            var size = get_filter('size'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('size' 필터 값) 가져오기    // 'size' 필터 값 배열 가져오기 (예: ['small', 'medium', ...])    // get_filter()는 front/js/custom.js에 있습니다.
            var color = get_filter('color'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('color' 필터 값) 가져오기    // 'color' 필터 값 배열 가져오기 (예: ['red', 'blue', ...])    // get_filter()는 front/js/custom.js에 있습니다.
            var price = get_filter('price'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('price' 필터 값) 가져오기    // 'price' 필터 값 배열 가져오기 (예: ['1000-2000', '2000-5000', ...])    // get_filter()는 front/js/custom.js에 있습니다.
            var brand = get_filter('brand'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('brand' 필터 값) 가져오기    // 'brand' 필터 값 배열 가져오기 (예: ['Concrete', 'Adidas', ...])    // get_filter()는 front/js/custom.js에 있습니다.


            // 모든 동적 필터 값(filters.blade.php의 ':checked' 체크박스 <input> 필드 값)을 동적으로 정렬 필터 'sort'와 함께 전송
            // 정렬 필터가 클릭되면, 다른 동적 필터 값들도 AJAX 호출과 함께 전송 (sort, url 포함)
            @foreach ($productFilters as $filters) // 현재 jQuery로 선택된 필터 외에 나머지 필터 값들을 가져오기 위한 새로운 별도 반복문    // 여기서 메인 필터에 대해 다시 반복해야 합니다. 그렇지 않으면 $(.filter) 선택자가 단 하나의 필터 값만 선택하고 다른 필터 값을 무시하게 됩니다. (예: 반복문이 없으면 'fabric' 필터 값만 선택하고 'sleeve' 필터 값은 무시함)
                var {{ $filters['filter_column'] }} = get_filter('{{ $filters['filter_column'] }}'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스 가져오기 (현재 선택된 필터 외의 다른 필터 포함)    // 필터 값 배열 가져오기 (예: ['cotton', 'polyester', ...])    // get_filter()는 front/js/custom.js에 있습니다.
            @endforeach


            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, // X-CSRF-TOKEN: https://laravel.com/docs/9.x/csrf#csrf-x-csrf-token    
                url: url, // 예: /men (이 URL은 web.php의 동적 라우트를 통해 'ProductsController@listing'을 호출합니다)    // web.php의 라우트와 ProductsController의 listing() 메소드 확인
                type: 'Post',
                data: { // 'sort'(정렬 필터), 'url' 변수와 모든 동적 필터 값을 동적으로 전달

                    // 정렬 필터가 클릭되면, 모든 동적 필터 값을 AJAX 호출과 함께 전송 (sort, url 포함)
                    @foreach ($productFilters as $filters) // AJAX 호출에 현재 선택된 필터 값 외에 나머지 모든 필터 값을 함께 보내기 위한 새로운 별도 반복문
                        {{ $filters['filter_column'] }}: {{ $filters['filter_column'] }}, // fabric은 'fabric' 필터 값의 배열입니다 (예: ['cotton', 'polyester', ...])    // 정렬 필터 값(sort)과 함께 동적 필터 값('fabric' 등) 전송
                    @endforeach
                sort: sort, url: url, size: size, color: color, price: price, brand: brand

                },
            success: function (data) {
                $('.filter_products').html(data);
            },
            error  : function () {
                alert('Error');
            }
            });
        });

    // 첫 번째 방법을 사용하여 동적 필터를 정적으로 작동 ('fabric' 필터 전용): // 이 파일의 get_filter() 함수와 Front/ProductsController.php의 listing() 메소드 확인
    // 정렬 필터 함수(위의 코드)와 마찬가지로 'url'과 'sort'를 함께 전송해야 합니다 ('fabric'과 함께)

    // 두 번째 방법을 사용하여 동적 필터를 동적으로 작동 (모든 필터 대상): // front/js/custom.js의 get_filter() 함수와 Front/ProductsController.php의 listing() 메소드 확인
    // 정렬 필터 함수(위의 코드)와 마찬가지로 'url'과 'sort'를 함께 전송해야 합니다 ('fabric'과 함께)
    // 필터 값이 클릭되면, 해당 필터 값과 다른 모든 필터 값을 함께 전송!!
    @foreach ($productFilters as $filter) // 데이터베이스에서 활성화된 모든 필터 가져오기 ($productFilters는 이 파일 상단에서 정의됨)

        // 필터 값이 클릭되면, 해당 필터 값과 다른 모든 필터 값을 함께 전송!!
        $('.{{ $filter['filter_column'] }}').on('click', function () { // filters.blade.php에서 'fabric' 필터(foreach 반복문으로 동적 생성됨) 선택
            var url = $('#url').val(); // listing.blade.php 페이지의 <select> 박스에서 가져옴
            var sort = $('#sort option:selected').val(); // listing.blade.php에서 선택된(:selected) <option> 요소만 선택 (예: 'price_highest', 'name_z_a' 등)    // 정렬 필터 값(sort)과 동적 필터 값 함께 전송


            var size = get_filter('size'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('size' 필터 값) 가져오기    // 'size' 필터 값 배열 가져오기 (예: ['small', 'medium', ...])    // get_filter()는 front/js/custom.js에 있습니다.
            var color = get_filter('color'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('color' 필터 값) 가져오기    // 'color' 필터 값 배열 가져오기 (예: ['red', 'blue', ...])    // get_filter()는 front/js/custom.js에 있습니다.
            var price = get_filter('price'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('price' 필터 값) 가져오기    // 'price' 필터 값 배열 가져오기 (예: ['1000-2000', '2000-5000', ...])    // get_filter()는 front/js/custom.js에 있습니다.
            var brand = get_filter('brand'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('brand' 필터 값) 가져오기    // 'brand' 필터 값 배열 가져오기 (예: ['Concrete', 'Adidas', ...])    // get_filter()는 front/js/custom.js에 있습니다.



            // 필터 값이 클릭되면, 해당 필터 값과 다른 모든 필터 값을 함께 전송!!
            @foreach ($productFilters as $filters) // 현재 jQuery로 선택된 필터 외에 나머지 필터 값들을 가져오기 위한 새로운 별도 반복문    // 여기서 메인 필터에 대해 다시 반복해야 합니다.
                var {{ $filters['filter_column'] }} = get_filter('{{ $filters['filter_column'] }}'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스 가져오기 ('fabric' 등)    // 필터 값 배열 가져오기    // get_filter()는 front/js/custom.js에 있습니다.
            @endforeach



            $.ajax({
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, // X-CSRF-TOKEN: https://laravel.com/docs/9.x/csrf#csrf-x-csrf-token    
                url: url, // Front/ProductsController.php의 listing() 메소드 호출    // 예: /men (이 URL은 web.php의 동적 라우트를 통해 'ProductsController@listing'을 호출합니다)
                method: 'Post',
                data: {

                    data: {

                        // 필터 값이 클릭되면, 해당 필터 값과 다른 모든 필터 값을 함께 전송!! (참고: 반복문을 제거하고 $filters를 $filter로 변경 후 콘솔 확인)
                        @foreach ($productFilters as $filters) // AJAX 호출에 현재 선택된 필터 값 외에 나머지 모든 필터 값을 함께 보내기 위한 새로운 별도 반복문    // 메인 필터에 대해 다시 반복해야 합니다.
                            {{ $filters['filter_column'] }}: {{ $filters['filter_column'] }}, // fabric은 'fabric' 필터 값의 배열입니다 (예: ['cotton', 'polyester', ...])    // 정렬 필터 값(sort)과 함께 동적 필터 값('fabric' 등) 전송
                        @endforeach
                url: url, sort: sort, size: size, color: color, price: price, brand: brand

                            },
            success: function (data) {
                $('.filter_products').html(data); // in listing.blade.php
            },
            error  : function () {
                alert('Error');
            }
                        });
                    });

    @endforeach




    // Size, price, color, brand 등도 동적 필터이지만, 다른 동적 필터처럼 관리되지 않고 각각의 데이터베이스 테이블에서 관리됩니다.
    // 첫째: 'size' 필터 (products_attributes 데이터베이스 테이블에서)
    // 'size' 필터 값이 클릭되면, 해당 'size' 필터 값과 다른 모든 필터 값을 함께 전송!!
    $('.size').on('click', function () { // filters.blade.php에서 'size' 필터 선택
        var url = $('#url').val(); // listing.blade.php 페이지(filters.blade.php 페이지를 포함함)의 <select> 박스에서 가져옴
        var sort = $('#sort option:selected').val(); // listing.blade.php(filters.blade.php를 포함함)에서 선택된(:selected) <option> 요소만 선택 (예: 'price_highest', 'name_z_a' 등)    // https://www.w3schools.com/jquery/sel_input_selected.asp    // .text() https://www.w3schools.com/jquery/html_text.asp    // 정렬 필터 값(sort)과 동적 필터 값('fabric' 등) 전송


        var size = get_filter('size'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('size' 필터 값) 가져오기    // 'size' 필터 값 배열 가져오기 (예: ['small', 'medium', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        var color = get_filter('color'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('color' 필터 값) 가져오기    // 'color' 필터 값 배열 가져오기 (예: ['red', 'blue', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        var price = get_filter('price'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('price' 필터 값) 가져오기    // 'price' 필터 값 배열 가져오기 (예: ['1000-2000', '2000-5000', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        var brand = get_filter('brand'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('brand' 필터 값) 가져오기    // 'brand' 필터 값 배열 가져오기 (예: ['Concrete', 'Adidas', ...])    // get_filter()는 front/js/custom.js에 있습니다.



        // 'size' 필터 값이 클릭되면, 해당 'size' 필터 값과 다른 모든 필터 값을 함께 전송!!
        @foreach ($productFilters as $filters) // 현재 jQuery로 선택된 'size' 필터 외에 나머지 필터 값들을 가져오기 위한 새로운 별도 반복문    // 메인 필터에 대해 다시 반복해야 합니다.
            var {{ $filters['filter_column'] }} = get_filter('{{ $filters['filter_column'] }}'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스 가져오기 ('fabric' 등)    // 필터 값 배열 가져오기 (예: ['red', 'blue', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        @endforeach



        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, // X-CSRF-TOKEN: https://laravel.com/docs/9.x/csrf#csrf-x-csrf-token    
            url: url, // Front/ProductsController.php의 listing() 메소드를 호출합니다.    // 예: /men (이 URL은 web.php의 동적 라우트를 통해 'ProductsController@listing'을 호출합니다)    // web.php의 라우트와 ProductsController의 listing() 메소드 확인
            method: 'Post',
            data: {

                // 'size' 필터 값이 클릭되면, 해당 'size' 필터 값과 다른 모든 필터 값을 함께 전송!!
                @foreach ($productFilters as $filters) // AJAX 호출에 현재 선택된 'size' 필터 값 외에 나머지 모든 필터 값을 함께 보내기 위한 새로운 별도 반복문    // 메인 필터에 대해 다시 반복해야 합니다.
                    {{ $filters['filter_column'] }}: {{ $filters['filter_column'] }}, // fabric은 'fabric' 필터 값의 배열입니다 (예: ['cotton', 'polyester', ...])    // 정렬 필터 값(sort)과 함께 동적 필터 값('fabric' 등) 전송
                @endforeach
            url: url, sort: sort, size: size, color: color, price: price, brand: brand

                },
        success: function (data) {
            $('.filter_products').html(data); // in listing.blade.php
        },
        error  : function () {
            alert('Error');
        }
            });
        });


    // Size, price, color, brand 등도 동적 필터이지만, 각각의 데이터베이스 테이블에서 관리됩니다.
    // 둘째: 'color' 필터 (products 테이블에서)
    // 'color' 필터 값이 클릭되면, 해당 'color' 필터 값과 다른 모든 필터 값을 함께 전송!!
    $('.color').on('click', function () { // filters.blade.php에서 'color' 필터 선택
        var url = $('#url').val(); // listing.blade.php 페이지(filters.blade.php 페이지를 포함함)의 <select> 박스에서 가져옴
        var sort = $('#sort option:selected').val(); // listing.blade.php(filters.blade.php를 포함함)에서 선택된(:selected) <option> 요소만 선택 (예: 'price_highest', 'name_z_a' 등)    // https://www.w3schools.com/jquery/sel_input_selected.asp    // .text() https://www.w3schools.com/jquery/html_text.asp    // 정렬 필터 값(sort)과 동적 필터 값('fabric' 등) 전송

        var size = get_filter('size'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('size' 필터 값) 가져오기    // 'size' 필터 값 배열 가져오기 (예: ['small', 'medium', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        var color = get_filter('color'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('color' 필터 값) 가져오기    // 'color' 필터 값 배열 가져오기 (예: ['red', 'blue', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        var price = get_filter('price'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('price' 필터 값) 가져오기    // 'price' 필터 값 배열 가져오기 (예: ['1000-2000', '2000-5000', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        var brand = get_filter('brand'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('brand' 필터 값) 가져오기    // 'brand' 필터 값 배열 가져오기 (예: ['Concrete', 'Adidas', ...])    // get_filter()는 front/js/custom.js에 있습니다.



        // 'color' 필터 값이 클릭되면, 해당 'color' 필터 값과 다른 모든 필터 값을 함께 전송!!
        @foreach ($productFilters as $filters) // 현재 jQuery로 선택된 'color' 필터 외에 나머지 필터 값들을 가져오기 위한 새로운 별도 반복문    // 메인 필터에 대해 다시 반복해야 합니다.
            var {{ $filters['filter_column'] }} = get_filter('{{ $filters['filter_column'] }}'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스 가져오기 ('fabric' 등)    // 필터 값 배열 가져오기 (예: ['red', 'green', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        @endforeach



        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, // X-CSRF-TOKEN: https://laravel.com/docs/9.x/csrf#csrf-x-csrf-token    
            url: url, // Front/ProductsController.php의 listing() 메소드를 호출합니다.    // 예: /men (이 URL은 web.php의 동적 라우트를 통해 'ProductsController@listing'을 호출합니다)    // web.php의 라우트와 ProductsController의 listing() 메소드 확인
            method: 'Post',
            data: {

                // 'color' 필터 값이 클릭되면, 해당 'color' 필터 값과 다른 모든 필터 값을 함께 전송!!
                @foreach ($productFilters as $filters) // AJAX 호출에 현재 선택된 'color' 필터 값 외에 나머지 모든 필터 값을 함께 보내기 위한 새로운 별도 반복문    // 메인 필터에 대해 다시 반복해야 합니다.
                    {{ $filters['filter_column'] }}: {{ $filters['filter_column'] }}, // fabric은 'fabric' 필터 값의 배열입니다 (예: ['cotton', 'polyester', ...])    // 정렬 필터 값(sort)과 함께 동적 필터 값('fabric' 등) 전송
                @endforeach
            url: url, sort: sort, size: size, color: color, price: price, brand: brand

                },
        success: function (data) {
            $('.filter_products').html(data); // in listing.blade.php
        },
        error  : function () {
            alert('Error');
        }
            });
        });


    // Size, price, color, brand 등도 동적 필터이지만, 각각의 데이터베이스 테이블에서 관리됩니다.
    // 셋째: 'price' 필터 (products 테이블에서)
    // 'price' 필터 값이 클릭되면, 해당 'price' 필터 값과 다른 모든 필터 값을 함께 전송!!
    $('.price').on('click', function () { // filters.blade.php에서 'price' 필터 선택
        var url = $('#url').val(); // listing.blade.php 페이지(filters.blade.php 페이지를 포함함)의 <select> 박스에서 가져옴
        var sort = $('#sort option:selected').val(); // listing.blade.php(filters.blade.php를 포함함)에서 선택된(:selected) <option> 요소만 선택 (예: 'price_highest', 'name_z_a' 등)    // https://www.w3schools.com/jquery/sel_input_selected.asp    // .text() https://www.w3schools.com/jquery/html_text.asp    // 정렬 필터 값(sort)과 동적 필터 값('fabric' 등) 전송

        var size = get_filter('size'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('size' 필터 값) 가져오기    // 'size' 필터 값 배열 가져오기 (예: ['small', 'medium', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        var color = get_filter('color'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('color' 필터 값) 가져오기    // 'color' 필터 값 배열 가져오기 (예: ['red', 'blue', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        var price = get_filter('price'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('price' 필터 값) 가져오기    // 'price' 필터 값 배열 가져오기 (예: ['1000-2000', '2000-5000', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        var brand = get_filter('brand'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('brand' 필터 값) 가져오기    // 'brand' 필터 값 배열 가져오기 (예: ['Concrete', 'Adidas', ...])    // get_filter()는 front/js/custom.js에 있습니다.


        // 'price' 필터 값이 클릭되면, 해당 'price' 필터 값과 다른 모든 필터 값을 함께 전송!!
        @foreach ($productFilters as $filters) // 현재 jQuery로 선택된 'price' 필터 외에 나머지 필터 값들을 가져오기 위한 새로운 별도 반복문    // 메인 필터에 대해 다시 반복해야 합니다.
            var {{ $filters['filter_column'] }} = get_filter('{{ $filters['filter_column'] }}'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스 가져오기 ('fabric' 등)    // 필터 값 배열 가져오기 (예: ['cotton', 'polyester', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        @endforeach



        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, // X-CSRF-TOKEN: https://laravel.com/docs/9.x/csrf#csrf-x-csrf-token    
            url: url, // Front/ProductsController.php의 listing() 메소드를 호출합니다.    // 예: /men (이 URL은 web.php의 동적 라우트를 통해 'ProductsController@listing'을 호출합니다)    // web.php의 라우트와 ProductsController의 listing() 메소드 확인
            method: 'Post',
            data: {

                // 'price' 필터 값이 클릭되면, 해당 'price' 필터 값과 다른 모든 필터 값을 함께 전송!!
                @foreach ($productFilters as $filters) // AJAX 호출에 현재 선택된 'price' 필터 값 외에 나머지 모든 필터 값을 함께 보내기 위한 새로운 별도 반복문    // 메인 필터에 대해 다시 반복해야 합니다.
                    {{ $filters['filter_column'] }}: {{ $filters['filter_column'] }}, // fabric은 'fabric' 필터 값의 배열입니다 (예: ['cotton', 'polyester', ...])    // 정렬 필터 값(sort)과 함께 동적 필터 값('fabric' 등) 전송
                @endforeach
            url: url, sort: sort, size: size, color: color, price: price, brand: brand

                },
        success: function (data) {
            $('.filter_products').html(data); // in listing.blade.php
        },
        error  : function () {
            alert('Error');
        }
            });
        });


    // Size, price, color, brand 등도 동적 필터이지만, 각각의 데이터베이스 테이블에서 관리됩니다.
    // 넷째: 'brand' 필터 (products 및 brands 데이터베이스 테이블에서)
    // 'brand' 필터 값이 클릭되면, 해당 'brand' 필터 값과 다른 모든 필터 값을 함께 전송!!
    $('.brand').on('click', function () { // filters.blade.php에서 'brand' 필터 선택
        var url = $('#url').val(); // listing.blade.php 페이지(filters.blade.php 페이지를 포함함)의 <select> 박스에서 가져옴
        var sort = $('#sort option:selected').val(); // listing.blade.php(filters.blade.php를 포함함)에서 선택된(:selected) <option> 요소만 선택 (예: 'brand_highest', 'name_z_a' 등)    // https://www.w3schools.com/jquery/sel_input_selected.asp    // .text() https://www.w3schools.com/jquery/html_text.asp    // 정렬 필터 값(sort)과 동적 필터 값('fabric' 등) 전송

        var size = get_filter('size'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('size' 필터 값) 가져오기    // 'size' 필터 값 배열 가져오기 (예: ['small', 'medium', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        var color = get_filter('color'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('color' 필터 값) 가져오기    // 'color' 필터 값 배열 가져오기 (예: ['red', 'blue', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        var price = get_filter('price'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('price' 필터 값) 가져오기    // 'price' 필터 값 배열 가져오기 (예: ['1000-2000', '2000-5000', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        var brand = get_filter('brand'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스('brand' 필터 값) 가져오기    // 'brand' 필터 값 배열 가져오기 (예: ['Concrete', 'Adidas', ...])    // get_filter()는 front/js/custom.js에 있습니다.


        // 'brand' 필터 값이 클릭되면, 해당 'brand' 필터 값과 다른 모든 필터 값을 함께 전송!!
        @foreach ($productFilters as $filters) // 현재 jQuery로 선택된 'brand' 필터 외에 나머지 필터 값들을 가져오기 위한 새로운 별도 반복문    // 메인 필터에 대해 다시 반복해야 합니다.
            var {{ $filters['filter_column'] }} = get_filter('{{ $filters['filter_column'] }}'); // filters.blade.php에서 ':checked' 상태인 모든 체크박스 가져오기 ('fabric' 등)    // 필터 값 배열 가져오기 (예: ['cotton', 'polyester', ...])    // get_filter()는 front/js/custom.js에 있습니다.
        @endforeach



        $.ajax({
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }, // X-CSRF-TOKEN: https://laravel.com/docs/9.x/csrf#csrf-x-csrf-token    
            url: url, // Front/ProductsController.php의 listing() 메소드를 호출합니다.    // 예: /men (이 URL은 web.php의 동적 라우트를 통해 'ProductsController@listing'을 호출합니다)    // web.php의 라우트와 ProductsController의 listing() 메소드 확인
            method: 'Post',
            data: {

                // 'brand' 필터 값이 클릭되면, 해당 'brand' 필터 값과 다른 모든 필터 값을 함께 전송!!
                @foreach ($productFilters as $filters) // AJAX 호출에 현재 선택된 'brand' 필터 값 외에 나머지 모든 필터 값을 함께 보내기 위한 새로운 별도 반복문    // 메인 필터에 대해 다시 반복해야 합니다.
                    {{ $filters['filter_column'] }}: {{ $filters['filter_column'] }}, // fabric은 'fabric' 필터 값의 배열입니다 (예: ['cotton', 'polyester', ...])    // 정렬 필터 값(sort)과 함께 동적 필터 값('fabric' 등) 전송
                @endforeach
            url: url, sort: sort, size: size, color: color, price: price, brand: brand

                },
        success: function (data) {
            $('.filter_products').html(data); // in listing.blade.php
        },
        error  : function () {
            alert('Error');
        }
            });
        });
    });
</script>