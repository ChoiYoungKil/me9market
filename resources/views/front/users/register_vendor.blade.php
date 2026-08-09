@extends('front.layout.layout')

@section('content')
    <!-- Page Introduction Wrapper -->
    <div class="page-style-a">
        <div class="container">
            <div class="page-intro">
                <h2>Seller Registration</h2>
                <ul class="bread-crumb">
                    <li class="has-separator">
                        <i class="ion ion-md-home"></i>
                        <a href="/">Home</a>
                    </li>
                    <li class="is-marked">
                        <a href="javascript:void(0);">Seller Registration</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Page Introduction Wrapper /- -->

    <!-- Account-Page -->
    <div class="page-account u-s-p-t-80">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="reg-wrapper">
                        <h2 class="account-h2 u-s-m-b-20">Become a Seller</h2>
                        <h6 class="account-h6 u-s-m-b-30">Register your shop and start selling on our platform.</h6>

                        <div class="u-s-m-b-30">
                            Already have a member account? <a href="{{ url('/login-register') }}" class="u-c-brand">Login
                                here</a> or <a href="{{ url('/login-register') }}">Register as a Member</a>
                        </div>

                        <p id="register-success"></p>

                        <form id="vendorRegisterForm" action="javascript:;" method="post">
                            @csrf
                            <input type="hidden" name="account_type" value="vendor">

                            <div class="row">
                                <div class="col-md-6">
                                    <h4 class="account-h4 u-s-m-b-20">Personal Details</h4>

                                    <div class="u-s-m-b-30">
                                        <label for="name">Name <span class="astk">*</span></label>
                                        <input type="text" id="name" name="name" class="text-field" placeholder="Your Name">
                                        <p id="register-name"></p>
                                    </div>

                                    <div class="u-s-m-b-30">
                                        <label for="mobile">Mobile <span class="astk">*</span></label>
                                        <input type="text" id="mobile" name="mobile" class="text-field"
                                            placeholder="Your Mobile Number">
                                        <p id="register-mobile"></p>
                                    </div>

                                    <div class="u-s-m-b-30">
                                        <label for="email">Email <span class="astk">*</span></label>
                                        <input type="email" id="email" name="email" class="text-field"
                                            placeholder="Your Email">
                                        <p id="register-email"></p>
                                    </div>

                                    <div class="u-s-m-b-30">
                                        <label for="password">Password <span class="astk">*</span></label>
                                        <input type="password" id="password" name="password" class="text-field"
                                            placeholder="Password">
                                        <p id="register-password"></p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <h4 class="account-h4 u-s-m-b-20">Shop Details</h4>

                                    <div class="u-s-m-b-30">
                                        <label for="shop_name">Shop Name <span class="astk">*</span></label>
                                        <input type="text" id="shop_name" name="shop_name" class="text-field"
                                            placeholder="Store Name">
                                        <p id="register-shop_name"></p>
                                    </div>

                                    <div class="u-s-m-b-30">
                                        <label for="shop_mobile">Shop Mobile <span class="astk">*</span></label>
                                        <input type="text" id="shop_mobile" name="shop_mobile" class="text-field"
                                            placeholder="Store Contact Number">
                                        <p id="register-shop_mobile"></p>
                                    </div>

                                    <div class="u-s-m-b-30">
                                        <label for="business_license_number">Business License Number <span
                                                class="astk">*</span></label>
                                        <input type="text" id="business_license_number" name="business_license_number"
                                            class="text-field" placeholder="Registration Number">
                                        <p id="register-business_license_number"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="u-s-m-b-30">
                                <input type="checkbox" class="check-box" id="accept" name="accept">
                                <label class="label-text no-color" for="accept">I’ve read and accept the
                                    <a href="javascript:void(0);" class="u-c-brand">terms & conditions</a>
                                </label>
                                <p id="register-accept"></p>
                            </div>

                            <div class="u-s-m-b-45">
                                <button class="button button-primary w-100">Register as Seller</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Account-Page /- -->

    <script>
        $(document).ready(function () {
            // 벤더 등록 양식 제출
            $('#vendorRegisterForm').submit(function () {
                $('.loader').show();
                var formdata = $(this).serialize();

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: '/vendor/register',
                    type: 'POST',
                    data: formdata,
                    success: function (resp) {
                        if (resp.type == 'error') {
                            $('.loader').hide();
                            $.each(resp.errors, function (i, error) {
                                $('#register-' + i).css('color', 'red');
                                $('#register-' + i).html(error);
                                setTimeout(function () {
                                    $('#register-' + i).css('display', 'none');
                                }, 3000);
                            });
                        } else if (resp.type == 'success') {
                            $('.loader').hide();
                            $('#register-success').css('color', 'green');
                            $('#register-success').html(resp.message);
                            // URL이 제공된 경우 리디렉션
                            if (resp.url) {
                                window.location.href = resp.url;
                            }
                        }
                    },
                    error: function () {
                        $('.loader').hide();
                        alert('Error');
                    }
                });
            });
        });
    </script>
@endsection