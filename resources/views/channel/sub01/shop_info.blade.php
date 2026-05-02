@extends('layouts.channel')

@php
    $dep1_id = "01";
    $dep1_tit = "Shop채널관리";
@endphp

@section('page_type', 'sub')

@section('content')
    <div id="container_w">
        <div id="contents">
            <div class="row">
                <div class="box box1">
                    <div class="page_info">
                        <div class="ttl">Shop채널 상세페이지</div>
                        <ul class="dep">
                            <li>HOME</li>
                            <li>Shop채널관리</li>
                            <li>Shop채널 상세페이지</li>
                        </ul>
                    </div>
                    <div class="tab_bx1">
                        <ul>
                            <li><a href="#" class="on"><span>Shop채널 정보</span></a></li>
                            <li><a href="{{ route('channel.shop_product01', ['id' => $shop->id]) }}"><span>판매상품</span></a></li>
                            <li><a href="{{ route('channel.shop_community', ['id' => $shop->id]) }}"><span>커뮤니티</span></a></li>
                        </ul>
                    </div>
                    <div class="conbx">
                        <div class="con_w">
                            <div class="ttl01">Shop 채널 기본정보</div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="175px">
                                        <col width="">
                                        <col width="175px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>Shop 채널코드</span></th>
                                            <td>{{ $shop->channel_code }}</td>
                                            <th class="w160"><span>등록일</span></th>
                                            <td>{{ $shop->created_at->format('Y-m-d H:i') }}</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>채널상태</span></th>
                                            <td>{{ $shop->status == 1 ? '운영' : '중지' }}</td>
                                            <th class="w160"><span>공개여부</span></th>
                                            <td>{{ $shop->is_public == 1 ? '공개' : '비공개' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>채널명</span></th>
                                            <td colspan="3">{{ $shop->channel_name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>구매권한</span></th>
                                            <td>{{ $shop->is_member_only == 1 ? '회원전용' : '비회원 구매가능' }}</td>
                                        @if($shop->use_period_type == 1)
                                            <tr>
                                                <th class="w160"><span>채널사용주기</span></th>
                                                <td colspan="3">
                                                    기간제 / 
                                                    {{ $shop->start_at ? date('Y-m-d H시', strtotime($shop->start_at)) : '-' }} 
                                                    ~ 
                                                    {{ $shop->end_at ? date('Y-m-d H시', strtotime($shop->end_at)) : '-' }}
                                                </td>
                                            </tr>
                                        @endif
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>카피라이트</span></th>
                                            <td colspan="3">{{ $shop->copyright }}</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>그룹 keyword</span></th>
                                            <td colspan="3">
                                                <ul class="tag_list" style="display: flex; gap: 5px; list-style: none; padding: 0;">
                                                    @php $keywords = json_decode($shop->keywords, true) ?? []; @endphp
                                                    @foreach($keywords as $keyword)
                                                        <li style="background-color: #eeeeee; color: #666; padding: 2px 10px; border-radius: 10px; font-size: 11px;">#{{ $keyword }}</li>
                                                    @endforeach
                                                </ul>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @if($shop->use_admin == 1)
                        <div class="con_w">
                            <div class="ttl01">Shop 채널 (모니터링) 관리자 정보</div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="175px">
                                        <col width="">
                                        <col width="175px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>Shop 채널 관리자 여부</span></th>
                                            <td>사용</td>
                                            <th class="w160"><span>Shop 채널 관리자 성명</span></th>
                                            <td>{{ $shop->admin_name }}</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>로그인 ID</span></th>
                                            <td>{{ $shop->admin_login_id }}</td>
                                            <th class="w160"><span>로그인 PW</span></th>
                                            <td>********</td>
                                        </tr>
                                        <tr>
                                            <th class="w160"><span>정산방법</span></th>
                                            <td>{{ $shop->settlement_type == 1 ? '전체 판매 수수료' : '판매 개당 비용' }}</td>
                                            <th class="w160"><span>정산요율</span></th>
                                            <td>{{ $shop->settlement_rate }}{{ $shop->settlement_type == 1 ? '%' : '원' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                        @if($shop->use_og == 1)
                        <div class="con_w">
                            <div class="ttl01">OG TAG</div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="175px">
                                        <col width="100px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        <tr>
                                            <th class="w160"><span>OG 사용여부</span></th>
                                            <td colspan="2">사용</td>
                                        </tr>
                                        <tr>
                                            <th class="w160" rowspan="3"><span>OG TAG</span></th>
                                            <td>Title</td>
                                            <td>{{ $shop->og_title }}</td>
                                        </tr>
                                        <tr>
                                            <td>Description</td>
                                            <td>{{ $shop->og_description }}</td>
                                        </tr>
                                        <tr>
                                            <td>Image</td>
                                            <td>
                                                @if($shop->og_image)
                                                    <img src="{{ asset($shop->og_image) }}" style="max-width: 200px;">
                                                @else
                                                    등록된 이미지가 없습니다.
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                        @if($shop->use_logo == 1 || $shop->use_banner == 1)
                        <div class="con_w">
                            <div class="ttl01">이미지</div>
                            <div class="tb01">
                                <table>
                                    <colgroup>
                                        <col width="175px">
                                        <col width="100px">
                                        <col width="">
                                    </colgroup>
                                    <tbody class="textL">
                                        @if($shop->use_logo == 1)
                                            <tr>
                                                <th class="w160"><span>로고 이미지</span></th>
                                                <td colspan="2">
                                                    @if($shop->logo_image)
                                                        <img src="{{ asset($shop->logo_image) }}" style="max-width: 200px;">
                                                    @else
                                                        등록된 로고가 없습니다.
                                                    @endif
                                                </td>
                                            </tr>
                                        @endif

                                        @if($shop->use_banner == 1)
                                            @php $banners = json_decode($shop->banner_images, true) ?? []; @endphp
                                            <tr>
                                                <th class="w160" rowspan="{{ max(1, count($banners)) }}"><span>배너 이미지</span></th>
                                                @forelse($banners as $index => $banner)
                                                    @if($index > 0) <tr> @endif
                                                    <td>#{{ $index + 1 }}</td>
                                                    <td><img src="{{ asset($banner) }}" style="max-height: 100px;"></td>
                                                    @if($index > 0) </tr> @endif
                                                @empty
                                                    <td colspan="2">-</td>
                                                @endforelse
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <div class="btm_btn" style="display: flex; justify-content: center; gap: 10px; margin-top: 20px;">
                    <a href="{{ route('channel.info_update', ['id' => $shop->id]) }}" class="col2" style="width: 140px; height: 40px; line-height: 40px; text-align: center; border-radius: 5px; color: #fff; font-weight: bold;">정보수정</a>
                    <a href="#" onclick="alert('준비중입니다'); return false;" class="col7" style="width: 140px; height: 40px; line-height: 40px; text-align: center; border-radius: 5px; color: #fff; font-weight: bold; background: #007bff;">Shop채널보기</a>
                    <a href="{{ route('channel.shop_list') }}" class="col5" style="width: 140px; height: 40px; line-height: 40px; text-align: center; border-radius: 5px; color: #fff; font-weight: bold;">목록</a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script type="text/javascript">
    </script>
@endpush