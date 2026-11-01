@extends('admin.layout.layout')
@section('content')
<div id="contents" class="admin-workspace admin-settings-list">
    <div class="admin-toolbar"><h1>배너 관리</h1><div class="admin-actions"><a class="admin-btn primary" href="{{ url('admin/add-edit-banner') }}">배너 등록</a></div></div>
    @include('admin.shared.messages')
    <section class="admin-panel admin-setting-panel">
<table id="banners" class="admin-table">
<colgroup><col style="width:64px"><col style="width:136px"><col style="width:80px"><col style="width:180px"><col><col style="width:260px"><col style="width:80px"><col style="width:164px"></colgroup>
                                    <thead>
                                        <tr>
                                            <th>번호</th>
                                            <th>이미지</th>
                                            <th>유형</th>
                                            <th>연결 주소</th>
                                            <th>제목</th>
                                            <th>이미지 설명</th>
                                            <th>상태</th>
                                            <th>관리</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($banners as $banner)
                                            <tr>
                                                <td>{{ $banner['id'] }}</td>
                                                <td>
                                                    <img class="admin-setting-banner" alt="{{ $banner['alt'] ?: $banner['title'] }}"
                                                        src="{{ asset('front/images/banner_images/' . $banner['image']) }}">
                                                </td>
                                                <td>{{ ['Slider'=>'슬라이드','Fix'=>'고정'][$banner['type']] ?? $banner['type'] }}</td>
                                                <td><span class="admin-banner-text" title="{{ $banner['link'] }}">{{ $banner['link'] }}</span></td>
                                                <td><span class="admin-banner-text" title="{{ $banner['title'] }}">{{ $banner['title'] }}</span></td>
                                                <td><span class="admin-banner-text" title="{{ $banner['alt'] }}">{{ $banner['alt'] }}</span></td>
                                                <td>
                                                    @if ($banner['status'] == 1)
                                                        <a class="updateBannerStatus" id="banner-{{ $banner['id'] }}"
                                                            banner_id="{{ $banner['id'] }}" href="javascript:void(0)">
                                                            <i class="admin-setting-status enabled" status="Active">사용</i>
                                                        </a>
                                                    @else
                                                        <a class="updateBannerStatus" id="banner-{{ $banner['id'] }}"
                                                            banner_id="{{ $banner['id'] }}" href="javascript:void(0)">
                                                            <i class="admin-setting-status disabled" status="Inactive">중지</i>
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ url('admin/add-edit-banner/' . $banner['id']) }}" class="admin-btn primary">수정</a>

                                                    <a href="JavaScript:void(0)" class="admin-btn danger confirmDelete" module="banner"
                                                        moduleid="{{ $banner['id'] }}">
                                                        삭제
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
    </section>
</div>
@endsection
