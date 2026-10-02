@foreach($products as $postedProduct)
    @php
        $postedChannels = $postedProduct->shopChannelProducts
            ->where('approval_status', 'approved')
            ->pluck('shopChannel')->filter()->unique('id')->values();
    @endphp
    <div class="popup_bx" data-id="posted-channels-{{ $postedProduct->id }}">
        <div class="pop_w"><div class="pop_inner"><div class="pop_con">
            <div class="close_btn close1">닫기</div>
            <div class="page_info type2"><div class="ttl">상품게시한 채널목록</div></div>
            <div class="conbx"><div class="con_w">
                <h3>{{ $postedProduct->product_name }}</h3>
                <div class="list_top1"><div class="count">총 <strong>{{ $postedChannels->count() }}</strong> 건</div></div>
                <div class="tb01 ovS"><table>
                    <thead><tr><th>채널코드</th><th>채널상태</th><th>채널명</th><th>채널범위</th><th>QR 코드</th><th>입장주소</th></tr></thead>
                    <tbody>
                        @forelse($postedChannels as $postedChannel)
                            @php($entryUrl = route('shop.enter', $postedChannel->channel_code))
                            <tr>
                                <td>{{ $postedChannel->channel_code }}</td>
                                <td>{{ (int) $postedChannel->status === 1 ? '운영' : '중지' }}</td>
                                <td>{{ $postedChannel->channel_name }}</td>
                                <td>{{ (int) $postedChannel->is_public === 1 ? '공개' : '비공개' }}, {{ (int) $postedChannel->is_member_only === 1 ? '회원용' : '일반용' }}</td>
                                <td><button type="button" class="pop_btn" data-pop="pop4_1_1" aria-label="{{ $postedChannel->channel_name }} QR 코드 확대">
                                    <img src="data:image/png;base64,{{ DNS2D::getBarcodePNG($entryUrl, 'QRCODE', 6, 6) }}" alt="{{ $postedChannel->channel_name }} 입장 QR 코드" width="60" height="60">
                                </button></td>
                                <td><a href="{{ $entryUrl }}" target="_blank" rel="noopener">{{ $postedChannel->channel_code }}</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6">게시된 채널이 없습니다.</td></tr>
                        @endforelse
                    </tbody>
                </table></div>
            </div></div>
        </div></div></div>
    </div>
@endforeach
