@php
    $charge = $charge ?? null;
@endphp

<div class="conbx">
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
                    <tr>
                        <th class="w160"><span>기본 배송비</span></th>
                        <td><input class="w160" type="text" inputmode="numeric" pattern="[0-9]*" name="base_fee" value="{{ old('base_fee', $charge->base_fee ?? 0) }}"> 원</td>
                        <th class="w160"><span>고정 배송비</span></th>
                        <td><input class="w160" type="text" inputmode="numeric" pattern="[0-9]*" name="fixed_fee" value="{{ old('fixed_fee', $charge->fixed_fee ?? '') }}"> 원</td>
                    </tr>
                    <tr>
                        <th class="w160"><span>무료배송 조건</span></th>
                        <td colspan="3">
                            <input class="w160" type="text" inputmode="numeric" pattern="[0-9]*" name="free_order_amount" value="{{ old('free_order_amount', $charge->free_order_amount ?? '') }}"> 원 이상
                            &nbsp;&nbsp;
                            <input class="w160" type="text" inputmode="numeric" pattern="[0-9]*" name="free_order_quantity" value="{{ old('free_order_quantity', $charge->free_order_quantity ?? '') }}"> 개 이상
                        </td>
                    </tr>
                    <tr>
                        <th class="w160"><span>메모</span></th>
                        <td colspan="3"><textarea name="memo" rows="4">{{ old('memo', $charge->memo ?? '') }}</textarea></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
