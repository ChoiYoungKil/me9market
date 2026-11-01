(() => {
 'use strict';
 const busyTokens=new Set();let busyNode;
 const ensureBusy=()=>{if(busyNode)return;busyNode=document.createElement('div');busyNode.className='admin-busy';busyNode.hidden=true;busyNode.setAttribute('role','status');busyNode.setAttribute('aria-live','polite');busyNode.innerHTML='<div class="admin-busy-box"><div class="admin-spinner" aria-hidden="true"></div><p data-busy-message>정보를 수신하고 있습니다…</p></div>';document.body.append(busyNode);};
 window.AdminBusy={show(message='정보를 수신하고 있습니다…'){ensureBusy();const host=document.querySelector('dialog[open]')||document.body;if(busyNode.parentElement!==host)host.append(busyNode);const token=Symbol();busyTokens.add(token);busyNode.querySelector('[data-busy-message]').textContent=message;busyNode.hidden=false;return token;},hide(token){busyTokens.delete(token);if(busyNode)busyNode.hidden=busyTokens.size===0;},reset(){busyTokens.clear();if(busyNode)busyNode.hidden=true;}};
 window.adminRequest=async(url,options={})=>{const target=new URL(url,location.origin);if(target.origin!==location.origin)throw Error('같은 사이트의 요청만 처리할 수 있습니다.');const token=AdminBusy.show(options.busyMessage);const controller=new AbortController();const timeout=setTimeout(()=>controller.abort(),30000);try{const headers=new Headers(options.headers||{});headers.set('X-Requested-With','XMLHttpRequest');headers.set('Accept',options.accept||'application/json');const csrf=document.querySelector('meta[name=csrf-token]')?.content;if(csrf)headers.set('X-CSRF-TOKEN',csrf);const response=await fetch(target.href,{...options,headers,signal:controller.signal,credentials:'same-origin'});const isJson=(response.headers.get('content-type')||'').includes('application/json');const data=isJson?await response.json():await response.text();if(!response.ok||data?.status===false){const message=typeof data==='object'?(data.message||Object.values(data.errors||{}).flat().join(' ')):'요청을 처리하지 못했습니다.';throw Error(response.status===419?'로그인 시간이 만료되었습니다. 새로고침 후 다시 시도해 주세요.':message);}return data;}catch(error){if(error.name==='AbortError')throw Error('응답 시간이 초과되었습니다. 처리 결과를 조회한 뒤 다시 시도해 주세요.');throw error;}finally{clearTimeout(timeout);AdminBusy.hide(token);}};
 const message=(form,text,error=false)=>{let node=form.querySelector('[data-form-message]');if(!node){node=document.createElement('div');node.dataset.formMessage='';form.prepend(node);}node.className='admin-alert '+(error?'error':'success');node.setAttribute('role',error?'alert':'status');node.textContent=text;};
 document.addEventListener('DOMContentLoaded',()=>{
  window.addEventListener('pageshow',()=>{AdminBusy.reset();document.querySelectorAll('form[data-submitting]').forEach(f=>delete f.dataset.submitting);});
  document.addEventListener('submit',event=>{const form=event.target.closest('form[method=post]:not([data-admin-ajax])');if(!form)return;if(form.dataset.submitting){event.preventDefault();return;}form.dataset.submitting='1';setTimeout(()=>{if(!event.defaultPrevented)AdminBusy.show('저장하고 있습니다…');else delete form.dataset.submitting;},0);});
  if(window.jQuery){let ajaxBusy;$(document).ajaxStart(()=>{ajaxBusy=AdminBusy.show();}).ajaxStop(()=>AdminBusy.hide(ajaxBusy));}
  document.querySelectorAll('[data-select-all]').forEach(input=>input.addEventListener('change',()=>{document.querySelectorAll(input.dataset.selectAll).forEach(row=>{if(!row.disabled)row.checked=input.checked;});}));
  document.querySelectorAll('[data-dialog-open]').forEach(button=>button.addEventListener('click',()=>document.getElementById(button.dataset.dialogOpen)?.showModal()));
  document.querySelectorAll('[data-dialog-close]').forEach(button=>button.addEventListener('click',()=>button.closest('dialog')?.close()));
  document.querySelectorAll('[data-popover]').forEach(button=>{const popup=document.getElementById(button.dataset.popover);if(!popup)return;button.addEventListener('click',()=>{popup.hidden=!popup.hidden;button.setAttribute('aria-expanded',String(!popup.hidden));const box=button.getBoundingClientRect();popup.style.left=Math.max(12,Math.min(box.left,window.innerWidth-popup.offsetWidth-12))+'px';popup.style.top=Math.max(12,Math.min(box.bottom+8,window.innerHeight-popup.offsetHeight-12))+'px';});document.addEventListener('keydown',event=>{if(event.key==='Escape'){popup.hidden=true;button.setAttribute('aria-expanded','false');}});});
  document.querySelectorAll('[data-popover-close]').forEach(button=>button.addEventListener('click',()=>{button.closest('.admin-popover').hidden=true;}));
  document.querySelectorAll('[data-admin-ajax]').forEach(form=>form.addEventListener('submit',async event=>{event.preventDefault();if(form.dataset.submitting)return;form.dataset.submitting='1';const button=event.submitter;if(button)button.disabled=true;try{const data=await adminRequest(form.getAttribute('action') || location.href,{method:'POST',body:new FormData(form)});message(form,data.message||'저장되었습니다.');if(data.redirect){if(form.closest('dialog')){window.location.assign(data.redirect);}else if(window.opener){window.opener.postMessage({type:'admin-record-saved'},location.origin);window.location.assign(data.redirect+'?popup=1');}else window.location.assign(data.redirect);}else if(form.dataset.successUrl)window.location.assign(form.dataset.successUrl);else if(form.hasAttribute('data-reload'))window.location.reload();}catch(error){message(form,error.message,true);}finally{delete form.dataset.submitting;if(button)button.disabled=false;}}));
  document.querySelectorAll('[data-panel-url]').forEach(link=>link.addEventListener('click',async event=>{event.preventDefault();const area=document.getElementById(link.dataset.panelTarget);if(!area)return;try{const html=await adminRequest(link.dataset.panelUrl,{accept:'text/html'});area.innerHTML=html;const stateUrl=new URL(location.href);stateUrl.searchParams.set('tab',new URL(link.dataset.panelUrl,location.origin).pathname.split('/').pop());history.replaceState(null,'',stateUrl);document.querySelectorAll('[data-panel-target="'+link.dataset.panelTarget+'"]').forEach(t=>{t.classList.toggle('active',t===link);t.setAttribute('aria-selected',String(t===link));});}catch(error){area.textContent=error.message;}}));
  document.querySelectorAll('[data-loading-demo]').forEach(button=>button.addEventListener('click',async()=>{const output=document.getElementById(button.dataset.output);try{const result=await adminRequest(button.dataset.loadingDemo);output.textContent=result.message;}catch(error){output.textContent=error.message;}}));
  document.querySelectorAll('[data-popup-close]').forEach(button=>button.addEventListener('click',()=>{if(window.opener)window.close();else location.assign(button.dataset.fallback||'/admin/notices');}));
  document.querySelectorAll('[data-new-window]').forEach(link=>link.addEventListener('click',event=>{event.preventDefault();window.open(link.href,'adminEditor','width=1000,height=850,scrollbars=yes,resizable=yes');}));
  window.addEventListener('message',event=>{if(event.origin===location.origin&&event.data?.type==='admin-record-saved'&&event.source!==window)window.location.reload();});
  document.querySelectorAll('[data-file-name]').forEach(input=>input.addEventListener('change',()=>{const output=document.getElementById(input.dataset.fileName);if(output)output.textContent=input.files?.[0]?.name||'선택한 파일 없음';}));
 });
})();
document.addEventListener('DOMContentLoaded',()=>{
 document.querySelectorAll('[data-rich-editor]').forEach(editor=>editor.addEventListener('input',()=>{document.getElementById(editor.dataset.input).value=editor.innerHTML;}));
 document.querySelectorAll('[data-editor-command]').forEach(button=>{button.addEventListener('mousedown',event=>event.preventDefault());button.addEventListener('click',()=>{const editor=document.getElementById(button.dataset.editorTarget);editor.focus();document.execCommand(button.dataset.editorCommand,false,null);editor.dispatchEvent(new Event('input'));});});
 document.querySelectorAll('[data-auto-dialog]').forEach(dialog=>dialog.showModal());
});
document.addEventListener('DOMContentLoaded',()=>{
 document.querySelectorAll('[data-admin-delete]').forEach(button=>button.addEventListener('click',()=>{if(window.confirm('선택한 자료를 삭제하시겠습니까?'))window.submitAdminDelete(button.dataset.adminDelete);}));
});
document.addEventListener('change',event=>{
 const select=event.target.closest('[data-claim-action]');if(!select)return;
 const form=select.closest('form');const action=select.value;
 form.querySelectorAll('[data-claim-field]').forEach(field=>{const type=field.dataset.claimField;const shown=type==='reason'?['return_hold','exchange_hold_before','exchange_hold_after'].includes(action):type==='invoice'?['return_invoice','exchange_invoice'].includes(action):action==='exchange_option';field.hidden=!shown;field.querySelectorAll('input,select').forEach(input=>input.required=shown);});
});
document.addEventListener('submit',async event=>{
 const form=event.target.closest('#order-panel form[data-admin-ajax]');if(!form)return;
 event.preventDefault();if(form.dataset.submitting)return;form.dataset.submitting='1';const button=event.submitter;if(button)button.disabled=true;
 let node=form.querySelector('[data-form-message]');if(!node){node=document.createElement('p');node.dataset.formMessage='';form.prepend(node);}
 try{const data=await window.adminRequest(form.getAttribute('action') || location.href,{method:'POST',body:new FormData(form)});node.textContent=data.message;node.className='admin-alert success';location.reload();}catch(error){node.textContent=error.message;node.className='admin-alert error';node.setAttribute('role','alert');}finally{delete form.dataset.submitting;if(button)button.disabled=false;}
});

document.addEventListener('DOMContentLoaded',()=>{
 const dialog=document.getElementById('admin-activity-detail');if(!dialog)return;
 const body=dialog.querySelector('[data-activity-body]');let version=0;
 dialog.addEventListener('close',()=>{version++;body.textContent='';});
 document.querySelectorAll('[data-activity-detail]').forEach(button=>button.addEventListener('click',async()=>{
  const current=++version;body.textContent='처리 내역을 불러오고 있습니다.';dialog.showModal();
  try{const html=await window.adminRequest(button.dataset.activityDetail,{accept:'text/html'});if(current===version)body.innerHTML=html;}
  catch(error){if(current===version)body.textContent=error.message;}
 }));
});
