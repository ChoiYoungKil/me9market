<section class="shop-form-section shop-checkout-privacy" aria-labelledby="shop-order-privacy-title">
    <div class="shop-privacy-heading"><h2 id="shop-order-privacy-title">개인정보 수집, 이용 및 제공 등에 관한 고지</h2><button type="button" class="shop-btn small" data-privacy-expand aria-expanded="false" aria-controls="shop-order-privacy-content">전문 펼쳐보기</button></div>
    <p class="shop-privacy-intro">주문, 배송을 위해 고객님께서 입력하신 정보의 관리는 당사의 개인정보처리방침을 준용합니다.</p>
    <div id="shop-order-privacy-content" class="shop-order-privacy-content" tabindex="0" role="region" aria-label="개인정보 고지 전문">
        @foreach($privacyDocument['blocks'] as $block)
            @if($block['type'] === 'table')
                @php($headers = $block['rows'][0])
                <table class="shop-privacy-table"><caption>개인정보 처리 목적, 항목 및 근거</caption><thead><tr>@foreach($headers as $header)<th scope="col">{{ $header }}</th>@endforeach</tr></thead><tbody>
                @foreach(array_slice($block['rows'],1) as $row)
                    <tr>
                        @foreach($row as $cell)
                            @if($loop->first)
                                <th scope="row">{{ $cell }}</th>
                            @else
                                <td data-label="{{ $headers[$loop->index] }}">{{ $cell }}</td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
                </tbody></table>
            @elseif(preg_match('/^제\d+조\s/u', $block['text']))
                <h3>{{ $block['text'] }}</h3>
            @else
                <p>{{ $block['text'] }}</p>
            @endif
        @endforeach
    </div>
    <label class="shop-check shop-order-consent"><input type="checkbox" name="order_confirmed" value="1" required @checked(old('order_confirmed'))>위 고지 내용에 동의합니다.</label>
</section>
