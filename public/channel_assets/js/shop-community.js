(() => {
 const form=document.getElementById('community-form'),editor=document.getElementById('community-editor');
 if(form&&editor){
  const sync=()=>form.elements.content.value=editor.innerHTML;editor.addEventListener('input',sync);
  document.querySelectorAll('[data-editor-command]').forEach(b=>{b.addEventListener('mousedown',e=>e.preventDefault());b.addEventListener('click',()=>{editor.focus();document.execCommand(b.dataset.editorCommand,false,null);sync();});});
  form.addEventListener('submit',e=>{sync();const error=form.querySelector('[data-editor-error]');if(!editor.textContent.trim()){e.preventDefault();error.textContent='내용을 입력해 주세요.';error.hidden=false;editor.focus();}});
 }
 document.querySelector('[data-delete-notice]')?.addEventListener('submit',e=>{if(!confirm('게시글을 삭제하시겠습니까? 삭제 후 복구할 수 없습니다.'))e.preventDefault();});
})();
