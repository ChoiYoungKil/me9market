<div class="member-field member-wide">
    <label for="{{ $field }}">{{ $label }}</label>
    <div>
        <input type="file" id="{{ $field }}" name="{{ $field }}" accept=".jpg,.jpeg,.png,.pdf" @required(($required ?? false) && empty($businessDetails?->$field))>
        <p class="member-help" data-file-name="{{ $field }}">JPG, PNG, PDF · 최대 5MB</p>
        @if(!empty($businessDetails?->$field))
            <a class="member-document" href="{{ route('front.member.register.document', $field === 'address_proof_image' ? 'license' : 'bankbook') }}">저장된 {{ $label }} 내려받기</a>
        @endif
    </div>
</div>
