@php
    $formKey=$policy ? 'edit-'.$policy->id : 'create';
    $oldInput=old('form_key')===$formKey ? session()->getOldInput() : [];
    $value=static function($key,$default='') use($oldInput){$v=array_key_exists($key,$oldInput) ? $oldInput[$key] : $default;return $v===null ? '' : (is_string($v) ? $v : $default);};
@endphp
<input type="hidden" name="form_key" value="{{ $formKey }}">
<fieldset><legend>설정구분</legend><div class="order-checks">@foreach(['default'=>'기본','custom'=>'사용자'] as $key=>$label)<label><input type="radio" name="type" value="{{ $key }}" @checked($value('type',$policy?->type ?? 'default')===$key)>{{ $label }}</label>@endforeach</div></fieldset>
<fieldset><legend>상태</legend><div class="order-checks">@foreach(['active'=>'사용','inactive'=>'중지'] as $key=>$label)<label><input type="radio" name="status" value="{{ $key }}" @checked($value('status',$policy?->status ?? 'active')===$key)>{{ $label }}</label>@endforeach</div></fieldset>
<label>취소/환불안내 명칭 *<input name="name" value="{{ $value('name',$policy?->name ?? '') }}" maxlength="255" required></label>
<div class="refund-content-label" data-refund-editor>
<label for="refund-content-{{ $policy?->id ?? 'new' }}">취소/환불안내 내용</label>
<textarea id="refund-content-{{ $policy?->id ?? 'new' }}" name="content" rows="12" maxlength="15000">{{ $value('content',$policy?->content ?? '') }}</textarea>
<div data-refund-toolbar hidden>
    <span class="ql-formats"><select class="ql-header" aria-label="문단 형식"><option value="">일반 문단</option><option value="2">제목 2</option><option value="3">제목 3</option></select></span>
    <span class="ql-formats"><button class="ql-bold" type="button" aria-label="굵게" title="굵게"></button><button class="ql-italic" type="button" aria-label="기울임" title="기울임"></button><button class="ql-underline" type="button" aria-label="밑줄" title="밑줄"></button><button class="ql-strike" type="button" aria-label="취소선" title="취소선"></button></span>
    <span class="ql-formats"><button class="ql-list" value="ordered" type="button" aria-label="번호 목록" title="번호 목록"></button><button class="ql-list" value="bullet" type="button" aria-label="글머리 목록" title="글머리 목록"></button><button class="ql-blockquote" type="button" aria-label="인용문" title="인용문"></button></span>
    <span class="ql-formats"><button class="ql-link" type="button" aria-label="링크" title="링크"></button><button class="ql-clean" type="button" aria-label="서식 지우기" title="서식 지우기"></button></span>
</div>
<div data-refund-editor-body hidden></div>
<template data-refund-editor-initial>{!! app(\App\Services\RefundPolicyContent::class)->editorHtml($value('content',$policy?->content ?? '')) !!}</template>
</div>
<p class="refund-help">안내는 상품에서 선택하여 적용합니다. 내용이 비어 있거나 중지된 안내 대신에는 공통 기본 안내가 표시됩니다.</p>
@if(old('form_key')===$formKey && $errors->any())<p class="refund-error" role="alert">{{ $errors->first() }}</p>@endif
<div class="order-actions"><button type="button" data-refund-close>취소</button><button type="submit" class="primary">{{ $policy ? '취소/환불안내 수정' : '취소/환불안내 등록' }}</button></div>
