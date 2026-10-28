<div class="member-field member-wide">
    <label for="zipcode">{{ $addressLabel ?? '주소 (배송지)' }}</label>
    <div class="member-address">
        <div class="member-inline"><input id="zipcode" name="zipcode" placeholder="우편번호" aria-label="우편번호" value="{{ $postcode ?? '' }}" pattern="[0-9]{5}" maxlength="5" required readonly><button type="button" data-postcode>우편번호찾기</button><button type="button" data-manual-address>주소 직접입력</button></div>
        <input id="address1" name="address1" placeholder="주소" aria-label="주소" value="{{ $address ?? '' }}" maxlength="255" required readonly>
        <input id="address2" name="address2" placeholder="상세 주소" aria-label="상세 주소" value="{{ $addressDetail ?? '' }}" maxlength="255" required>
    </div>
</div>
