@extends('layouts.admin')

@section('page_type', 'sub')

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">대제목 #1</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>대분류</li>
                        <li>중분류</li>
                        <li>소분류</li>
                    </ul>
                </div>

                <div class="conbx">
                    <div class="con_w">
                        <div class="ttl01">로딩화면</div>

                        <!-- 로딩태그 -->
                        <div class="loading01"></div>
                    </div>
                </div>
            </div>
        </div>
    </div><!-- //contents -->
@endsection

@push('scripts')
    <script type="text/javascript">
        $(function () {
            $(".loading01").html(
                '<div class="loading_w">' +
                '<div class="icon_bx">' +
                '<div class="icon">' +
                '<div class="bar1"></div><div class="bar2"></div><div class="bar3"></div><div class="bar4"></div><div class="bar5"></div><div class="bar6"></div><div class="bar7"></div><div class="bar8"></div>' +
                '</div>' +
                '<div class="txt">정보를 수신하고 있습니다...</div>' +
                '</div>' +
                '</div>'
            );
        });
    </script>
@endpush