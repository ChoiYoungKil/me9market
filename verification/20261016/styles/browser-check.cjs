const fs=require('node:fs'), path=require('node:path'), http=require('node:http');
const {chromium}=require('/Users/youngkilmacmini/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const allPages=JSON.parse(fs.readFileSync('/tmp/me9-portal-pages/index.json'));
const pages=allPages.filter(p=>!process.env.ME9_UI_PATHS || process.env.ME9_UI_PATHS.split(',').includes(p.path));
const root=path.resolve('public'), snapshots='/tmp/me9-portal-pages';
const out=path.resolve('verification/20261016/styles');fs.mkdirSync(out,{recursive:true});
const mime={'.css':'text/css','.js':'application/javascript','.png':'image/png','.jpg':'image/jpeg','.svg':'image/svg+xml','.woff':'font/woff','.woff2':'font/woff2','.ttf':'font/ttf','.ico':'image/x-icon'};
const server=http.createServer((req,res)=>{
 const uri=new URL(req.url,'http://127.0.0.1:8098').pathname;
 const entry=allPages.find(p=>p.path===uri);
 if(entry){res.setHeader('Content-Type','text/html; charset=utf-8');res.end(fs.readFileSync(path.join(snapshots,entry.file),'utf8').replace(/https?:\/\/(?:localhost|127\.0\.0\.1)(?::\d+)?/g,'http://127.0.0.1:8098'));return;}
 const target=path.resolve(root,'.'+decodeURIComponent(uri));
 if(!target.startsWith(root+path.sep)||!fs.existsSync(target)||fs.statSync(target).isDirectory()){res.writeHead(404);res.end();return;}
 res.setHeader('Content-Type',mime[path.extname(target)]||'application/octet-stream');fs.createReadStream(target).pipe(res);
});
(async()=>{
 await new Promise(r=>server.listen(8098,'127.0.0.1',r));
 const browser=await chromium.launch({executablePath:'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',headless:true});
 const page=await browser.newPage();const results=[];
 for(const width of (process.env.ME9_UI_WIDTHS ? process.env.ME9_UI_WIDTHS.split(',').map(Number) : [1440,390])){
  await page.setViewportSize({width,height:1000});
  for(const item of pages){
   if(item.status!==200)continue;
   const errors=[],missing=[];
   const errorHandler=e=>errors.push(e.message);const responseHandler=r=>{if(r.status()>=400&&r.url().startsWith('http://127.0.0.1'))missing.push(r.url());};
   page.on('pageerror',errorHandler);page.on('response',responseHandler);
   await page.goto('http://127.0.0.1:8098'+item.path,{waitUntil:'load'});await page.waitForTimeout(130);
   const metrics=await page.evaluate(()=>({overflow:document.documentElement.scrollWidth>innerWidth+1,scrollWidth:document.documentElement.scrollWidth,offenders:[...document.querySelectorAll('body *')].filter(e=>{const r=e.getBoundingClientRect();return r.width>0&&r.right>innerWidth+3&&getComputedStyle(e).position!=='fixed'&&!e.closest('.tb01,.table-responsive,.portal-table-scroll')}).slice(0,8).map(e=>({tag:e.tagName,id:e.id,cls:e.className,right:Math.round(e.getBoundingClientRect().right)}))}));
   results.push({path:item.path,width,...metrics,errors,missing});
   if(metrics.overflow||item.path.includes('shipping')||item.path.includes('delivery')||item.role==='distributor'||item.path.includes('/login')||['/admin/dashboard','/channel','/mypage/points/status'].includes(item.path)) await page.screenshot({path:path.join(out,item.role+'-'+item.file.slice(0,-5)+'-'+width+'.png'),fullPage:true});
   page.off('pageerror',errorHandler);page.off('response',responseHandler);
  }
 }
 const interactions=[];
 await page.setViewportSize({width:390,height:1000});
 await page.goto('http://127.0.0.1:8098/admin/shipping-charges',{waitUntil:'load'});
 const form=page.locator('#new-delivery-policy');
 await form.locator('[name="shipping_type"]').selectOption('variable');
 interactions.push({check:'variable controls visible',passed:await form.locator('[name="calculation_rules[repeat_every]"]').isVisible()});
 await form.locator('[name="shipping_type"]').selectOption('tiered');
 interactions.push({check:'tiered hides variable-only controls',passed:!(await form.locator('[name="calculation_rules[repeat_every]"]').isVisible())});
 const before=await form.locator('[data-shipping-tier]').count();
 await form.locator('[data-add-tier]').click();
 interactions.push({check:'tier add inserts exactly one row',passed:(await form.locator('[data-shipping-tier]').count())===before+1});
 for(const uri of ['/admin/shipping-charges','/channel/settings/delivery','/mypage/points/status']){
  await page.goto('http://127.0.0.1:8098'+uri,{waitUntil:'load'});
  await page.locator('.portal-menu-toggle').click();
  interactions.push({check:uri+' mobile menu',passed:await page.locator('#l_menu').isVisible()});
 }
 await page.goto('http://127.0.0.1:8098/channel/settings/delivery',{waitUntil:'load'});
 await page.locator('[data-pop="pop_delivery_create"]').click();
 const popup=page.locator('[data-id="pop_delivery_create"]');
 await popup.locator('[name="shipping_type"]').selectOption('tiered');
 interactions.push({check:'channel shipping modal and tier controls',passed:await popup.locator('[data-shipping-tiers]').isVisible()});
 await page.waitForTimeout(400);
 await page.screenshot({path:path.join(out,'channel-delivery-modal-390.png'),fullPage:true});
 fs.writeFileSync(path.join(out,'interactions.json'),JSON.stringify(interactions,null,2));
 fs.writeFileSync(path.join(out,process.env.ME9_UI_REPORT || 'browser.json'),JSON.stringify(results,null,2));
 console.log(JSON.stringify(results.filter(x=>x.overflow||x.errors.length||x.missing.length),null,2));
 await browser.close();server.close();
})();
