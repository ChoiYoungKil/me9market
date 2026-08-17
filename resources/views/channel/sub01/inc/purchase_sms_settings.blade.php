@php
    $shop = $shop ?? null;
    $smsEnabledValue = old('use_purchase_sms', ($shop?->use_purchase_sms ?? false) ? '1' : '0');
    $templates = is_array($shop?->purchase_sms_templates ?? null) ? $shop->purchase_sms_templates : [];
    $smsTemplateRows = [
        'purchase' => '상품구매알림',
        'purchase_confirmed' => '상품구매확정알림',
        'cancel' => '상품취소알림',
        'return' => '상품반품알림',
    ];
@endphp

<div class="ttl01 mt40">구매현황 문자알림</div>
<div class="tb01">
    <table>
        <colgroup>
            <col width="175px">
            <col width="">
        </colgroup>
        <tbody class="textL">
            <tr>
                <th class="w160"><span>문자알림여부</span></th>
                <td>
                    <ul class="chk01">
                        <li>
                            <input type="radio" name="use_purchase_sms" value="0" id="purchase_sms_off" {{ $smsEnabledValue == '0' ? 'checked' : '' }}>
                            <label for="purchase_sms_off">미사용</label>
                        </li>
                        <li>
                            <input type="radio" name="use_purchase_sms" value="1" id="purchase_sms_on" {{ $smsEnabledValue == '1' ? 'checked' : '' }}>
                            <label for="purchase_sms_on">사용</label>
                        </li>
                    </ul>
                    <div class="col2 fs2 mt5">SMS 사용 내역은 포인트 즉시 차감이 아니라 후불 청구 항목으로 기록됩니다.</div>
                </td>
            </tr>
            <tr class="purchase_sms_row" style="display: {{ $smsEnabledValue == '1' ? 'table-row' : 'none' }};">
                <th class="w160"><span>치환항목</span></th>
                <td>
                    <span class="col2 fs2">@{{구매자}}, @{{구매상품}}, @{{구매수량}}, @{{구매액}}, @{{구매날짜}}</span>
                </td>
            </tr>
            @foreach($smsTemplateRows as $templateKey => $templateLabel)
                <tr class="purchase_sms_row" style="display: {{ $smsEnabledValue == '1' ? 'table-row' : 'none' }};">
                    <th class="w160"><span>{{ $templateLabel }}</span></th>
                    <td>
                        <textarea name="purchase_sms_templates[{{ $templateKey }}]" class="wFull" rows="3">{{ old('purchase_sms_templates.' . $templateKey, $templates[$templateKey] ?? ($templateKey === 'purchase' ? ($templates['customer'] ?? '') : '')) }}</textarea>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
