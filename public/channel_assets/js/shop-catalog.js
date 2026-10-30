(() => {
 const root=document.getElementById('shop-products');if(!root)return;
 const s=JSON.parse(root.querySelector('#catalog-state').textContent),catalog=root.querySelector('#catalog-dialog'),productDialog=root.querySelector('#catalog-product-dialog'),detailDialog=root.querySelector('#catalog-detail-dialog');
 const form=root.querySelector('#catalog-add-form'),error=root.querySelector('[data-product-error]'),preview=root.querySelector('[data-price-preview]');
 const csrf=document.querySelector('meta[name=csrf-token]').content;
 const showError=(box,message)=>{box.textContent=message;box.hidden=!message;};
 const el=(tag,text)=>{const e=document.createElement(tag);if(text!==undefined)e.textContent=text;return e;};
 const amount=v=>Number(v||0).toLocaleString('ko-KR');
 const request=async(url,data)=>{const response=await fetch(url,{method:'POST',headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':csrf,'X-Requested-With':'XMLHttpRequest'},body:JSON.stringify(data)});const result=await response.json();if(!response.ok)throw Error(Object.values(result.errors||{}).flat().join(' ')||result.message||'요청을 처리하지 못했습니다.');return result;};
 const listUrl=()=>'/channel/shop/product01?shop_id='+s.shopId;
 root.querySelector('[data-open-catalog]').addEventListener('click',()=>catalog.showModal());
 if(s.open)catalog.showModal();
 root.querySelectorAll('[data-close-dialog]').forEach(b=>b.addEventListener('click',()=>b.closest('dialog').close()));
 let current,mode,sequence=0,quoteTimer;
 const submit=form.querySelector('[type=submit]');
 async function quote(initial=false){
  if(!current)return;const serial=++sequence;submit.disabled=true;showError(error,'');
  const params=new URLSearchParams({shop_id:s.shopId,product_id:current.id,selling_price:form.elements.selling_price.value||0,reward_points:form.elements.reward_points.value||0});
  try{const result=await (await fetch(s.quote+'?'+params,{headers:{Accept:'application/json'}})).json();if(serial!==sequence)return;
   if(!result.status){if(initial&&result.minimum_price&&!current.fixed){form.elements.selling_price.value=result.minimum_price;return quote();}showError(error,result.message||'가격 정보를 확인해 주세요.');preview.textContent='';return;}
   const p=result.pricing;form.elements.reward_points.max=p.maximum_reward_points;
   preview.textContent='예상 이익금 '+amount(p.profit)+'원 · 최대 지급 포인트 '+amount(p.maximum_reward_points)+' Point · 최소 판매가 '+amount(p.minimum_price)+'원';
   submit.disabled=false;
  }catch{if(serial===sequence)showError(error,'가격 계산에 실패했습니다. 다시 입력해 주세요.');}
 }
 form.elements.selling_price.addEventListener('input',()=>{submit.disabled=true;clearTimeout(quoteTimer);quoteTimer=setTimeout(()=>quote(),180);});
 form.elements.reward_points.addEventListener('input',()=>{submit.disabled=true;clearTimeout(quoteTimer);quoteTimer=setTimeout(()=>quote(),180);});
 root.querySelectorAll('[data-add-product]').forEach(b=>b.addEventListener('click',()=>{
  current=s.products.find(p=>p.id===Number(b.dataset.addProduct));mode=b.dataset.mode;form.reset();showError(error,'');
  root.querySelector('[data-product-title]').textContent=mode==='request'?(current.state==='rejected'?'재요청하기':'판매요청하기'):'판매 상품 추가';
  const info=root.querySelector('[data-product-information]');info.replaceChildren(el('h3',current.name),el('p','상품코드 '+current.code),el('p','판매 상품 코드는 추가 완료 시 발급됩니다.'));
  form.elements.selling_price.value=current.price;form.elements.selling_price.readOnly=current.fixed;
  form.elements.reward_points.value=0;form.elements.request_reason.required=mode==='request';root.querySelector('[data-reason]').hidden=mode!=='request';
  submit.textContent=mode==='request'?(current.state==='rejected'?'재요청하기':'판매요청하기'):'상품추가하기';productDialog.showModal();quote(true);
 }));
 form.addEventListener('submit',async event=>{
  event.preventDefault();if(!form.reportValidity()||submit.disabled)return;submit.disabled=true;
  try{const data=Object.fromEntries(new FormData(form));data.shop_id=s.shopId;data.product_id=current.id;
   const result=await request(mode==='request'?s.request:s.store,data);
   if(!result.status){showError(error,result.message);submit.disabled=false;return;}
   location.href=mode==='request'?location.href:listUrl();
  }catch(e){showError(error,e.message);submit.disabled=false;}
 });
 root.querySelectorAll('[data-favorite]').forEach(b=>b.addEventListener('change',async()=>{b.disabled=true;try{await request(s.favorite,{product_id:b.dataset.favorite,favorite:b.checked});const tr=b.closest('tr');if(b.checked)tr.parentNode.prepend(tr);}catch(e){b.checked=!b.checked;showError(root.querySelector('[data-catalog-error]'),e.message);}finally{b.disabled=false;}}));
 root.querySelectorAll('[data-listing-status]').forEach(b=>b.addEventListener('click',async()=>{b.disabled=true;try{const result=await request(s.status,{shop_product_id:b.dataset.listingStatus,status:b.dataset.nextStatus});if(!result.status){showError(root.querySelector('[data-catalog-error]'),result.message);b.disabled=false;return;}location.reload();}catch(e){showError(root.querySelector('[data-catalog-error]'),e.message);b.disabled=false;}}));
 async function detail(id,listing){
  const box=root.querySelector('[data-detail-content]');box.textContent='불러오는 중입니다.';detailDialog.showModal();
  try{const r=await(await fetch(s.detailBase+'/'+id,{headers:{Accept:'application/json'}})).json();if(!r.status)throw Error(r.message);const p=r.product;
   box.replaceChildren(el('h3',p.product_name),el('p',r.category_path));
   if(listing)box.append(el('p','판매 상품 코드 '+listing.code),el('p','판매 가격 '+amount(listing.price)+'원 · 지급포인트 '+amount(listing.reward)+' Point'));
   const images=el('div');images.className='detail-images';(p.image_urls||[]).forEach(url=>{const img=el('img');img.src=url;img.alt=p.product_name;images.append(img);});box.append(images);
   const dl=el('dl');dl.className='shop-details';const fields={'상품코드':p.product_code,'판매자':p.seller_name,'판매형태':p.price_constraint_enabled&&p.price_constraint_type==='range'?'위탁판매':'상품판매','과세구분':p.tax_label,'판매가격 조건':p.price_condition_label,'판매이익 조건':p.profit_share_label,'재고':p.stock_label,'구매제한수량':p.purchase_limit_label};
   Object.entries(fields).forEach(([key,value])=>{const row=el('div');row.append(el('dt',key),el('dd',value));dl.append(row);});box.append(dl);if(listing){const row=el('div');row.append(el('dt','채널 재고'),el('dd',listing.stockText));dl.append(row);}
   const description=el('div');description.className='catalog-description';description.textContent=new DOMParser().parseFromString(p.detail_html||'','text/html').body.textContent;box.append(description);
  }catch(e){box.textContent=e.message||'상품 정보를 불러올 수 없습니다.';}
 }
 root.querySelectorAll('[data-base-view]').forEach(b=>b.addEventListener('click',()=>detail(b.dataset.baseView)));
 root.querySelectorAll('[data-listing-view]').forEach(b=>b.addEventListener('click',()=>{const listing=s.listings.find(l=>l.id===Number(b.dataset.listingView));detail(listing.productId,listing);}));
 root.dataset.catalogReady='true';
})();
