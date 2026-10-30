(() => {
    const root=document.getElementById('channel-shop-detail');if(!root)return;
    const dialog=root.querySelector('#detail-private-dialog');
    root.querySelector('[data-detail-private]')?.addEventListener('click',()=>dialog.showModal());
    root.querySelector('[data-detail-close]')?.addEventListener('click',()=>dialog.close());
})();
