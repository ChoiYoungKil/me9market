<div class="info-file">
    @if($path)
        <span>등록된 {{ $label }} · {{ strtoupper(pathinfo($path, PATHINFO_EXTENSION)) }}</span>
        <a href="{{ route('channel.info.document',['kind'=>$kind,'mode'=>'view']) }}" target="_blank" rel="noopener">파일 보기</a>
        <a href="{{ route('channel.info.document',['kind'=>$kind,'mode'=>'download']) }}">다운로드</a>
    @else<span>등록된 파일 없음</span>@endif
    <label for="{{ $field }}">{{ $path ? '파일 교체' : '파일 업로드' }}<input id="{{ $field }}" type="file" name="{{ $field }}" accept="image/jpeg,image/png,application/pdf"></label>
    <button type="button" class="info-small-button" data-clear-file="{{ $field }}">선택 취소</button>
    <small>JPG·PNG·PDF, 최대 5MB. 선택 후 저장하거나 인증 요청을 해 주세요.</small>
</div>
