@extends('layouts.channel')

@section('page_type', 'sub')

@php
    $dep1_id = "02";
    $dep1_tit = "상품관리";
    
    $mainImage = $product->images->first();
    $imageUrl = $mainImage ? asset('front/images/product_images/small/' . $mainImage->image) : asset('channel_assets/images/sub/thum01.jpg');
@endphp

@section('content')
    <div id="contents">
        <div class="row">
            <div class="box box1">
                <div class="page_info">
                    <div class="ttl">자사 상품 수정</div>
                    <ul class="dep">
                        <li>HOME</li>
                        <li>상품관리</li>
                        <li>자사 상품 수정</li>
                    </ul>
                </div>
                
                <div class="tab_bx1">
                    <ul>
                        <li><a href="{{ route('channel.product_own') }}" class="on"><span>자사상품관리</span></a></li>
                        <li><a href="{{ route('channel.product_public') }}"><span>공개상품관리</span></a></li>
                        <li><a href="{{ route('channel.product_partial') }}"><span>부분공개상품관리</span></a></li>
                        <li><a href="{{ route('channel.product_request') }}"><span>판매 요청 관리</span></a></li>
                    </ul>
                </div>

                <div class="conbx">
                    <form action="{{ route('channel.product.base.update', $product->id) }}" method="POST">
                        @csrf
                        <div class="con_w">
                            <div class="ttl01">기본 정보</div>
                            <div class="tb01 textL">
                                <table>
                                    <colgroup>
                                        <col width="160px"><col width=""><col width="160px"><col width="">
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <th>상품코드</th>
                                            <td>{{ $product->product_code }}</td>
                                            <th>상품상태</th>
                                            <td>
                                                <ul class="chk01">
                                                    <li>
                                                        <input type="radio" name="status" id="status_1" value="1" {{ $product->status == 1 ? 'checked' : '' }}>
                                                        <label for="status_1">판매</label>
                                                    </li>
                                                    <li>
                                                        <input type="radio" name="status" id="status_0" value="0" {{ $product->status == 0 ? 'checked' : '' }}>
                                                        <label for="status_0">중지</label>
                                                    </li>
                                                </ul>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>섹션</th>
                                            <td>
                                                <select name="section_id" required>
                                                    <option value="">섹션 선택</option>
                                                    @foreach($sections as $section)
                                                        <option value="{{ $section->id }}" {{ $product->section_id == $section->id ? 'selected' : '' }}>{{ $section->name }}</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <th>카테고리</th>
                                            <td>
                                                <select name="category_id" required>
                                                    <option value="">카테고리 선택</option>
                                                    @foreach($categories as $category)
                                                        <optgroup label="{{ $category->category_name }}">
                                                            @foreach($category->subCategories as $sub)
                                                                <option value="{{ $sub->id }}" {{ $product->category_id == $sub->id ? 'selected' : '' }}>{{ $sub->category_name }}</option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endforeach
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>상품명</th>
                                            <td colspan="3">
                                                <input type="text" name="product_name" value="{{ $product->product_name }}" class="wFull" required>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>기본 원가</th>
                                            <td>
                                                <input type="text" name="product_price" value="{{ $product->product_price }}" class="w160" required> 원
                                            </td>
                                            <th>상품 색상</th>
                                            <td>
                                                <input type="text" name="product_color" value="{{ $product->product_color }}" class="w160">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="con_w">
                            <div class="ttl01">판매 정책</div>
                            <div class="tb01 textL">
                                <table>
                                    <colgroup>
                                        <col width="160px"><col width=""><col width="160px"><col width="">
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <th>공개 범위</th>
                                            <td>
                                                <select name="is_public">
                                                    <option value="1" {{ $product->is_public == 1 ? 'selected' : '' }}>공개 (다른 판매자 판매 가능)</option>
                                                    <option value="0" {{ $product->is_public == 0 ? 'selected' : '' }}>비공개 (나만 판매 가능)</option>
                                                </select>
                                            </td>
                                            <th>판매 승인 정책</th>
                                            <td>
                                                <select name="is_partial">
                                                    <option value="0" {{ $product->is_partial == 0 ? 'selected' : '' }}>전체 허용 (누구나 판매 가능)</option>
                                                    <option value="1" {{ $product->is_partial == 1 ? 'selected' : '' }}>승인 필요 (요청 시 개별 허용)</option>
                                                </select>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="con_w">
                            <div class="ttl01">상품 이미지</div>
                            <div class="list01">
                                <ul>
                                    <li>
                                        <div class="img_bx" style="background-image:url({{ $imageUrl }})"></div>
                                        <div class="txt_bx">
                                            <strong>{{ $product->product_name }}</strong>
                                            <p class="mt5" style="font-size: 12px; color: #888;">* 이미지 변경은 현재 지원되지 않습니다.</p>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="btm_btn mt20">
                            <a href="{{ route('channel.product_own') }}" class="col5">목록으로</a>
                            <button type="submit" class="btn01 col2" style="border:none; cursor:pointer; height: 50px; padding: 0 40px; font-weight: bold; font-size: 16px;">수정 완료</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
