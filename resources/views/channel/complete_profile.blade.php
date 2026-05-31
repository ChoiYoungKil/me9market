@extends('layouts.channel_login')

@section('content')
<div id="container">
    <div id="contents">
        <div id="join">
            <div class="box box1">
                <div class="inner_bx">
                    <div class="page_info" style="text-align: center; margin-bottom: 2rem;">
                        <h2>판매자 정보 입력</h2>
                        <p>판매 활동을 시작하려면 필수 정보를 입력해주세요.</p>
                    </div>

                    <form id="channelRegisterForm" action="javascript:;" method="post" enctype="multipart/form-data">
                        @csrf
                        <div id="board">
                            <div class="write02">
                                <div class="f_bx">
                                    <div class="f_w">
                                        <div class="f_ttl">회원정보 (Read Only)</div>
                                        <div class="tb01">
                                            <table class="two">
                                                <tbody>
                                                    <tr>
                                                        <th class="w160"><span>이름</span></th>
                                                        <td>
                                                            <input type="text"
                                                                value="{{ Auth::guard('admin')->user()->name }}"
                                                                class="w300" readonly disabled>
                                                        </td>
                                                        <th class="w160"><span>휴대폰 번호</span></th>
                                                        <td>
                                                            <input type="text"
                                                                value="{{ Auth::guard('admin')->user()->mobile }}"
                                                                class="w300" readonly disabled>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="w160"><span>이메일(아이디)</span></th>
                                                        <td colspan="3">
                                                            <input type="email"
                                                                value="{{ Auth::guard('admin')->user()->email }}"
                                                                class="w300" readonly disabled>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="f_w">
                                        <div class="f_ttl">회원사 정보</div>
                                        <div class="tb01 type2">
                                            <table class="two">
                                                <colgroup>
                                                    <col width="160px">
                                                    <col width="">
                                                    <col width="160px">
                                                    <col width="">
                                                </colgroup>
                                                <tbody>
                                                    <tr>
                                                        <th class="w160"><span>회원사 명</span></th>
                                                        <td colspan="3">
                                                            <input type="text" id="shop_name" name="shop_name"
                                                                class="w300" required>
                                                            <p id="register-shop_name" class="err_msg"></p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="w160"><span>사업자구분</span></th>
                                                        <td colspan="3">
                                                            <ul class="chk01">
                                                                <li>
                                                                    <input type="radio" name="shop_business_type"
                                                                        id="type_1" value="1" checked>
                                                                    <label for="type_1">개인판매</label>
                                                                </li>
                                                                <li>
                                                                    <input type="radio" name="shop_business_type"
                                                                        id="type_2" value="2">
                                                                    <label for="type_2">개인사업자</label>
                                                                </li>
                                                                <li>
                                                                    <input type="radio" name="shop_business_type"
                                                                        id="type_3" value="3">
                                                                    <label for="type_3">법인사업자</label>
                                                                </li>
                                                            </ul>
                                                            <p id="register-shop_business_type" class="err_msg"></p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="w160"><span>사업자등록번호</span></th>
                                                        <td colspan="3">
                                                            <div class="tel_bx2">
                                                                <input type="text" id="business_license_number"
                                                                    name="business_license_number" class="w300"
                                                                    placeholder="사업자번호 (-포함)" required>
                                                            </div>
                                                            <p id="register-business_license_number" class="err_msg">
                                                            </p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="w160"><span>연락처</span></th>
                                                        <td colspan="3">
                                                            <div class="tel_bx">
                                                                <input type="text" id="shop_mobile" name="shop_mobile"
                                                                    class="w300" placeholder="연락처 (-포함)" required>
                                                            </div>
                                                            <p id="register-shop_mobile" class="err_msg"></p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="w160"><span>회원사 주소지</span></th>
                                                        <td colspan="3">
                                                            <div class="addr_bx">
                                                                <input type="text" name="shop_pincode" class="addr1"
                                                                    placeholder="우편번호" style="width: 100px;">
                                                                <a href="javascript:;" class="btn01">우편번호찾기</a>
                                                                <input type="text" name="shop_address" class="addr2"
                                                                    placeholder="주소" style="width: 300px;">
                                                                <input type="text" name="shop_address_detail"
                                                                    class="addr3" placeholder="상세주소"
                                                                    style="width: 250px;">
                                                            </div>
                                                            <p id="register-shop_address" class="err_msg"></p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="w160"><span>정산용 계좌번호</span></th>
                                                        <td colspan="3">
                                                            <div class="bank_bx">
                                                                <select name="bank_name" style="width: 120px;" required>
                                                                    <option value="" disabled selected>은행선택</option>
                                                                    <option value="국민은행">국민은행</option>
                                                                    <option value="신한은행">신한은행</option>
                                                                    <option value="우리은행">우리은행</option>
                                                                    <option value="하나은행">하나은행</option>
                                                                    <option value="기업은행">기업은행</option>
                                                                    <option value="농협은행">농협은행</option>
                                                                </select>
                                                                <input type="text" name="bank_account_number"
                                                                    class="bank1" placeholder="계좌번호 (‘-’없이 숫자만 입력)"
                                                                    required style="width: 200px;">
                                                                <input type="text" name="bank_account_holder_name"
                                                                    class="bank2" placeholder="예금주" required
                                                                    style="width: 100px;">
                                                            </div>
                                                            <p id="register-bank_name" class="err_msg"></p>
                                                            <p id="register-bank_account_number" class="err_msg"></p>
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th class="w160"><span>정산용 통장사본</span></th>
                                                        <td colspan="3">
                                                            <div class="fileBox">
                                                                <input type="text" class="fileName w300" readonly
                                                                    placeholder="파일선택">
                                                                <label for="bank_copy_image"
                                                                    class="btn_file">찾아보기</label>
                                                                <input type="file" id="bank_copy_image"
                                                                    class="uploadBtn" name="bank_copy_image"
                                                                    style="display:none;">
                                                            </div>
                                                            <p id="register-bank_copy_image" class="err_msg"></p>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <p id="register-success"
                                        style="color: green; text-align: center; margin-top: 10px; font-weight: bold;">
                                    </p>
                                </div>

                            </div>
                            <div class="btm_btn type2">
                                <button type="submit" class="col2" style="border:0; cursor:pointer;">심사 요청</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div><!-- //contents -->
</div><!-- //container -->

<script type="text/javascript">
    $(document).ready(function () {
        // File Input Display
        $('.fileBox .uploadBtn').on('change', function () {
            if (window.FileReader) {
                var filename = $(this)[0].files[0].name;
            } else {
                var filename = $(this).val().split('/').pop().split('\\').pop();
            }
            $(this).parent('.fileBox').find('.fileName').val(filename);
        });

        // AJAX Submission
        $('#channelRegisterForm').submit(function () {
            // Clear previous errors
            $('.err_msg').hide().html('');

            var formdata = new FormData(this);

            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: '{{ route('channel.complete_profile.submit') }}',
                type: 'POST',
                data: formdata,
                processData: false,
                contentType: false,
                success: function (resp) {
                    if (resp.type == 'error') {
                        $.each(resp.errors, function (i, error) {
                            $('#register-' + i).css('display', 'block').css('color',
                                'red').html(error);
                        });
                    } else if (resp.type == 'success') {
                        $('#register-success').html(resp.message);
                        if (resp.url) {
                            setTimeout(function () {
                                window.location.href = resp.url;
                            }, 2000);
                        }
                    }
                },
                error: function () {
                    alert('Error');
                }
            });
        });
    });
</script>

<style>
    .err_msg {
        color: red;
        font-size: 12px;
        margin-top: 5px;
        display: none;
    }
</style>
@endsection