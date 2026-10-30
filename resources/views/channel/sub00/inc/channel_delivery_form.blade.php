@php
    $formKey = $charge ? 'edit-'.$charge->id : 'create';
    $oldInput = old('form_key') === $formKey ? session()->getOldInput() : [];
    $value = static function ($key, $default='') use ($oldInput) { $v=data_get($oldInput,$key,$default); return is_scalar($v) ? $v : $default; };
    $rules=$charge?->calculation_rules ?? [];
    $ruleValue=static fn($key,$default='')=>$value('calculation_rules.'.$key,$rules[$key] ?? $default);
    $type=$value('shipping_type',$charge?->shipping_type ?? 'free');
    $regionMode=$ruleValue('region_mode','none');
    $tiers=data_get($oldInput,'calculation_rules.tiers',$rules['tiers'] ?? [['min'=>0,'max'=>null,'fee'=>0]]);
    $tiers=is_array($tiers) ? array_slice(array_values(array_filter($tiers,'is_array')),0,50) : [];
    if (!$tiers) $tiers=[['min'=>0,'max'=>null,'fee'=>0]];
    $courier=$value('courier',$charge?->courier ?: '자체배송');
    $couriers=array_unique(array_merge(['자체배송','CJ대한통운','한진택배','롯데택배','우체국택배','로젠택배'],[$courier]));
@endphp
<input type="hidden" name="form_key" value="{{ $formKey }}">
<p class="delivery-help">배송구분: 사용자</p>
<fieldset><legend>상태</legend><div class="order-checks">@foreach([1=>'사용',0=>'중지'] as $key=>$label)<label><input type="radio" name="status" value="{{ $key }}" @checked((string)$value('status',$charge?->status ?? 1)===(string)$key)>{{ $label }}</label>@endforeach</div></fieldset>
<div class="order-fields"><label>배송비 명칭 *<input name="name" required maxlength="100" value="{{ $value('name',$charge?->name ?? '') }}"></label><label>지정택배사<select name="courier">@foreach($couriers as $option)<option value="{{ $option }}" @selected($courier===$option)>{{ $option }}</option>@endforeach</select></label></div>
<fieldset><legend>배송비 유형</legend><div class="order-checks">@foreach($shippingTypes as $key=>$label)<label><input type="radio" name="shipping_type" value="{{ $key }}" @checked($type===$key)>{{ $label }}</label>@endforeach</div></fieldset>
<section class="delivery-condition" data-types="conditional,variable"><label>기본 배송비 (원)<input type="number" name="base_fee" min="0" max="99999999" required value="{{ $value('base_fee',$charge?->base_fee ?? 0) }}"></label><p class="delivery-help" data-types="variable">기본배송비가 0원인 경우 반복 부과 금액만 계산합니다.</p></section>
<section class="delivery-condition" data-types="conditional"><h3>무료배송 조건</h3>
@foreach(['amount'=>['조건 1 · 구매 금액','원',$charge?->free_order_amount], 'quantity'=>['조건 2 · 구매 수량','개',$charge?->free_order_quantity]] as $key=>[$label,$unit,$initial])
<div class="delivery-threshold"><input type="hidden" name="{{ $key }}_condition_enabled" value="0"><label class="delivery-check"><input type="checkbox" name="{{ $key }}_condition_enabled" value="1" data-threshold="{{ $key }}" @checked((int)$value($key.'_condition_enabled',($initial ?? 0)>0 ? 1 : 0)===1)>{{ $label }}</label><label>{{ $unit }} 이상 구매 시 무료<input type="number" min="1" max="{{ $key==='amount' ? '99999999' : '999999' }}" name="free_order_{{ $key }}" data-threshold-input="{{ $key }}" value="{{ $value('free_order_'.$key,$initial ?? '') }}"></label></div>
@endforeach<p class="delivery-help">선택한 조건 중 하나 이상을 충족하면 기본 배송비가 무료입니다. 지역 추가금은 별도로 적용됩니다.</p></section>
<section class="delivery-condition" data-types="fixed"><label>고정 배송비 (원)<input type="number" min="0" max="99999999" name="fixed_fee" required value="{{ $value('fixed_fee',$charge?->fixed_fee ?? $charge?->base_fee ?? 0) }}"></label><p class="delivery-help">구매 금액·수량과 관계없이 고정 금액을 부과합니다.</p></section>
<section class="delivery-condition" data-types="variable,tiered"><fieldset><legend>구매조건선택</legend><div class="order-checks">@foreach(['amount'=>'금액별','quantity'=>'수량별'] as $key=>$label)<label><input type="radio" name="calculation_rules[basis]" value="{{ $key }}" @checked($ruleValue('basis','amount')===$key)>{{ $label }}</label>@endforeach</div></fieldset>
<div data-types="variable"><div class="order-fields"><label><span>반복 부과 기준 (<span data-basis-unit></span>)</span><input type="number" min="1" max="99999999" required name="calculation_rules[repeat_every]" value="{{ $ruleValue('repeat_every',1) }}"></label><label>반복 추가 배송비 (원)<input type="number" min="0" max="99999999" required name="calculation_rules[repeat_fee]" value="{{ $ruleValue('repeat_fee',0) }}"></label></div>
@foreach(['start_at_one'=>'반복 부과 기준을 1부터 시작', 'repeat_region'=>'지역 배송비도 동일 조건으로 반복 부과'] as $key=>$label)<input type="hidden" name="calculation_rules[{{ $key }}]" value="0"><label class="delivery-check"><input type="checkbox" name="calculation_rules[{{ $key }}]" value="1" @checked((int)$ruleValue($key,0)===1)>{{ $label }}</label>@endforeach
<p class="delivery-help">기본 배송비에 반복 금액이 합산됩니다. ‘1부터 시작’을 선택하면 첫 구매 단위부터 반복 금액이 부과됩니다. 지역 배송비는 기본 1회 적용됩니다.</p></div>
<div data-types="tiered"><h3>구간별 배송비</h3><p class="delivery-help">각 구간은 하한 이상·상한 미만입니다. 다음 하한은 자동으로 연결되며, 마지막 구간에는 상한이 없습니다.</p><div data-tiers>
@foreach($tiers as $index=>$tier)<div class="delivery-tier" data-tier>
<label><span>구간 하한 (<span data-basis-unit></span>)</span><input type="number" min="0" readonly name="calculation_rules[tiers][{{ $index }}][min]" value="{{ is_scalar($tier['min'] ?? null) ? $tier['min'] : 0 }}"></label>
<label><span>구간 상한 (<span data-basis-unit></span>)</span><input type="number" min="1" max="99999999" name="calculation_rules[tiers][{{ $index }}][max]" value="{{ is_scalar($tier['max'] ?? null) ? $tier['max'] : '' }}"></label>
<label>구간 배송비 (원)<input type="number" min="0" max="99999999" required name="calculation_rules[tiers][{{ $index }}][fee]" value="{{ is_scalar($tier['fee'] ?? null) ? $tier['fee'] : '' }}"></label><button type="button" data-remove-tier>구간 삭제</button></div>@endforeach
</div><button type="button" data-add-tier>구간 추가</button><p class="delivery-help">해당 구간 한 개의 배송비만 적용합니다. 0원을 입력하면 해당 구간은 무료배송입니다.</p></div></section>
<fieldset><legend>배송비 결제</legend><div class="order-checks">@foreach($paymentTypes as $key=>$label)<label><input type="radio" name="payment_type" value="{{ $key }}" @checked($value('payment_type',$charge?->payment_type ?? 'prepaid')===$key)>{{ $label }}</label>@endforeach</div></fieldset>
<section class="delivery-condition"><h3>지역별 배송</h3>@foreach(['jeju'=>'제주 지역','remote'=>'도서산간 지역'] as $key=>$label)<fieldset><legend>{{ $label }}</legend><div class="order-checks">@foreach([0=>'배송함',1=>'배송안함'] as $block=>$caption)<label><input type="radio" name="calculation_rules[block_{{ $key }}]" value="{{ $block }}" @checked((int)$ruleValue('block_'.$key,0)===$block)>{{ $caption }}</label>@endforeach</div></fieldset>@endforeach</section>
<fieldset><legend>지역별 배송비</legend><div class="order-checks"><label><input type="radio" name="region_enabled" value="0" @checked($value('region_enabled',$regionMode==='none'?0:1)==0)>사용안함</label><label><input type="radio" name="region_enabled" value="1" @checked($value('region_enabled',$regionMode==='none'?0:1)==1)>배송권역 방식</label></div></fieldset>
<section class="delivery-condition" data-region-settings><label>배송권역<select name="calculation_rules[region_mode]"><option value="two" @selected($regionMode!=='three')>2권역 · 제주/도서산간 통합</option><option value="three" @selected($regionMode==='three')>3권역 · 제주/도서산간 분리</option></select></label><div class="order-fields">
<label><span data-jeju-label>제주/도서산간 추가 배송비 (원)</span><input type="number" min="0" max="99999999" name="calculation_rules[jeju_fee]" value="{{ $ruleValue('jeju_fee',0) }}"></label>
<label data-three-region>도서산간 추가 배송비 (원)<input type="number" min="0" max="99999999" name="calculation_rules[remote_fee]" value="{{ $ruleValue('remote_fee',0) }}"></label>
<label>그 외 지역 추가 배송비 (원)<input type="number" min="0" max="99999999" name="calculation_rules[mainland_fee]" value="{{ $ruleValue('mainland_fee',0) }}"></label></div><p class="delivery-help">그 외 지역 추가금은 제주도 등에서 출고하는 상품의 추가 배송비를 설정할 때 사용할 수 있습니다.</p></section>
<details class="delivery-postcodes"><summary>배송 지역 우편번호 기준</summary><p class="delivery-help">지역 추가금 또는 배송 제한을 사용할 때 택배사 기준 우편번호가 필요합니다. 줄마다 5자리 번호 또는 시작-끝 범위를 입력하세요.</p><div class="order-fields">@foreach(['jeju'=>'제주','remote'=>'도서산간'] as $key=>$label)<label>{{ $label }} 우편번호<textarea name="{{ $key }}_postcodes_text" rows="3" maxlength="100000">{{ $value($key.'_postcodes_text',implode("\n",$rules[$key.'_postcodes'] ?? [])) }}</textarea></label>@endforeach</div></details>
<label class="delivery-memo">메모<textarea name="memo" rows="3" maxlength="1000">{{ $value('memo',$charge?->memo ?? '') }}</textarea></label>
<details class="delivery-preview"><summary>배송비 계산 확인</summary><p class="delivery-help">입력한 조건으로 배송비를 확인합니다. 설정 저장이나 주문 생성은 하지 않습니다.</p><div class="order-fields"><label>구매 금액 (원)<input type="number" name="preview_amount" min="0" max="999999999" value="10000"></label><label>구매 수량 (개)<input type="number" name="preview_quantity" min="1" max="999999" value="1"></label><label>배송지 우편번호<input name="preview_postcode" inputmode="numeric" maxlength="5" value="04524"></label><label>배송지 주소<input name="preview_address" maxlength="255" value="서울 중구 세종대로 110"></label></div><button type="button" data-preview-delivery>계산하기</button><p data-preview-result aria-live="polite"></p></details>
<p class="delivery-error" data-delivery-error role="alert">@if(old('form_key')===$formKey && $errors->any()){{ $errors->first() }}@endif</p>
<div class="order-actions"><button type="button" data-delivery-close>취소</button><button type="submit" class="primary">{{ $charge ? '배송비 수정' : '배송비 등록' }}</button></div>
