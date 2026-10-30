<!doctype html><html lang="ko"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>{{ $document['title'] }} · Me9 market</title>
<style>body{margin:0;background:#f5f5fa;color:#252b3d;font:16px/1.85 system-ui,sans-serif}main{max-width:900px;margin:40px auto;padding:32px;background:#fff;border:1px solid #e3e4ee;border-radius:12px}header{border-bottom:1px solid #e3e4ee;margin-bottom:26px;padding-bottom:20px}h1{font-size:25px;line-height:1.5}p{white-space:pre-wrap;overflow-wrap:anywhere}header p{font-size:13px;color:#666}a{color:#51489c}table{border-collapse:collapse;width:100%;font-size:14px}td{border:1px solid #dce0ea;padding:12px;white-space:pre-wrap;vertical-align:top}.scroll{overflow-x:auto}@media(max-width:640px){main{margin:0;padding:24px 18px;border:0;border-radius:0}h1{font-size:22px}table{min-width:640px}}</style></head><body><main>
<header><a href="{{ url('/') }}">Me9 market</a><h1>{{ $document['title'] }}</h1><p>제공받은 {{ $document['version'] }} 수정안 원문 · 미확정 항목 포함</p></header>
@foreach($document['blocks'] as $block)
    @if($block['type'] === 'table')<div class="scroll"><table><tbody>@foreach($block['rows'] as $row)<tr>@foreach($row as $cell)<td>{{ $cell }}</td>@endforeach</tr>@endforeach</tbody></table></div>
    @else<p>{{ $block['text'] }}</p>@endif
@endforeach
</main></body></html>
