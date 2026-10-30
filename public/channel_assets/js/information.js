(() => {
    const root=document.getElementById('channel-information'); if(!root)return;
    root.querySelectorAll('[data-open-dialog]').forEach(button=>button.addEventListener('click',()=>document.getElementById(button.dataset.openDialog).showModal()));
    root.querySelectorAll('[data-close-dialog]').forEach(button=>button.addEventListener('click',()=>button.closest('dialog').close()));
    root.querySelectorAll('[data-clear-file]').forEach(button=>button.addEventListener('click',()=>{document.getElementById(button.dataset.clearFile).value='';}));
    const pg=root.querySelector('[data-pg-fields]');
    const updatePg=()=>{pg.hidden=root.querySelector('[name=use_own_pg]:checked').value!=='1';};
    root.querySelectorAll('[name=use_own_pg]').forEach(input=>input.addEventListener('change',updatePg));updatePg();
    root.querySelector('[data-pg-provider]').addEventListener('change',()=>pg.querySelectorAll('input').forEach(input=>{input.value='';input.placeholder='변경한 PG사의 연결 정보 입력';}));
    root.querySelector('[data-email-domain]').addEventListener('change',event=>{if(!event.target.value)return;const field=root.querySelector('[name=shop_email]');field.value=field.value.split('@')[0]+'@'+event.target.value;});
    root.querySelector('[data-postcode]').addEventListener('click',()=>{
        if(!window.daum?.Postcode){alert('주소 검색을 불러오지 못했습니다. 잠시 후 다시 시도해 주세요.');return;}
        new daum.Postcode({oncomplete:data=>{root.querySelector('#shop_pincode').value=data.zonecode;root.querySelector('#shop_address').value=data.userSelectedType==='R'?data.roadAddress:data.jibunAddress;root.querySelector('#shop_address_detail').focus();}}).open();
    });
    root.querySelector('#infoForm').addEventListener('invalid',event=>{const details=event.target.closest('details');if(details)details.open=true;},true);
    root.querySelector('[data-password-form]').addEventListener('submit',async event=>{
        event.preventDefault();const form=event.target;const feedback=form.querySelector('[data-password-feedback]');
        try{const response=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}});const result=await response.json();feedback.textContent=result.message||'변경 결과를 확인해 주세요.';if(result.status==='success')form.reset();}
        catch{feedback.textContent='비밀번호 변경에 실패했습니다. 잠시 후 다시 시도해 주세요.';}
    });
})();
