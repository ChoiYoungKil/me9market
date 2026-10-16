@php
    $charge = $charge ?? null;
@endphp

<div class="conbx delivery-policy-form">
    <div class="con_w">
        <div class="tb01">
            <table>
                <colgroup>
                    <col width="160px">
                    <col width="">
                    <col width="160px">
                    <col width="">
                </colgroup>
                <tbody class="textL">
                    <tr>
                        <th class="w160"><span>배송구분</span></th>
                        <td colspan="3">사용자</td>
                    </tr>
                    <tr>
                        <th class="w160"><span>상태</span></th>
                        <td colspan="3">
                            <ul class="chk01">
                                <li>
                                    <input type="radio" name="status" id="delivery_status_{{ $charge->id ?? 'new' }}_1" value="1" {{ old('status', $charge->status ?? 1) == 1 ? 'checked' : '' }}>
                                    <label for="delivery_status_{{ $charge->id ?? 'new' }}_1">사용</label>
                                </li>
                                <li>
                                    <input type="radio" name="status" id="delivery_status_{{ $charge->id ?? 'new' }}_0" value="0" {{ old('status', $charge->status ?? 1) == 0 ? 'checked' : '' }}>
                                    <label for="delivery_status_{{ $charge->id ?? 'new' }}_0">중지</label>
                                </li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th class="w160"><span>배송비 명칭<em>필수</em></span></th>
                        <td colspan="3"><input type="text" name="name" value="{{ old('name', $charge->name ?? '') }}" required="required"></td>
                    </tr>
                    <tr>
                        <th class="w160"><span>지정택배사</span></th>
                        <td colspan="3"><input type="text" name="courier" value="{{ old('courier', $charge->courier ?? '자체배송') }}"></td>
                    </tr>
                    <tr>
                        <th class="w160"><span>배송비 유형</span></th>
                        <td colspan="3">
                            <select name="shipping_type" class="w310" required="required">
                                @foreach($shippingTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('shipping_type', $charge->shipping_type ?? 'free') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th class="w160"><span>배송비 결제</span></th>
                        <td colspan="3">
                            <select name="payment_type" class="w310" required="required">
                                @foreach($paymentTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('payment_type', $charge->payment_type ?? 'prepaid') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </td>
                    </tr>
                    <tr data-delivery-types="conditional,fixed,variable">
                        <th class="w160"><span data-fee-label>기본 배송비</span></th>
                        <td colspan="3"><input data-base-fee class="w160" type="text" inputmode="numeric" pattern="[0-9]*" name="base_fee" value="{{ old('base_fee', $charge->base_fee ?? 0) }}"><input data-fixed-fee class="w160" type="text" inputmode="numeric" pattern="[0-9]*" name="fixed_fee" value="{{ old('fixed_fee', $charge->fixed_fee ?? '') }}"> 원</td>
                    </tr>
                    <tr data-delivery-types="conditional">
                        <th class="w160"><span>무료배송 조건</span></th>
                        <td colspan="3">
                            <input class="w160" type="text" inputmode="numeric" pattern="[0-9]*" name="free_order_amount" value="{{ old('free_order_amount', $charge->free_order_amount ?? '') }}"> 원 이상
                            &nbsp;&nbsp;
                            <input class="w160" type="text" inputmode="numeric" pattern="[0-9]*" name="free_order_quantity" value="{{ old('free_order_quantity', $charge->free_order_quantity ?? '') }}"> 개 이상
                        </td>
                    </tr>
                    @php($rules = old('calculation_rules', $charge->calculation_rules ?? []))
                    <tr data-delivery-types="variable,tiered"><th>반복·구간 기준</th><td colspan="3">
                        <select name="calculation_rules[basis]">@foreach(['quantity' => '수량', 'amount' => '금액'] as $value => $label)<option value="{{ $value }}" {{ ($rules['basis'] ?? 'quantity') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
                        <p data-variable-only>변동 배송비: <input type="number" min="1" name="calculation_rules[repeat_every]" value="{{ $rules['repeat_every'] ?? 1 }}"> 마다 <input type="number" min="0" name="calculation_rules[repeat_fee]" value="{{ $rules['repeat_fee'] ?? 0 }}"> 원 추가</p>
                        @foreach(['start_at_one' => '반복 부과 기준을 1부터 시작', 'repeat_region' => '지역 배송비도 동일 조건으로 반복 부과'] as $key => $label)
                            <label data-variable-only><input type="hidden" name="calculation_rules[{{ $key }}]" value="0"><input type="checkbox" name="calculation_rules[{{ $key }}]" value="1" {{ !empty($rules[$key]) ? 'checked' : '' }}>{{ $label }}</label>
                        @endforeach
                    </td></tr>
                    <tr data-delivery-types="tiered"><th>구간별 배송비</th><td colspan="3">
                        <p>0부터 빈틈 없이 입력하세요. 하한 이상·상한 미만이며 마지막 상한은 비웁니다.</p>
                        <div data-shipping-tiers>
                            @foreach(($rules['tiers'] ?? [['min' => 0, 'max' => null, 'fee' => 0]]) as $index => $tier)
                                <div data-shipping-tier><input type="number" min="0" name="calculation_rules[tiers][{{ $index }}][min]" value="{{ $tier['min'] }}" aria-label="구간 하한"> 이상
                                <input type="number" min="1" name="calculation_rules[tiers][{{ $index }}][max]" value="{{ $tier['max'] ?? '' }}" aria-label="구간 상한"> 미만
                                <input type="number" min="0" name="calculation_rules[tiers][{{ $index }}][fee]" value="{{ $tier['fee'] }}" aria-label="구간 배송비"> 원 <button type="button" data-remove-tier>삭제</button></div>
                            @endforeach
                        </div><button type="button" data-add-tier>구간 추가</button>
                    </td></tr>
                    <tr><th>지역 배송</th><td colspan="3">
                        <select name="calculation_rules[region_mode]">@foreach(['none' => '추가금 사용 안함', 'two' => '2권역 (제주·도서산간 통합)', 'three' => '3권역 (제주·도서산간 분리)'] as $value => $label)<option value="{{ $value }}" {{ ($rules['region_mode'] ?? 'none') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
                        @foreach(['jeju' => '제주 (2권역은 제주·도서산간)', 'remote' => '도서산간 (3권역)', 'mainland' => '그 외 지역'] as $key => $label)
                            <p>{{ $label }} 추가금 <input type="number" min="0" name="calculation_rules[{{ $key }}_fee]" value="{{ $rules[$key.'_fee'] ?? 0 }}"> 원</p>
                        @endforeach
                        @foreach(['jeju' => '제주', 'remote' => '도서산간'] as $key => $label)
                            <p><label><input type="hidden" name="calculation_rules[block_{{ $key }}]" value="0"><input type="checkbox" name="calculation_rules[block_{{ $key }}]" value="1" {{ !empty($rules['block_'.$key]) ? 'checked' : '' }}>{{ $label }} 배송 제한</label></p>
                            <label>{{ $label }} 우편번호 (택배사 기준, 줄마다 5자리 번호 또는 시작-끝 범위)
                            <textarea name="{{ $key }}_postcodes_text" rows="3">{{ old($key.'_postcodes_text', implode("\n", $rules[$key.'_postcodes'] ?? [])) }}</textarea></label>
                        @endforeach
                    </td></tr>
                    <tr>
                        <th class="w160"><span>메모</span></th>
                        <td colspan="3"><textarea name="memo" rows="4">{{ old('memo', $charge->memo ?? '') }}</textarea></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('[data-shipping-tiers]').forEach(function (container) {
    if (container.dataset.initialized) return;
    container.dataset.initialized = '1';
    const form = container.closest('form');
    function renumber() {
        container.querySelectorAll('[data-shipping-tier]').forEach(function (row, index) {
            row.querySelectorAll('input').forEach(function (input) { input.name = input.name.replace(/\[tiers\]\[\d+\]/, '[tiers][' + index + ']'); });
        });
    }
    form.querySelector('[data-add-tier]').addEventListener('click', function () {
        const row = container.querySelector('[data-shipping-tier]').cloneNode(true);
        row.querySelectorAll('input').forEach(function (input) { input.value = ''; });
        container.appendChild(row); renumber();
    });
    container.addEventListener('click', function (event) {
        if (event.target.matches('[data-remove-tier]') && container.children.length > 1) { event.target.closest('[data-shipping-tier]').remove(); renumber(); }
    });
});
</script>

<script>
document.querySelectorAll('.delivery-policy-form').forEach(function (editor) {
    if (editor.dataset.policyInitialized) return;
    editor.dataset.policyInitialized = '1';
    const select = editor.querySelector('[name="shipping_type"]');
    const region = editor.querySelector('[name="calculation_rules[region_mode]"]');
    function refresh() {
        editor.querySelectorAll('[data-delivery-types]').forEach(function (row) {
            row.hidden = !row.dataset.deliveryTypes.split(',').includes(select.value);
            row.querySelectorAll('input,select,textarea').forEach(function (input) { input.disabled = row.hidden; });
        });
        editor.querySelectorAll('[data-variable-only]').forEach(function (element) {
            element.hidden = select.value !== 'variable';
            element.querySelectorAll('input').forEach(function (input) { input.disabled = element.hidden; });
        });
        const fixed = select.value === 'fixed';
        editor.querySelector('[data-fee-label]').textContent = fixed ? '고정 배송비' : '기본 배송비';
        editor.querySelector('[data-fixed-fee]').hidden = !fixed;
        editor.querySelector('[data-fixed-fee]').disabled = !fixed;
        editor.querySelector('[data-base-fee]').hidden = fixed;
        editor.querySelector('[data-base-fee]').disabled = fixed || !['conditional','variable'].includes(select.value);
        ['jeju_fee','remote_fee','mainland_fee'].forEach(function (key) {
            const input = editor.querySelector('[name="calculation_rules[' + key + ']"]');
            input.closest('p').hidden = region.value === 'none' || (key === 'remote_fee' && region.value === 'two');
            input.disabled = input.closest('p').hidden;
        });
    }
    select.addEventListener('change', refresh);
    region.addEventListener('change', refresh);
    refresh();
});
</script>
