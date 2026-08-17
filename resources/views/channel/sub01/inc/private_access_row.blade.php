@php
    $privateAccessRowsValue = (string) ($privateAccessRowsValue ?? '');
    $privateAccessCount = collect(preg_split('/\r\n|\r|\n/', $privateAccessRowsValue))
        ->filter(fn ($line) => trim($line) !== '')
        ->count();
@endphp

<tr class="private_access_row" style="display: {{ !empty($privateAccessVisible) ? 'table-row' : 'none' }};">
    <th class="w160"><span>접속가능인원</span></th>
    <td colspan="3">
        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
            <a href="{{ url()->current() }}" class="btn02 col5 private_access_open" data-pop="pop_private_access_manager">인원관리</a>
            <span class="col2 fs2">등록 인원: <strong id="private_access_count">{{ $privateAccessCount }}</strong>명</span>
        </div>
        <div class="col2 fs2 mt5">비공개 채널은 휴대폰번호와 입장코드가 등록된 고객만 접속할 수 있습니다.</div>
    </td>
</tr>
