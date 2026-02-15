@extends('layouts.frontend')

@section('content')
    <!-- Include Channel CSS for styling -->
    <link href="/channel_assets/css/sub.css" rel="stylesheet" type="text/css" />
    <link href="/channel_assets/css/board.css" rel="stylesheet" type="text/css" />

    <div id="contents" style="padding: 120px 0; min-height: 600px; text-align: center;">
        <div class="row">
            <div class="box box1"
                style="max-width: 800px; margin: 0 auto; background: #fff; padding: 50px; border: 1px solid #ddd; border-radius: 10px;">

                <div style="font-size: 60px; color: #4caf50; margin-bottom: 20px;">
                    <!-- Check icon -->
                    ✔️
                </div>
                <div class="ttl" style="font-size: 32px; font-weight: bold; margin-bottom: 20px; color: #333;">주문이 완료되었습니다!
                </div>
                <p style="font-size: 18px; color: #666; margin-bottom: 50px;">
                    고객님의 주문이 성공적으로 접수되었습니다.<br>
                    주문 내역은 마이페이지 > 주문목록에서 확인 가능합니다.
                </p>

                <div class="result_info"
                    style="text-align: left; background: #f9f9f9; padding: 30px; margin-bottom: 40px; border-radius: 5px;">
                    <div
                        style="display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                        <span style="color: #888;">주문번호</span>
                        <strong style="font-size: 18px;">Me9-20260215-0001</strong>
                    </div>
                    <div
                        style="display: flex; justify-content: space-between; margin-bottom: 15px; border-bottom: 1px solid #eee; padding-bottom: 15px;">
                        <span style="color: #888;">결제금액</span>
                        <strong style="font-size: 18px;">56,500 원</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: #888;">배송지</span>
                        <strong style="font-size: 16px;">서울시 강남구 테헤란로 123, 101호</strong>
                    </div>
                </div>

                <div class="btm_btn">
                    <a href="/" class="btn02"
                        style="width: 200px; height: 50px; line-height: 50px; display: inline-block; background: #fff; border: 1px solid #ccc; color: #333; font-size: 16px; margin-right: 10px;">홈으로</a>
                    <a href="{{ route('front.shop.order.details') }}" class="btn01"
                        style="width: 200px; height: 50px; line-height: 50px; display: inline-block; background: #333; color: #fff; font-size: 16px;">주문내역
                        확인</a>
                </div>
            </div>
        </div>
    </div>
@endsection