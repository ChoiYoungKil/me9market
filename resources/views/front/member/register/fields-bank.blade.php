<div class="member-field member-wide">
    <label for="bank_name">{{ $bankLabel ?? '정산용 계좌번호' }}</label>
    <div class="member-bank">
        <select id="bank_name" name="bank_name" required><option value="">은행선택</option>
            @foreach(['국민은행','신한은행','우리은행','하나은행','농협은행','IBK기업은행','수협은행','SC제일은행','씨티은행','대구은행','부산은행','광주은행','제주은행','전북은행','경남은행','새마을금고','신협','우체국','케이뱅크','카카오뱅크','토스뱅크'] as $bank)
                <option value="{{ $bank }}" @selected(($businessDetails->bank_name ?? '') === $bank)>{{ $bank }}</option>
            @endforeach
        </select>
        <input name="account_number" aria-label="정산 계좌번호" placeholder="계좌번호 (숫자만 입력)" inputmode="numeric" pattern="[0-9]{6,30}" maxlength="30" value="{{ $businessDetails->bank_account_number ?? '' }}" required>
        <input name="account_holder_name" aria-label="예금주" placeholder="예금주" maxlength="100" value="{{ $businessDetails->bank_account_holder_name ?? '' }}" required>
    </div>
</div>
