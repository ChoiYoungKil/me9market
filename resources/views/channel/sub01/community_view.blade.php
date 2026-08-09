@extends('layouts.channel')

@section('content')
    @php
        $page_type = "sub";
        $dep1_id = "01";
        $dep1_tit = "Shop채널관리";
    @endphp
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
                        <li><a href="{{ route('channel.shop_info') }}"><span>Shop채널 정보</span></a></li>
                        <li><a href="{{ route('channel.product_own') }}"><span>판매상품</span></a></li>
                        <li><a href="{{ route('channel.shop_community', ['shop_id' => request('shop_id')]) }}"
                                class="on"><span>커뮤니티</span></a></li>
                    </ul>
                </div>
                <div class="conbx">
                    <div class="con_w">
                        <!-- 제목 영역 -->
                        <div style="padding: 30px 0 20px 0; border-bottom: 2px solid #333;">
                            <h2 style="font-size: 24px; font-weight: bold; margin: 0 0 15px 0; line-height: 1.4;">
                                {{ $notice->title }}
                            </h2>
                            <div style="color: #666; font-size: 14px;">
                                작성일 {{ $notice->created_at->format('Y-m-d') }}
                            </div>
                        </div>

                        <!-- 내용 영역 -->
                        <div style="padding: 40px 0; min-height: 300px; line-height: 1.8; font-size: 15px;">
                            {!! $notice->content !!}
                        </div>

                        <!-- 첨부파일 영역 -->
                        @if($notice->attachment)
                            <div style="padding: 20px; background: #f9f9f9; border-radius: 5px; margin-bottom: 30px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span
                                        style="background: #ff0000; color: white; padding: 4px 12px; border-radius: 3px; font-size: 12px; font-weight: bold;">1</span>
                                    <span style="color: #666; margin-right: 10px;">첨부파일</span>
                                    <a href="{{ asset('uploads/notices/' . $notice->attachment) }}" download
                                        style="color: #0066cc; text-decoration: underline;">
                                        {{ $notice->attachment }}
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- 하단 버튼 영역 -->
                        <div
                            style="display: flex; justify-content: center; gap: 10px; margin: 40px 0; padding: 30px 0; border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;">
                            <a href="{{ route('channel.shop_community', ['shop_id' => request('shop_id')]) }}"
                                style="background: #000; color: white; padding: 12px 40px; text-decoration: none; border-radius: 3px; font-size: 14px;">
                                목록
                            </a>
                            <a href="{{ route('channel.community.update', ['id' => $notice->id, 'shop_id' => request('shop_id')]) }}"
                                style="background: #0066cc; color: white; padding: 12px 40px; text-decoration: none; border-radius: 3px; font-size: 14px;">
                                수정
                            </a>
                            <a href="#" class="delete-notice" data-id="{{ $notice->id }}"
                                style="background: #ff0000; color: white; padding: 12px 40px; text-decoration: none; border-radius: 3px; font-size: 14px;">
                                삭제
                            </a>
                        </div>

                        <!-- 이전글/다음글 영역 -->
                        <div style="border-top: 1px solid #ddd;">
                            @if($nextNotice)
                                <div style="display: flex; border-bottom: 1px solid #ddd; padding: 15px 0;">
                                    <div style="width: 100px; font-weight: bold; color: #333;">다음글</div>
                                    <div style="flex: 1;">
                                        <a href="{{ route('channel.community.view', ['id' => $nextNotice->id, 'shop_id' => request('shop_id')]) }}"
                                            style="color: #333; text-decoration: none;">
                                            {{ $nextNotice->title }}
                                        </a>
                                    </div>
                                </div>
                            @endif

                            @if($prevNotice)
                                <div style="display: flex; padding: 15px 0;">
                                    <div style="width: 100px; font-weight: bold; color: #333;">이전글</div>
                                    <div style="flex: 1;">
                                        <a href="{{ route('channel.community.view', ['id' => $prevNotice->id, 'shop_id' => request('shop_id')]) }}"
                                            style="color: #333; text-decoration: none;">
                                            {{ $prevNotice->title }}
                                        </a>
                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <form id="deleteForm" action="{{ route('channel.community.delete', $notice->id) }}" method="POST"
        style="display: none;">
        @csrf
    </form>
@endsection

@push('scripts')
    <script>
        $('.delete-notice').click(function (e) {
            e.preventDefault();
            if (confirm('정말 삭제하시겠습니까?')) {
                $('#deleteForm').submit();
            }
        });
    </script>
@endpush