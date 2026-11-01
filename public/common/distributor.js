'use strict';
document.addEventListener('DOMContentLoaded',()=>{
 document.querySelectorAll('[data-open]').forEach(b=>b.addEventListener('click',()=>document.getElementById(b.dataset.open)?.showModal()));
 document.querySelectorAll('[data-close]').forEach(b=>b.addEventListener('click',()=>document.getElementById(b.dataset.close)?.close()));
 document.querySelectorAll('[data-months]').forEach(b=>b.addEventListener('click',()=>{const end=new Date();const start=new Date();start.setMonth(start.getMonth()-Number(b.dataset.months));const date=d=>`${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;document.getElementById('from').value=date(start);document.getElementById('to').value=date(end);}));
 document.getElementById('postcode-search')?.addEventListener('click',()=>{if(!window.daum?.Postcode){document.getElementById('postcode-message').textContent='주소 검색 서비스에 연결하지 못했습니다. 우편번호와 주소를 직접 입력해 주세요.';return;}new daum.Postcode({oncomplete:data=>{document.getElementById('zipcode').value=data.zonecode;document.getElementById('address').value=data.roadAddress||data.jibunAddress;document.getElementById('address_detail').focus();}}).open();});
 document.getElementById('status_code')?.addEventListener('change',event=>{const required=event.target.value!=='ready_to_ship';document.getElementById('courier').required=required;document.getElementById('tracking_no').required=required;});
 document.querySelectorAll('form[method=post]').forEach(form=>form.addEventListener('submit',()=>{const b=form.querySelector('button[type=submit]');if(b){b.disabled=true;b.textContent='처리 중…';}}));
});
document.addEventListener('DOMContentLoaded',()=>{
 const dialog=document.getElementById('order-dialog');const frame=document.getElementById('order-detail-frame');let saved=false;
 if(dialog&&frame){
  document.querySelectorAll('[data-order-dialog]').forEach(link=>link.addEventListener('click',event=>{event.preventDefault();saved=false;const url=new URL(link.href);url.searchParams.set('frame','1');frame.src=url.href;dialog.showModal();}));
  const close=()=>dialog.close();document.getElementById('order-dialog-close').addEventListener('click',close);
  dialog.addEventListener('close',()=>{frame.removeAttribute('src');if(saved)window.location.reload();});
  window.addEventListener('message',event=>{if(event.origin!==window.location.origin||event.source!==frame.contentWindow)return;if(event.data==='distributor-order-saved')saved=true;if(event.data==='distributor-order-close')close();});
 }
 if(document.body.classList.contains('embedded-detail')){
  document.querySelectorAll('[data-dismiss-detail]').forEach(link=>link.addEventListener('click',event=>{event.preventDefault();window.parent.postMessage('distributor-order-close',window.location.origin);}));
  if(document.querySelector('.alert.success'))window.parent.postMessage('distributor-order-saved',window.location.origin);
 }
});
