{{-- 이 페이지는 front/products/checkout.blade.php에 'include' 되며, jQuery AJAX를 사용하여 다시 로드됩니다. front/js/custom.js 확인 --}}


<!-- Form-Fields /- -->
<h4 class="section-h4 deliveryText">Add New Delivery Address</h4> {{-- Edit 버튼을 클릭할 때 <h4> 내용을 변경하기 위해 jQuery 핸들로 사용할
    deliveryText CSS 클래스를 생성했습니다. --}}
    <div class="u-s-m-b-24">
        <input type="checkbox" class="check-box" id="ship-to-different-address" data-toggle="collapse"
            data-target="#showdifferent">




        @if (count($deliveryAddresses) > 0) {{-- 현재 인증/로그인된 사용자에 대한 $deliveryAddresses가 있는지 확인 --}} {{--
            $deliveryAddresses 변수는 Front/ProductsController.php의 checkout() 메소드에서 전달됩니다. --}}
            <label class="label-text newAddress" for="ship-to-different-address">Ship to a different address?</label>
        @else {{-- 기존 배송 주소가 없는 경우 --}}
            <label class="label-text newAddress" for="ship-to-different-address">Check to add Delivery Address</label>
        @endif

    </div>
    <div class="collapse" id="showdifferent">
        <!-- Form-Fields -->

        {{-- 참고: AJAX 호출 응답(백엔드)에서 폼의 유효성 검사 오류 메시지(Laravel의 유효성 검사 오류 메시지)를 표시하기 위해 모든 <input> 필드 뒤에 <p> 태그를 생성합니다.
            jQuery 루프가 작동하려면
        <p> ID 패턴이 delivery-x(예: delivery-mobile, delivery-email...)와 같아야 합니다. 그리고 x는 'name' HTML 속성과 동일해야 합니다(예:
            name='mobile' HTML 속성이 있는 <input>에는 id="delivery-mobile" HTML 속성이 있는
        <p>가 있어야 함). 그래야 유효성 검사 오류 배열이 백엔드/서버에서 AJAX 요청으로 응답으로 전송될 때(컨트롤러 내부의 메소드에서 $validator->messages() 확인), jQuery
            $.each() 루프로 편리하게/쉽게 처리할 수 있습니다. front/js/custom.js 확인 --}}
        <form id="addressAddEditForm" action="javascript:;" method="post">
            @csrf


            <input type="hidden" name="delivery_id"> {{-- 이 HTML 폼이 AJAX를 통해 제출될 때 배송 주소 ID를 제출하여 `delivery_addresses`
            데이터베이스 테이블에 배송 주소를 저장하기 위해 이 숨겨진 <input> 필드를 생성했습니다. front/js/custom.js 파일의 AJAX를 통한 배송 주소 저장 기능을 확인하세요.
            --}}
            <div class="group-inline u-s-m-b-13">
                <div class="group-1 u-s-p-r-16">
                    <label for="delivery_name">Name
                        <span class="astk">*</span>
                    </label>
                    <input class="text-field" type="text" id="delivery_name" name="delivery_name">
                    <p id="delivery-delivery_name"></p> {{-- 이 <p> 태그는 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사
                        오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}} {{-- jQuery 루프가 작동하려면 패턴이 delivery-x(예: delivery-mobile,
                        delivery-email...)와 같아야 합니다. --}}
                </div>
                <div class="group-2">
                    <label for="delivery_address">Address
                        <span class="astk">*</span>
                    </label>
                    <input class="text-field" type="text" id="delivery_address" name="delivery_address">
                    <p id="delivery-delivery_address"></p> {{-- 이 <p> 태그는 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성
                        검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                </div>
            </div>
            <div class="group-inline u-s-m-b-13">
                <div class="group-1 u-s-p-r-16">
                    <label for="delivery_city">City
                        <span class="astk">*</span>
                    </label>
                    <input class="text-field" type="text" id="delivery_city" name="delivery_city">
                    <p id="delivery-delivery_city"></p> {{-- 이 <p> 태그는 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사
                        오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                </div>
                <div class="group-2">
                    <label for="delivery_state">State
                        <span class="astk">*</span>
                    </label>
                    <input class="text-field" type="text" id="delivery_state" name="delivery_state">
                    <p id="delivery-delivery_state"></p> {{-- 이 <p> 태그는 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사
                        오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                </div>
            </div>
            <div class="u-s-m-b-13">
                <label for="select-country-extra">Country
                    <span class="astk">*</span>
                </label>
                <div class="select-box-wrapper">
                    <select class="select-box" id="delivery_country" name="delivery_country">
                        <option value="">Select Country</option>

                        @foreach ($countries as $country) {{-- $countries was passed from UserController to view using
                            compact() method --}}
                            <option value="{{ $country['country_name'] }}" @if ($country['country_name'] == \Illuminate\Support\Facades\Auth::user()->country) selected
                            @endif>{{ $country['country_name'] }}</option>
                        @endforeach

                    </select>
                    </select>
                    <p id="delivery-delivery_country"></p> {{-- 이 <p> 태그는 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성
                        검사 오류 메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
                </div>
            </div>
            <div class="u-s-m-b-13">
                <label for="delivery_pincode">Pincode
                    <span class="astk">*</span>
                </label>
                <input class="text-field" type="text" id="delivery_pincode" name="delivery_pincode">
                <p id="delivery-delivery_pincode"></p> {{-- 이 <p> 태그는 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사 오류
                    메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
            </div>
            <div class="u-s-m-b-13">
                <label for="delivery_mobile">Mobile
                    <span class="astk">*</span>
                </label>
                <input class="text-field" type="text" id="delivery_mobile" name="delivery_mobile">
                <p id="delivery-delivery_mobile"></p> {{-- 이 <p> 태그는 서버(백엔드)의 AJAX 호출 응답에서 유효성 검사 오류 메시지(라라벨의 유효성 검사 오류
                    메시지)를 표시하기 위해 jQuery에서 사용됩니다. --}}
            </div>
            <div class="u-s-m-b-13">
                <button style="width: 100%" type="submit" class="button button-outline-secondary">Save</button> {{--
                추가(Add) 또는 편집(Edit) 여부에 관계없이 저장 --}}
            </div>

        </form>

        <!-- Form-Fields /- -->



    </div>
    <div>
        <label for="order-notes">Order Notes</label>
        <textarea class="text-area" id="order-notes"
            placeholder="Notes about your order, e.g. special notes for delivery."></textarea>
    </div>