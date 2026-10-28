<div id="pop_{{ $type }}" class="popup_bx">
    <div class="pop_w"><div class="pop_inner"><div class="pop_con" style="max-width:600px;">
        <a href="javascript:void(0);" onclick="close{{ ucfirst($type) }}Popup()" class="close1">닫기</a>
        <div class="ttl01 brb"><strong>{{ $label }}신청하기</strong></div>
        <div class="conbx">
            <div class="product01" style="display:flex; align-items:center; margin-bottom:20px;">
                <div id="{{ $type }}_prd_img" class="img_bx" style="width:80px; height:80px; flex-shrink:0; background:#f5f5f5 center/cover; border:1px solid #eee; border-radius:5px; margin-right:15px;"></div>
                <div class="txt_bx"><div class="txt_w">
                    <div id="{{ $type }}_prd_shop" style="font-size:13px; color:#777;"></div>
                    <strong id="{{ $type }}_prd_name" style="font-size:16px; color:#333; display:block; margin:5px 0;"></strong>
                    <div id="{{ $type }}_prd_option" style="font-size:13px; color:#666;"></div>
                </div></div>
            </div>
            <form id="{{ $type }}_form">
                <input type="hidden" name="order_item_id" id="{{ $type }}_order_item_id">
                <div class="con">
                    <label for="{{ $type }}_reason_select" style="display:block; font-size:15px; font-weight:700; margin-bottom:10px;">{{ $label }}사유</label>
                    <select name="reason" id="{{ $type }}_reason_select" required style="width:100%; height:40px; padding:0 10px; border:1px solid #ddd; border-radius:5px;">
                        <option value="">{{ $label }}사유 선택</option>
                        @foreach(config('array.order_'.$type.'_reasons') as $key => $reason)
                            <option value="{{ $key }}">{{ $reason }}</option>
                        @endforeach
                    </select>
                    <div id="{{ $type }}_detail_reason_bx" style="display:none; margin-top:10px;">
                        <textarea name="detail_reason" id="{{ $type }}_detail_reason" aria-label="기타 {{ $label }} 사유" maxlength="2000" placeholder="기타 내용을 입력해 주세요" style="width:100%; height:100px; padding:10px; border:1px solid #ddd; border-radius:5px; resize:vertical;"></textarea>
                    </div>
                </div>
                @if($type !== 'cancel')
                    <fieldset style="border:0; padding:0; margin:20px 0 0;">
                        <legend style="font-size:15px; font-weight:700; margin-bottom:10px;">상품회수방법</legend>
                        <ul class="chk01 mdipi" style="display:flex; gap:20px; margin-bottom:15px;">
                            <li><input type="radio" id="{{ $type }}_method_auto" name="{{ $type }}_method" value="auto" checked><label for="{{ $type }}_method_auto">자동회수</label></li>
                            <li><input type="radio" id="{{ $type }}_method_manual" name="{{ $type }}_method" value="manual"><label for="{{ $type }}_method_manual">수동회수</label></li>
                        </ul>
                        <div id="{{ $type }}_method_text_auto" class="imp_txt" style="background:#f9f9f9; padding:15px; border-radius:5px; font-size:13px; line-height:1.5; color:#666;">
                            <p>※ {{ $label }}이라고 표기해서 문 앞에 두시면 “한진택배”에서 회수합니다.<br>물품에 문제가 없을 경우 택배비가 발생합니다.</p>
                        </div>
                        <div id="{{ $type }}_method_text_manual" class="addr_bx" style="display:none; border:1px solid #ddd; padding:15px; border-radius:5px;">
                            <strong style="display:block; margin-bottom:5px;">상품회수주소</strong>
                            <span id="{{ $type }}_pickup_address">반송지 정보를 불러오는 중입니다.</span>
                            <input type="text" name="customer_courier_name" aria-label="{{ $label }} 택배사" maxlength="100" placeholder="택배사 (나중에 등록 가능)" style="width:100%; margin-top:10px; height:36px; border:1px solid #ddd; padding:0 8px; box-sizing:border-box;">
                            <input type="text" name="customer_tracking_number" aria-label="{{ $label }} 송장번호" maxlength="100" placeholder="송장번호 (나중에 등록 가능)" style="width:100%; margin-top:6px; height:36px; border:1px solid #ddd; padding:0 8px; box-sizing:border-box;">
                        </div>
                    </fieldset>
                @endif
                <div class="btm_btn" style="margin-top:20px; display:flex; justify-content:center; gap:8px;">
                    <button type="submit" style="min-width:120px; height:44px; padding:0 16px; border:1px solid #6954a6; border-radius:5px; background:#6954a6; color:#fff; cursor:pointer;">{{ $label }}신청</button>
                    <button type="button" onclick="close{{ ucfirst($type) }}Popup()" style="min-width:120px; height:44px; padding:0 16px; border:1px solid #ccc; border-radius:5px; background:#fff; color:#444; cursor:pointer;">창닫기</button>
                </div>
            </form>
        </div>
    </div></div></div>
</div>
