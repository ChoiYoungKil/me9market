(() => {
 const root=document.getElementById('shop-sales-requests');if(!root)return;
 const state=JSON.parse(root.querySelector('#sales-request-state').textContent),dialog=root.querySelector('dialog');let current;
 root.querySelectorAll('[data-review-request]').forEach(b=>b.addEventListener('click',()=>{current=state.rows.find(r=>r.id===Number(b.dataset.reviewRequest));const box=root.querySelector('[data-request-information]');box.replaceChildren();Object.entries({'상품명':current.name,'요청 채널':current.channel,'신청사유':current.reason,'요청 판매가':Number(current.price).toLocaleString()+'원','현재 상태':{pending:'대기',approved:'허용',rejected:'거부'}[current.status]}).forEach(([k,v])=>{const row=document.createElement('div'),dt=document.createElement('dt'),dd=document.createElement('dd');dt.textContent=k;dd.textContent=v;row.append(dt,dd);box.append(row);});dialog.showModal();}));
 root.querySelector('[data-close-request]').addEventListener('click',()=>dialog.close());
 root.querySelectorAll('[data-review-status]').forEach(b=>b.addEventListener('click',async()=>{b.disabled=true;try{const response=await fetch(state.url,{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({request_id:current.id,status:b.dataset.reviewStatus})});const result=await response.json();if(result.status)location.reload();else throw Error(result.message);}catch(e){const box=root.querySelector('[data-request-error]');box.textContent=e.message||'처리하지 못했습니다.';box.hidden=false;b.disabled=false;}}));
 root.querySelector('[data-select-all-requests]')?.addEventListener('change',e=>root.querySelectorAll('[data-request-check]').forEach(c=>c.checked=e.target.checked));
 root.querySelectorAll('[data-batch-review]').forEach(b=>b.addEventListener('click',async()=>{
  const ids=Array.from(root.querySelectorAll('[data-request-check]:checked')).map(c=>Number(c.dataset.requestCheck)),box=root.querySelector('[data-batch-error]');
  if(!ids.length){box.textContent='처리할 판매요청을 선택해 주세요.';box.hidden=false;return;}
  b.disabled=true;
  try{const r=await(await fetch(state.url,{method:'POST',headers:{'Content-Type':'application/json',Accept:'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content},body:JSON.stringify({request_ids:ids,status:b.dataset.batchReview})})).json();if(r.status)location.reload();else throw Error(r.message);}catch(e){box.textContent=e.message||'처리하지 못했습니다.';box.hidden=false;b.disabled=false;}
 }));
})();
