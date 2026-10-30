(() => {
    'use strict';
    const orders = window.Me9OrderData || [];
    const definitions = window.Me9ClaimDefinitions || {};
    const detail = document.getElementById('order-detail');
    const actionDialog = document.getElementById('order-action');
    const form = document.getElementById('order-action-form');
    const body = document.getElementById('order-action-body');
    const error = document.getElementById('order-action-error');
    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    const date = value => { if(!value)return '-'; if(!String(value).includes('T'))return value; return new Intl.DateTimeFormat('sv-SE',{timeZone:'Asia/Seoul',year:'numeric',month:'2-digit',day:'2-digit',hour:'2-digit',minute:'2-digit',second:'2-digit',hour12:false}).format(new Date(value)); };
    const money = value => Number(value || 0).toLocaleString('ko-KR');
    const claimNo = claim => claim ? 'Me9-' + ({cancel:'Cancel',return:'Return',exchange:'Exchange'}[claim.type]) + '-' + String(claim.id).padStart(8, '0') : '-';
    const tabs = {info:'주문 정보',normal:'정상 주문',cancel:'취소 주문',return:'반품 주문',exchange:'교환 주문'};
    let order, tab = 'info', action, targetItems = [], appliedFee = 0;
    const sum = (items, key) => items.reduce((n, item) => n + Number(item[key] || 0), 0);
    const facts = values => '<dl class="order-facts">' + values.map(([label,value]) => '<div><dt>' + esc(label) + '</dt><dd>' + esc(label.includes('일시') ? date(value) : (value || '-')) + '</dd></div>').join('') + '</dl>';
    const button = (key,label) => '<button type="button" data-order-action="' + key + '">' + esc(label) + '</button>';
    function visibleItems() {
        return order.items.filter(item => tab === 'info' || (tab === 'normal' ? ['awaiting_payment','paid','ready_to_ship','shipping','delivered','confirmed'].includes(item.status) : (item.status.startsWith(tab) || (order.claims_data || []).some(c=>c.type===tab && c.order_product_id===item.id))));
    }
    function table(items, selectable = false) {
        const heads = ['주문상태','상품명','상품코드','옵션','주문수량','취소수량','반품수량','교환수량','판매형태','PG 결제','포인트결제','상품가','배송비','할인비용','상품+수수료','매입 배송비','제공포인트','택배사','송장번호'];
        return '<div class="order-table-scroll" tabindex="0" aria-label="주문상품 가로 스크롤"><table class="order-table"><thead><tr>' + (selectable ? '<th>선택</th>' : '') + heads.map(h=>'<th>'+h+'</th>').join('') + '</tr></thead><tbody>' + (items.length ? items.map(item => '<tr>' + (selectable ? '<td><input type="checkbox" aria-label="상품 '+item.id+' 선택" name="item_ids[]" value="'+item.id+'" '+(item.can_manage ? 'checked' : 'disabled')+'></td>' : '') +
            '<td>'+esc(item.status_label)+'</td><td class="order-product-name">'+esc(item.product_name)+'</td><td>'+esc(item.product_code)+'</td><td>'+esc(item.option_name)+'</td><td>'+item.qty+'</td>' + ['cancel','return','exchange'].map(t=>'<td>'+(item.status.startsWith(t)?item.qty:0)+'</td>').join('') + '<td>'+esc(item.channel_financial.form)+'</td>' + ['paid_cash_amount','used_point_amount','paid_line_total','shipping_amount','discount_amount'].map(k=>'<td>'+money(item[k])+(k==='used_point_amount'?' p':' 원')+'</td>').join('') + '<td>'+money(item.channel_financial.purchase)+' 원</td><td>'+money(item.channel_financial.purchase_shipping)+' 원</td><td>'+money(item.reward_points)+' p</td><td>'+esc(item.courier_name || '-')+'</td><td>'+esc(item.tracking_number || '-')+'</td></tr>').join('') : '<tr><td colspan="20" class="order-empty">해당 상태의 주문 상품이 없습니다.</td></tr>') + '</tbody></table></div>';
    }
    function renderDetail() {
        document.getElementById('order-detail-tabs').innerHTML = Object.entries(tabs).map(([key,label])=>'<button type="button" data-detail-tab="'+key+'" aria-selected="'+(tab===key)+'">'+label+'</button>').join('');
        const items = visibleItems();
        let html = facts([['주문번호',order.order_no],['주문일시',order.created_at],['Shop채널',order.shop_name],['결제일시',order.payment_date || (order.events || []).find(e=>e.status_label==='결제완료')?.created_at || '-']]);
        if (tab === 'info') {
            html += '<h3>주문자 정보</h3>'+facts([['회원',order.user_id ? '회원 #'+order.user_id : '비회원'],['주문자',order.buyer_name || order.name],['연락처',order.buyer_mobile || order.mobile],['이메일',order.email]])+'<h3>배송 정보</h3>'+facts([['수령인',order.recipient_name || order.name],['연락처',order.recipient_mobile || order.mobile],['배송지',(order.recipient_zipcode || order.pincode || '')+' '+(order.recipient_address || order.address || '')+' '+(order.recipient_address_detail || '')],['배송메모',order.delivery_memo]])+'<h3>결제 정보</h3>'+facts([['결제 수단',order.payment_method],[items.every(i=>i.status==='awaiting_payment')?'결제예정금액':'PG 결제금액',money(sum(items,'paid_cash_amount'))+' 원'],['포인트결제',money(sum(items,'used_point_amount'))+' p'],['제공포인트',money(sum(items,'reward_points'))+' p']])+button('payment_log','PG 연동 로그');
        }
        if (['cancel','return','exchange'].includes(tab)) {
            const claims = (order.claims_data || []).filter(c=>c.type===tab && items.some(i=>i.id===c.order_product_id)).sort((a,b)=>b.id-a.id);
            html += '<h3>신청 정보</h3>'+ (claims.length ? claims.map(c=>facts([['신청번호',claimNo(c)],['신청일시',c.created_at],['사유',c.reason],['상세 사유',c.detail_reason],['회수 방법',c.pickup_method==='manual'?'고객 직접 회수':c.pickup_method==='automatic'?'업체 회수':'-'],['회수 택배사 / 송장',[c.customer_courier_name,c.customer_tracking_number].filter(Boolean).join(' / ')],['회수지',c.return_address],['회수 담당자',[c.pickup_name,c.pickup_mobile].filter(Boolean).join(' / ')],['처리 내역',c.admin_comment],...(tab==='exchange'?[['원래 옵션',c.original_option],['재배송 수령인',c.exchange_recipient_name],['재배송 연락처',c.exchange_recipient_mobile],['재배송지',c.exchange_recipient_address]]:[])])).join('') : '<p class="order-notice">신청 내역이 없습니다.</p>');
            html += '<h3>'+(tab==='exchange'?'교환비용 및 재발송':'환불 정보')+'</h3>'+facts(tab==='exchange' ? [['추가 배송비',money(sum(items,'exchange_shipping_fee'))+' 원'],['재발송 상품',items.filter(i=>i.replacement).map(i=>'상품 #'+i.replacement.id+' / '+i.replacement.status_label+' / '+(i.replacement.courier_name || '')+' '+(i.replacement.tracking_number || '')).join('\n') || (claims.some(c=>c.status==='converted_to_return')?'반품으로 전환된 주문입니다.':claims.every(c=>c.status==='withdrawn')?'교환이 철회된 주문입니다.':'교환 확정 시 배송대기 상품이 생성됩니다.')]] : [['상품 결제금액',money(sum(items,'paid_cash_amount'))+' 원'],['배송비 차감',money(sum(items,'return_shipping_fee'))+' 원'],['환불 예상 / 반영 금액',money(claims.every(c=>['withdrawn','rejected'].includes(c.status))?0:sum(items,'refund_cash_amount'))+' 원'],['환불상태',items.map(i=>i.refund_status_label).join(', ')]]);
        }
        html += '<h3>주문상품 목록</h3>'+table(items,tab!=='info');
        if(tab==='normal') html += items.filter(item=>item.is_exchange_replacement).map(item=>'<h3>재발송 배송지 · 상품 #'+item.id+'</h3>'+facts([['수령인',item.recipient_name],['연락처',item.recipient_mobile],['주소',item.recipient_address]])).join('');
        if(tab==='normal') html += '<div class="order-actions">'+button('paid','결제완료')+button('ready','배송대기')+button('shipping','배송관리')+button('cancel_request','취소 요청')+button('return_request','반품 요청')+button('exchange_request','교환 요청')+button('delivered','배송완료')+button('confirmed','구매확정')+'</div>';
        if(['cancel','return','exchange'].includes(tab)) html += '<div class="order-actions">'+Object.entries(definitions).filter(([key])=>key.startsWith(tab+'_')).map(([key,d])=>button(key,d.label)).join('')+'</div>';
        if(tab==='info') html += '<h3>주문 상태 변경 정보</h3><div class="order-history">'+esc((order.events || []).map(e=>date(e.created_at)+' '+e.status_label+' / 상품 #'+e.order_product_id+' / '+e.actor).concat((order.claims_data || []).map(c=>claimNo(c)+' · '+date(c.created_at)+' '+c.reason+'\n'+(c.admin_comment || ''))).join('\n') || '등록된 처리 이력이 없습니다.')+'</div>';
        document.getElementById('order-detail-body').innerHTML=html;
    }
    function openOrder(id, nextTab = 'info') { order=orders.find(o=>o.id===Number(id)); if(!order)return; tab=nextTab; renderDetail(); if(!detail.open)detail.showModal(); }
    function selectedItems() {
        const ids=Array.from(detail.querySelectorAll('input[name="item_ids[]"]:checked')).map(i=>Number(i.value));
        return visibleItems().filter(item=>ids.includes(item.id) && item.can_manage);
    }
    function field(name,label,type='text',value='',required=true) {
        const input='<input type="'+type+'" name="'+name+'" value="'+esc(value)+'" '+(required?'required':'')+' maxlength="'+(name.includes('address')?'1000':name.includes('mobile')?'30':'100')+'">';
        return '<label>'+label+(name.includes('address')?'<div class="order-inline">'+input+'<button type="button" data-order-postcode="'+name+'">우편번호 찾기</button></div>':input)+'</label>';
    }
    function courierFields() { return '<div class="order-fields"><label>택배사<select name="courier_name" required><option value="">택배사 선택</option>'+['CJ대한통운','한진택배','롯데택배','우체국택배','로젠택배','직접배송'].map(c=>'<option>'+c+'</option>').join('')+'</select></label>'+field('tracking_number','송장번호')+'</div>'; }
    function showAction(key) {
        action=key;appliedFee=0;error.textContent='';targetItems=selectedItems();
        let title='',html='';
        if(key==='payment_log') { title='PG 연동 로그';html='<p class="order-notice">결제수단: '+esc(order.payment_method)+' / PG: '+esc(order.payment_gateway)+'</p>'+((order.payment_logs || []).length ? order.payment_logs.map(log=>facts([['거래번호',log.payment_id],['결제금액',money(log.amount)+' '+log.currency],['결과',log.payment_status],['연동일시',log.created_at]])).join('') : '<p>이 주문에 저장된 PG 연동 로그가 없습니다.</p>'); }
        else {
            const config={paid:['결제완료',['awaiting_payment']],ready:['배송대기',['paid']],shipping:['배송관리',['ready_to_ship','shipping']],delivered:['배송완료',['shipping']],confirmed:['구매확정',['delivered']],cancel_request:['취소 요청',['awaiting_payment','paid','ready_to_ship']],return_request:['반품 요청',['shipping','delivered']],exchange_request:['교환 요청',['shipping','delivered']]};
            const d=config[key];title=d?d[0]:definitions[key]?.label;
            const allowed=d?d[1]:definitions[key]?.from || [];
            html='<p class="order-notice">'+esc(order.order_no)+' · 선택 상품 '+targetItems.length+'건\n'+esc(title)+' 처리를 진행하시겠습니까?</p>';
            if(!targetItems.length || targetItems.some(item=>!allowed.includes(item.status))) { html='<p class="order-notice">현재 상태에서는 '+esc(title)+' 처리를 할 수 없습니다. 처리 가능한 상품을 선택해 주세요.</p>';targetItems=[]; }
            else {
                html+=table(targetItems);
                if(key==='shipping' || key.endsWith('_invoice'))html+='<h3>택배 정보</h3>'+courierFields();
                if(key.endsWith('_request')) {
                    html+='<h3>신청 정보</h3><div class="order-fields"><label>신청 사유<select name="reason" required><option value="">사유 선택</option><option>마음이 변했어요</option><option>상품에 하자가 있어요</option><option>다른 상품이 배송됐어요</option></select></label><label>상세 사유<textarea name="detail_reason" maxlength="3000" required placeholder="상세 사유를 입력해 주세요 (필수)"></textarea></label></div>';
                    if(key==='exchange_request')html+='<h3>교환 옵션·배송지</h3><div class="order-fields">'+targetItems.map(item=>'<label>'+esc(item.product_name)+' 변경 옵션<select name="exchange_options['+item.id+']"><option value="">동일 옵션</option>'+(item.exchange_options || []).map(o=>'<option>'+esc(o)+'</option>').join('')+'</select></label>').join('')+field('exchange_recipient_name','재배송 수령인','text',order.name)+field('exchange_recipient_mobile','재배송 연락처','text',order.mobile)+field('exchange_recipient_address','재배송 주소','text',order.address)+'</div>';
                    if(key!=='cancel_request') {
                        html+='<h3>회수 정보</h3><label>회수 방법<select name="pickup_method" required><option value="">회수 방법 선택</option><option value="manual">고객 직접 회수</option><option value="automatic">업체 회수</option></select></label><div id="order-manual-pickup" class="order-fields" hidden>'+field('customer_courier_name','고객 발송 택배사')+field('customer_tracking_number','고객 발송 송장번호','text','',false)+'</div><div id="order-auto-pickup" class="order-fields" hidden>'+field('pickup_name','회수 주문자','text',order.recipient_name || order.name)+field('pickup_mobile','회수 연락처','text',order.recipient_mobile || order.mobile)+field('return_address','회수지 주소','text',(order.recipient_zipcode || order.pincode || '')+' '+(order.recipient_address || order.address || '')+' '+(order.recipient_address_detail || ''))+'</div>';
                        html+='<div class="order-fee-row"><label>'+(key==='return_request'?'반품 배송비':'추가 배송비')+' (원)<input name="claim_shipping_fee" type="number" min="0" max="'+sum(targetItems,'paid_cash_amount')+'" step="1" value="0" required></label><button type="button" data-apply-fee>적용</button></div><div id="order-fee-summary" class="order-fee-summary"></div>';
                    } else html+='<p class="order-notice">환불 예상 금액 '+money(sum(targetItems,'paid_cash_amount'))+' 원 · 취소 요청 접수 후 승인이 필요합니다.</p>';
                }
                if(['return_hold','exchange_hold_before','exchange_hold_after'].includes(key))html+='<h3>보류 사유</h3><label>사유<textarea name="reason" maxlength="1000" required></textarea></label>';
                if(key==='exchange_option') {
                    let choices=targetItems[0].exchange_options || [];targetItems.slice(1).forEach(i=>choices=choices.filter(c=>(i.exchange_options || []).includes(c)));
                    html+='<h3>교환 옵션</h3><label>변경 옵션<select name="option" required><option value="">옵션 선택</option>'+choices.map(c=>'<option>'+esc(c)+'</option>').join('')+'</select></label><p class="order-notice">차액이 없는 판매 중 옵션만 선택할 수 있습니다.</p>';
                }
            }
        }
        document.getElementById('order-action-title').textContent=title;
        body.innerHTML=html;
        const submit=document.getElementById('order-action-submit');submit.hidden=key==='payment_log' || !targetItems.length;submit.disabled=false;
        body.querySelectorAll('[hidden] input').forEach(i=>i.disabled=true);
        if(body.querySelector('#order-fee-summary'))renderFee();
        actionDialog.showModal();
    }
    function renderFee() { const total=sum(targetItems,'paid_cash_amount');document.getElementById('order-fee-summary').textContent=action==='return_request' ? '반품상품 결제금액 '+money(total)+' 원 / 차감 내역 '+money(appliedFee)+' 원 / 환불 예상 금액 '+money(total-appliedFee)+' 원' : '추가 배송비 '+money(appliedFee)+' 원'; }
    function applyFee() { const input=form.elements.claim_shipping_fee;if(!input.reportValidity())return false;appliedFee=Number(input.value);renderFee();return true; }
    document.addEventListener('click', e=>{
        const postcode=e.target.closest('[data-order-postcode]');
        if(postcode){
            if(!window.daum?.Postcode){error.textContent='주소 검색을 불러오지 못했습니다. 주소를 직접 입력해 주세요.';return;}
            const target=form.elements[postcode.dataset.orderPostcode],dialog=document.getElementById('order-postcode'),widget=document.getElementById('order-postcode-widget');
            widget.replaceChildren();dialog.showModal();
            new window.daum.Postcode({oncomplete:data=>{target.value='('+data.zonecode+') '+(data.userSelectedType==='R'?data.roadAddress:data.jibunAddress);dialog.close();target.focus();},width:'100%',height:'100%'}).embed(widget);
            return;
        }
        const close=e.target.closest('[data-close-dialog]');if(close){document.getElementById(close.dataset.closeDialog).close();return;}
        const open=e.target.closest('[data-order-open]');if(open){openOrder(open.dataset.orderOpen,open.dataset.orderTab || 'info');return;}
        const next=e.target.closest('[data-detail-tab]');if(next){tab=next.dataset.detailTab;renderDetail();return;}
        const act=e.target.closest('[data-order-action]');if(act){showAction(act.dataset.orderAction);return;}
        if(e.target.closest('[data-apply-fee]'))applyFee();
        const product=e.target.closest('[data-product-open]');if(product)showProduct(product.dataset.productOpen);
    });
    async function showProduct(value) {
        const [orderId,itemId]=value.split(':');openOrder(orderId);
        const item=order.items.find(i=>i.id===Number(itemId));
        const snapshot=facts([['주문 상품명',item.product_name],['상품코드',item.product_code],['주문 옵션',item.option_name],['상품 유형',item.product_type],['주문 단가',money(item.price)+' 원']]);
        document.getElementById('order-action-title').textContent='주문 상품 상세';body.innerHTML=snapshot+'<p role="status">상품 상세를 불러오는 중입니다.</p>';document.getElementById('order-action-submit').hidden=true;error.textContent='';actionDialog.showModal();
        try {
            const result=await fetch('/channel/product/base/detail/'+item.product_id,{headers:{Accept:'application/json'}}).then(r=>r.json());
            if(!result.status)throw Error(result.message || '상품 상세를 불러오지 못했습니다.');
            const product=result.product;
            body.innerHTML=snapshot+'<h3>상품 정보</h3>'+facts([['현재 상품명',product.product_name],['판매자',product.seller_name],['분류',result.category_path],['판매 조건',product.price_condition_label],['재고',product.stock_label]])+'<div class="order-product-images">'+(product.image_urls || []).map(src=>'<img src="'+esc(src)+'" alt="상품 이미지">').join('')+'</div><h3>상품 상세 설명</h3><div class="order-product-description">'+product.detail_html+'</div>';
        } catch(failure){body.innerHTML=snapshot+'<p class="order-notice">'+esc(failure.message)+'</p>';}
    }
    form.addEventListener('change',e=>{
        if(e.target.name==='pickup_method')for(const [key,value] of [['order-manual-pickup','manual'],['order-auto-pickup','automatic']]){const region=document.getElementById(key);region.hidden=e.target.value!==value;region.querySelectorAll('input').forEach(i=>i.disabled=region.hidden);}
        if(e.target.name==='claim_shipping_fee') {appliedFee=0;renderFee();}
    });
    form.addEventListener('submit',async e=>{
        e.preventDefault();if(!targetItems.length || !form.reportValidity())return;
        if(form.elements.claim_shipping_fee && Number(form.elements.claim_shipping_fee.value)!==appliedFee){error.textContent='배송비를 입력한 뒤 적용 버튼을 눌러 주세요.';return;}
        const submit=document.getElementById('order-action-submit');submit.disabled=true;error.textContent='처리 중입니다…';
        const values=Object.fromEntries(new FormData(form).entries());values.exchange_options={};body.querySelectorAll('select[name^="exchange_options["]').forEach(input=>{values.exchange_options[input.name.match(/\[(\d+)\]/)[1]]=input.value;delete values[input.name];});values.order_id=order.id;values.item_ids=targetItems.map(i=>i.id);
        let endpoint='/channel/order/claim/action',nextTab=tab;
        if(action.endsWith('_request')){nextTab=action.replace('_request','');endpoint='/channel/order/'+nextTab+'/request';}
        else if(['paid','ready','shipping','delivered','confirmed'].includes(action)){endpoint='/channel/order/status/update';values.status=action==='ready'?'ready_to_ship':action;nextTab='normal';}
        else values.action=action;
        try {
            const response=await fetch(endpoint,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify(values)});
            const result=await response.json();if(!response.ok || !result.status)throw Error(result.message || '처리하지 못했습니다.');
            if(['cancel_reject','return_withdraw','exchange_withdraw'].includes(action))nextTab='normal';
            if(action==='exchange_to_return')nextTab='return';
            const path={info:'list',normal:'list',cancel:'cancel/list',return:'return/list',exchange:'exchange/list'}[nextTab];
            const url=new URL('/channel/order/'+path,location.origin);url.searchParams.set('order_id',order.id);url.searchParams.set('review_order',order.id);url.searchParams.set('review_tab',nextTab);url.searchParams.set('order_type','all');url.searchParams.set('result','saved');location.assign(url.href);
        } catch(failure) {error.textContent=failure.message;submit.disabled=false;}
    });
    document.getElementById('order-detail-toggle').addEventListener('click',e=>{const region=document.getElementById('order-search-details');region.hidden=!region.hidden;e.currentTarget.setAttribute('aria-expanded',String(!region.hidden));e.currentTarget.textContent='상세 '+(region.hidden?'⌄':'⌃');document.querySelector('[name="detail_open"]').value=region.hidden?'0':'1';});
    document.getElementById('order-per-page').addEventListener('change',e=>{const url=new URL(location.href);url.searchParams.set('per_page',e.target.value);url.searchParams.delete('page');location.assign(url.href);});
    const query=new URLSearchParams(location.search);if(query.has('review_order')){openOrder(query.get('review_order'),query.get('review_tab') || 'info');if(query.get('result')==='saved')document.getElementById('order-detail-body').insertAdjacentHTML('afterbegin','<p role="status" class="order-notice">저장된 주문 정보를 다시 불러왔습니다.</p>');}
})();
