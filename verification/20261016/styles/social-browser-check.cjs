const fs = require('node:fs'), path = require('node:path'), http = require('node:http');
const { chromium } = require('/Users/youngkilmacmini/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const pages = JSON.parse(fs.readFileSync('/tmp/me9-social-pages/index.json'));
const root = path.resolve('public'), output = path.resolve('verification/20261016/styles');
const mime = {'.css':'text/css','.js':'application/javascript','.png':'image/png','.jpg':'image/jpeg','.svg':'image/svg+xml','.woff':'font/woff','.woff2':'font/woff2','.ttf':'font/ttf'};
const server = http.createServer((req,res)=>{
 const uri = new URL(req.url, 'http://127.0.0.1:8099').pathname;
 const entry = pages.find(p=>p.path===uri);
 if(entry){res.setHeader('Content-Type','text/html; charset=utf-8');res.end(fs.readFileSync('/tmp/me9-social-pages/'+entry.file,'utf8').replace(/https?:\/\/(?:localhost|127\.0\.0\.1)(?::\d+)?/g, 'http://127.0.0.1:8099'));return;}
 const file = path.resolve(root,'.'+decodeURIComponent(uri));
 if(!file.startsWith(root+path.sep)||!fs.existsSync(file)||fs.statSync(file).isDirectory()){res.writeHead(404);res.end();return;}
 res.setHeader('Content-Type',mime[path.extname(file)]||'application/octet-stream');fs.createReadStream(file).pipe(res);
});
(async()=>{
 await new Promise(r=>server.listen(8099,'127.0.0.1',r));
 const browser = await chromium.launch({executablePath:'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',headless:true});
 const results=[];
 try {
  for(const width of [1440,390]) {
   const page=await browser.newPage({viewport:{width,height:1000}});
   for(const item of pages){
    const errors=[],missing=[];
    const onerror=e=>errors.push(e.message), onresponse=r=>{if(r.status()>=400&&r.url().startsWith('http://127.0.0.1:8099'))missing.push(r.url());};
    page.on('pageerror',onerror);page.on('response',onresponse);
    await page.goto('http://127.0.0.1:8099'+item.path,{waitUntil:'load'});
    const metrics=await page.evaluate(()=>({overflow:document.documentElement.scrollWidth>innerWidth+1,titleHidden:(()=>{const h=document.querySelector('.social-signup-card h1'),header=document.querySelector('#header');return !!(h&&header&&h.getBoundingClientRect().top<header.getBoundingClientRect().bottom);})(),buttons:[...document.querySelectorAll('.social-login-button,.social-signup-card button,.social-connections button')].map(e=>({text:e.innerText,width:e.getBoundingClientRect().width,height:e.getBoundingClientRect().height}))}));
    await page.screenshot({path:path.join(output,'social-'+item.file.replace('.html','')+'-'+width+'.png'),fullPage:true});
    results.push({path:item.path,width,...metrics,errors,missing});
    page.off('pageerror',onerror);page.off('response',onresponse);
   }
   await page.close();
  }
  fs.writeFileSync(path.join(output,'social-browser.json'),JSON.stringify(results,null,2));
  const failures=results.filter(r=>r.overflow||r.titleHidden||r.errors.length||r.missing.length||r.buttons.some(b=>b.width<40||b.height<40));
  console.log(JSON.stringify({checks:results.length,failures},null,2));
  if(failures.length)process.exitCode=1;
 }finally{await browser.close();server.close();}
})().catch(e=>{console.error(e);process.exitCode=1;server.close();});
