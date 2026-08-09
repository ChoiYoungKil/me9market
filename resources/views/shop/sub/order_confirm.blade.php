@extends('layouts.shop')

@section('page_type', 'sub')

@section('content')
    <div id="container">
        <div id="contents">
            <div id="login">
                <div class="box box1">
                    <div class="inner_bx">
                        <img src="{{ asset('shop/images/common/logo.png') }}" class="logo">
                        <form>
                            <div class="f_bx">
                                <input class="mt0" type="text" placeholder="주문자 이름">
                                <div class="tel_bx">
                                    <select>
                                        <option>010</option>
                                    </select>
                                    <span>-</span>
                                    <input type="text" class="">
                                    <span>-</span>
                                    <input type="text" class="">
                                </div>
                                <a href="#" class="btn">주문조회</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div><!-- //contents -->
    </div><!-- //container -->
@endsection