@extends('layouts.admin')
@section('page_type','sub')
@push('styles')<link rel="stylesheet" href="/channel_assets/css/order-workspace.css?v={{ filemtime(public_path('channel_assets/css/order-workspace.css')) }}"><link rel="stylesheet" href="/channel_assets/css/settlement-workspace.css?v={{ filemtime(public_path('channel_assets/css/settlement-workspace.css')) }}">@endpush
@section('content')
<div id="contents"><div class="order-workspace settlement-workspace"><div class="order-page-heading"><div><p>{{ $shop->channel_code }} / {{ $period }}</p><h1>{{ $data['kind']==='billing'?'채널 청구액 상세 목록':'채널 지급액 상세 목록' }}</h1></div></div>
<section class="order-panel"><div class="order-toolbar"><a class="order-button" href="{{ request()->fullUrlWithQuery(['download'=>1]) }}">엑셀출력</a><button type="button" onclick="window.close()">창닫기</button></div>
<div class="order-table-scroll" tabindex="0"><table class="order-table"><thead><tr>@foreach($headers as $header)<th>{{ $header }}</th>@endforeach</tr></thead><tbody>@forelse($rows as $row)<tr>@foreach($row as $value)<td>{{ is_numeric($value)?number_format($value):$value }}</td>@endforeach</tr>@empty<tr><td colspan="{{ count($headers) }}">상세 내역이 없습니다.</td></tr>@endforelse</tbody><tfoot><tr><th colspan="{{ count($headers)-1 }}">합계</th><td>{{ number_format($rows->sum(fn($row)=>$row[count($headers)-1])) }}</td></tr></tfoot></table></div>
</section></div></div>
@endsection
