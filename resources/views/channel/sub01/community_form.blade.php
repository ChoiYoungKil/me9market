@extends('layouts.channel')
@section('page_type','sub')
@php $dep1_id='01';$editing=isset($notice); @endphp
@push('styles')<link rel="stylesheet" href="{{ asset('channel_assets/css/shop-configuration.css') }}?v={{ filemtime(public_path('channel_assets/css/shop-configuration.css')) }}">@endpush
@section('content')
<div id="contents"><div class="channel-shop" id="shop-community-form"><header><p>MY CHANNEL</p><h1>{{ $editing?'커뮤니티 수정':'커뮤니티 글쓰기' }}</h1><p>{{ $shop->channel_name }}</p></header>
@include('channel.sub01.inc.community_tabs')
@if($errors->any())<div class="shop-feedback error" role="alert">@foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
<section class="shop-panel"><form method="POST" enctype="multipart/form-data" action="{{ $editing?route('channel.community.update.submit',$notice->id):route('channel.community.register.submit') }}" id="community-form">@csrf<input type="hidden" name="shop_id" value="{{ $shopId }}">
<div class="shop-fields"><div><span class="field-label">등록일</span>{{ $editing?$notice->created_at->format('Y-m-d'):now()->format('Y-m-d') }}</div><div><span class="field-label">작성자</span>{{ $shop->channel_name }}</div>
<fieldset class="wide"><legend>분류</legend><label><input type="radio" name="type" value="notice" @checked(old('type',$notice->type??'notice')==='notice')>공지</label><label><input type="radio" name="type" value="general" @checked(old('type',$notice->type??'notice')==='general')>일반</label></fieldset>
<label class="wide">제목<input name="title" maxlength="500" value="{{ old('title',$notice->title??'') }}" required></label>
<div class="wide"><label id="community-content-label">내용</label><div class="editor-toolbar" role="toolbar" aria-label="내용 서식"><button type="button" data-editor-command="bold">굵게</button><button type="button" data-editor-command="italic">기울임</button><button type="button" data-editor-command="insertUnorderedList">목록</button><button type="button" data-editor-command="removeFormat">서식 지우기</button></div><div id="community-editor" contenteditable="true" role="textbox" aria-labelledby="community-content-label" aria-multiline="true">{!! app(\App\Services\ShopCommunity::class)->clean(old('content',$notice->content??'')) !!}</div><textarea name="content" hidden>{{ old('content',$notice->content??'') }}</textarea><p class="shop-feedback error" data-editor-error role="alert" hidden></p></div>
<label class="wide">첨부파일<input type="file" name="attachment" accept=".pdf,.txt,.csv,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.webp,.zip"><small>문서·이미지·ZIP, 최대 10MB</small></label>
@if($editing&&$notice->attachment)<div class="wide"><a href="{{ route('channel.community.attachment',$notice->id) }}">현재 첨부파일 다운로드</a><label><input type="checkbox" name="delete_attachment" value="1">현재 첨부파일 삭제</label><p class="shop-help">새 파일을 선택하면 기존 첨부파일이 교체됩니다.</p></div>@endif
</div><div class="shop-actions"><button type="submit">{{ $editing?'수정하기':'등록하기' }}</button><a class="secondary" href="{{ route('channel.shop_community',['shop_id'=>$shopId]) }}">목록</a></div></form></section>
</div></div>
@endsection
@push('scripts')<script src="{{ asset('channel_assets/js/shop-community.js') }}?v={{ filemtime(public_path('channel_assets/js/shop-community.js')) }}" defer></script>@endpush
