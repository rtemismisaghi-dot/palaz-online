<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="PALAZ ONLINE — فروشگاه و خدمات تخصصی پالاز">
<title>@yield('title','PALAZ ONLINE')</title>
@vite(['resources/css/app.css','resources/js/app.js'])
<style id="palaz-header-rotator">
@media (min-width:701px){
.site-header.header-variant-1 .main-nav{background:transparent}
.site-header.header-variant-2 .main-nav{background:rgba(255,255,255,.55);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}
.site-header.header-variant-3 .main-nav{background:linear-gradient(90deg,rgba(255,255,255,.35),rgba(247,245,242,.78),rgba(255,255,255,.35))}
.site-header.header-variant-2 .nav-inner,.site-header.header-variant-3 .nav-inner{border-color:transparent}
.site-header.header-variant-2 .nav-inner{box-shadow:0 8px 24px rgba(20,20,20,.04)}
.site-header.header-variant-3 .nav-inner{box-shadow:inset 0 1px 0 rgba(255,255,255,.8),0 6px 20px rgba(20,20,20,.035)}
}
</style>

<script>
document.addEventListener('DOMContentLoaded',function(){
 const header=document.querySelector('.site-header');
 if(!header)return;
 let v=1;
 header.classList.add('header-variant-1');
 setInterval(function(){
   header.classList.remove('header-variant-1','header-variant-2','header-variant-3');
   v=v===3?1:v+1;
   header.classList.add('header-variant-'+v);
 },5000);
 const mobileBtn=header.querySelector('.mobile-menu');
 const nav=header.querySelector('.main-nav');
 if(mobileBtn && nav){
   mobileBtn.addEventListener('click',function(){
     const open=header.classList.toggle('mobile-open');
     mobileBtn.setAttribute('aria-expanded',open?'true':'false');
     mobileBtn.textContent=open?'×':'☰';
   });
   nav.querySelectorAll('a').forEach(function(link){
     link.addEventListener('click',function(){
       header.classList.remove('mobile-open');
       mobileBtn.setAttribute('aria-expanded','false');
       mobileBtn.textContent='☰';
     });
   });
 }
});
</script>

<style id="palaz-ai-advisor">
.palaz-ai-trigger{position:fixed;right:24px;bottom:24px;width:86px;height:86px;border:0;border-radius:50%;padding:0;overflow:hidden;z-index:1200;cursor:pointer;background:#fff;box-shadow:0 12px 34px rgba(0,0,0,.18);transition:transform .25s,box-shadow .25s}
.palaz-ai-trigger:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(0,0,0,.22)}
.palaz-ai-trigger img{width:100%;height:100%;object-fit:cover;display:block;animation:palazAiFloat 4.8s ease-in-out infinite;transform-origin:50% 62%}
.palaz-ai-trigger:active img{animation:none;transform:scale(.98)}
.palaz-ai-avatar img{animation:palazAiBreath 3.8s ease-in-out infinite;transform-origin:50% 64%}
.palaz-ai-trigger .ai-pulse{position:absolute;inset:-5px;border:2px solid rgba(163,30,45,.28);border-radius:50%;animation:palazAiPulse 2.4s infinite}
@keyframes palazAiPulse{0%,100%{transform:scale(.94);opacity:.2}50%{transform:scale(1.08);opacity:.75}}
@keyframes palazAiFloat{0%,100%{transform:translate3d(0,0,0) scale(1)}50%{transform:translate3d(0,-2px,0) scale(1.012)}}
@keyframes palazAiBreath{0%,100%{transform:translate3d(0,0,0) scale(1)}45%{transform:translate3d(0,-1px,0) scale(1.008)}70%{transform:translate3d(0,1px,0) scale(.998)}}
.palaz-ai-panel{position:fixed;right:24px;bottom:122px;width:min(390px,calc(100vw - 32px));height:560px;background:#fff;border-radius:24px;z-index:1199;box-shadow:0 24px 70px rgba(0,0,0,.22);overflow:hidden;opacity:0;transform:translateY(18px) scale(.97);pointer-events:none;transition:opacity .25s,transform .25s;display:flex;flex-direction:column}
.palaz-ai-panel.open{opacity:1;transform:none;pointer-events:auto}
.palaz-ai-panel.ai-listening .palaz-ai-avatar{box-shadow:0 0 0 4px rgba(255,255,255,.12),0 0 0 7px rgba(255,255,255,.08)}
.palaz-ai-panel.ai-thinking .palaz-ai-dot{animation:palazAiThink .65s infinite alternate}
.palaz-ai-panel.ai-answering .palaz-ai-avatar img{animation:palazAiAnswer 1.6s ease-in-out infinite}
.palaz-ai-head{display:flex;align-items:center;gap:12px;padding:15px 18px;background:linear-gradient(135deg,#8f1e2d,#b52d40);color:#fff}
.palaz-ai-avatar{width:52px;height:52px;border-radius:50%;overflow:hidden;flex:0 0 auto;border:2px solid rgba(255,255,255,.75);background:#fff}
.palaz-ai-avatar img{width:100%;height:100%;object-fit:cover}
.palaz-ai-head strong{display:block;font-size:15px}.palaz-ai-head small{display:block;margin-top:4px;opacity:.82}
.palaz-ai-close{margin-right:auto;border:0;background:transparent;color:#fff;font-size:25px;cursor:pointer}
.palaz-ai-body{flex:1;padding:20px;overflow:auto;background:#faf9f7}
.palaz-ai-welcome{background:#fff;border:1px solid #eee8e2;border-radius:18px;padding:16px;line-height:1.9}
.palaz-ai-welcome b{display:block;margin-bottom:5px}
.palaz-ai-state{display:flex;align-items:center;gap:7px;margin-top:12px;color:#777;font-size:12px}
.palaz-ai-dot{width:7px;height:7px;border-radius:50%;background:#a51f32;animation:palazAiBlink 1.4s infinite}
@keyframes palazAiBlink{50%{opacity:.25;transform:scale(.75)}}
@keyframes palazAiThink{from{opacity:.3;transform:scale(.7)}to{opacity:1;transform:scale(1.25)}}
@keyframes palazAiRecord{50%{transform:scale(1.08);opacity:.75}}
@keyframes palazAiAnswer{0%,100%{transform:translate3d(0,0,0) scale(1)}50%{transform:translate3d(0,-1px,0) scale(1.012)}}
.palaz-ai-suggestions{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
.palaz-ai-suggestions button{border:1px solid #e4d8d2;background:#fff;border-radius:999px;padding:9px 12px;font-family:inherit;cursor:pointer}
.palaz-ai-foot{padding:12px;border-top:1px solid #eee;background:#fff;display:flex;gap:8px}
.palaz-ai-mic{border:0;background:#f5f5f5;width:38px;height:38px;border-radius:50%;cursor:pointer;font-size:17px;flex:0 0 38px}.palaz-ai-mic.recording{background:#a51d2d;color:#fff;animation:palazAiRecord 1s infinite}.palaz-ai-speak{border:0;background:#f5f5f5;width:38px;height:38px;border-radius:50%;cursor:pointer;font-size:17px;flex:0 0 38px}.palaz-ai-speak.active{background:#a51d2d;color:#fff}.palaz-ai-foot input{flex:1;border:1px solid #ddd;border-radius:14px;padding:11px 13px;font-family:inherit;outline:none}
.palaz-ai-foot button{border:0;border-radius:14px;background:#a51f32;color:#fff;padding:0 16px;font-family:inherit;cursor:pointer}
@media(max-width:700px){.palaz-ai-trigger{right:16px;bottom:16px;width:72px;height:72px}.palaz-ai-panel{right:8px;bottom:8px;width:calc(100vw - 16px);height:min(620px,calc(100vh - 16px));border-radius:22px}.palaz-ai-panel.open~.palaz-ai-trigger{transform:scale(.9)}}
</style></head>
<body>
<div class="palaz-topbar"><div class="container"><span>☎ 021-12345678</span><span>⌖ تهران، جردن، خیابان پالاز</span><b>پشتیبانی ۲۴ ساعته ◔</b></div></div>
<header class="site-header">
<div class="container header-main">
<button class="mobile-menu" type="button" aria-label="منو" aria-expanded="false">☰</button>
<a href="{{ route('home') }}" class="brand-real" aria-label="PALAZ ONLINE"><img src="{{ asset('images/palaz-logo.png') }}?v=20260926" alt="" class="brand-logo"><span class="brand-wordmark"><strong>PALAZ</strong><small>ONLINE</small></span></a>
<form class="search search-real" action="{{ route('shop') }}"><span>⌕</span><input name="q" value="{{ request('q') }}" placeholder="جستجوی محصول، دسته یا برند..." aria-label="جستجو"><button type="submit">⌕</button></form>
<div class="header-actions">
<a href="{{ route('services') }}" class="header-action"><span class="action-icon">♙</span><span>حساب کاربری</span></a>
<a href="{{ route('home') }}#favorite" class="header-action favorite"><span class="action-icon">♡</span><span>علاقه‌مندی‌ها</span></a>
<a href="{{ route('cart') }}" class="header-action cart"><span class="action-icon">🛒</span><span>سبد خرید</span><b>{{ count(session('cart', [])) }}</b></a>
</div>
</div>
<nav class="main-nav"><div class="container nav-inner">
<a href="{{ route('home') }}" class="{{ request()->routeIs('home')?'active':'' }}">خانه</a>
<a href="{{ route('shop') }}" class="{{ request()->routeIs('shop')?'active':'' }}">فروشگاه</a>
<div class="nav-dropdown"><span>محصولات <small>⌄</small></span><div class="mega">
<a href="{{ route('shop',['category'=>'carpet']) }}"><b>موکت</b><small>مجموعه موکت‌های پالاز</small></a>
<a href="{{ route('shop',['category'=>'laminate']) }}"><b>لمینیت</b><small>طرح‌های چوبی و کلاسیک</small></a>
<a href="{{ route('shop',['category'=>'spc']) }}"><b>فرش‌گونه</b><small>مجموعه فرش‌گونه پالاز</small></a>
<a href="{{ route('shop',['category'=>'wallpaper']) }}"><b>کاغذدیواری</b><small>طرح‌ها و رنگ‌های متنوع</small></a>
<a href="{{ route('shop',['category'=>'tile']) }}"><b>موکت تایل</b><small>راهکارهای مدولار</small></a>
<a href="{{ route('shop',['category'=>'grass']) }}"><b>چمن مصنوعی</b><small>برای فضای سبز</small></a>
</div></div>
<a href="{{ route('services') }}">خدمات</a><a href="{{ route('home') }}#journey">طراحی فضای من</a><a href="{{ route('home') }}">مجله پالاز</a><a href="{{ route('home') }}">درباره پالاز</a>
</div></nav>
</header>
@if(session('success'))<div class="flash success">{{ session('success') }}</div>@endif
@if(session('service_success'))<div class="flash success">{{ session('service_success') }}</div>@endif
@yield('content')
<footer class="footer"><div class="container footer-grid">
<div><div class="footer-brand">PALAZ <span>ONLINE</span></div><p>پالاز؛ همراه مطمئن شما در انتخاب، خرید و اجرای پوشش‌های فضای زندگی.</p><div class="footer-social">◎　◉　in　◌</div></div>
<div><h4>فروشگاه</h4><a href="{{ route('shop',['category'=>'carpet']) }}">موکت</a><a href="{{ route('shop',['category'=>'laminate']) }}">لمینیت</a><a href="{{ route('shop',['category'=>'spc']) }}">فرش‌گونه</a><a href="{{ route('shop',['category'=>'wallpaper']) }}">کاغذدیواری</a></div>
<div><h4>خدمات حرفه‌ای</h4><a href="{{ route('services',['type'=>'measurement']) }}">اندازه‌گیری</a><a href="{{ route('services',['type'=>'installation']) }}">نصب حرفه‌ای</a><a href="{{ route('services',['type'=>'design']) }}">طراحی و محاسبه</a><a href="{{ route('services') }}">مشاوره تخصصی</a></div>
<div><h4>راهنمای مشتری</h4><a href="{{ route('cart') }}">سبد خرید</a><a href="{{ route('services') }}">پیگیری خدمات</a><a href="{{ route('home') }}">درباره پالاز</a><a href="{{ route('home') }}">تماس با ما</a></div>
<div class="footer-news"><h4>عضویت در خبرنامه</h4><p>از جدیدترین محصولات و پیشنهادها باخبر شوید.</p><form><input placeholder="ایمیل خود را وارد کنید"><button>→</button></form></div>
</div><div class="container footer-bottom"><span>© {{ date('Y') }} PALAZ ONLINE. All rights reserved.</span><span>طراحی و توسعه برای یک تجربه متصل</span></div></footer>
<button class="palaz-ai-trigger" id="palazAiTrigger" type="button" aria-label="دستیار فروش پالاز"><img src="{{ asset('images/ai-advisor/ChatGPT Image Sep 28, 2026, 08_36_42 PM.png') }}" alt="دستیار فروش پالاز"><span class="ai-pulse"></span></button>
<section class="palaz-ai-panel" id="palazAiPanel" aria-label="دستیار فروش پالاز" aria-hidden="true">
<div class="palaz-ai-head"><div class="palaz-ai-avatar"><img src="{{ asset('images/ai-advisor/ChatGPT Image Sep 28, 2026, 08_36_42 PM.png') }}" alt=""></div><div><strong>دستیار فروش پالاز</strong><small>همراه شما برای انتخاب بهتر</small></div><button class="palaz-ai-close" id="palazAiClose" type="button" aria-label="بستن">×</button></div>
<div class="palaz-ai-body"><div class="palaz-ai-welcome"><b>سلام، من دستیار پالاز هستم 👋</b><span>برای انتخاب موکت، لمینیت، کاغذدیواری و سایر محصولات می‌تونم راهنماییتون کنم.</span><div class="palaz-ai-state"><i class="palaz-ai-dot"></i><span>آماده پاسخگویی</span></div></div><div class="palaz-ai-suggestions"><button type="button">برای پذیرایی چی پیشنهاد می‌دی؟</button><button type="button">محصول مناسب فضای من</button><button type="button">مقایسه محصولات</button></div></div>
<div class="palaz-ai-foot"><button class="palaz-ai-mic" id="palazAiMic" type="button" aria-label="شروع گفت‌وگوی صوتی">🎙</button><input id="palazAiInput" placeholder="سؤال خود را بنویسید..." aria-label="پیام"><button id="palazAiSend" type="button">ارسال</button><button class="palaz-ai-speak" id="palazAiSpeak" type="button" aria-label="خواندن پاسخ با صدا">🔊</button></div>
</section><script>
document.addEventListener('DOMContentLoaded',function(){
 const t=document.getElementById('palazAiTrigger'),p=document.getElementById('palazAiPanel'),c=document.getElementById('palazAiClose');
 if(!t||!p)return;
 function setAiState(state){
 p.classList.remove('ai-listening','ai-thinking','ai-answering');
 const label=p.querySelector('.palaz-ai-state span');
 if(state==='listening'){p.classList.add('ai-listening');label.textContent='در حال گوش دادن…'}
 else if(state==='thinking'){p.classList.add('ai-thinking');label.textContent='در حال فکر کردن…'}
 else if(state==='answering'){p.classList.add('ai-answering');label.textContent='در حال پاسخگویی…'}
 else{label.textContent='آماده پاسخگویی'}
}
 function toggle(open){p.classList.toggle('open',open);p.setAttribute('aria-hidden',open?'false':'true');if(open)setTimeout(()=>document.getElementById('palazAiInput')?.focus(),220)}
 t.addEventListener('click',()=>{const opening=!p.classList.contains('open');toggle(opening);if(opening)setAiState('listening')});c?.addEventListener('click',()=>toggle(false));
 document.addEventListener('keydown',e=>{if(e.key==='Escape')toggle(false)});
 document.querySelectorAll('.palaz-ai-suggestions button').forEach(b=>b.addEventListener('click',()=>{document.getElementById('palazAiInput').value=b.textContent}));
 const send=document.getElementById('palazAiSend'),input=document.getElementById('palazAiInput'),mic=document.getElementById('palazAiMic'),speak=document.getElementById('palazAiSpeak');
 let lastSpoken='سلام، من دستیار فروش پالاز هستم. برای انتخاب محصول مناسب در خدمت شما هستم.';
 function speakText(text){
  if(!('speechSynthesis' in window)){setAiState('ready');return}
  window.speechSynthesis.cancel();const u=new SpeechSynthesisUtterance(text);u.lang='fa-IR';u.rate=.92;u.pitch=1;
  u.onstart=()=>{speak?.classList.add('active');setAiState('answering')};
  u.onend=()=>{speak?.classList.remove('active');setAiState('ready')};
  window.speechSynthesis.speak(u);
 }
 let recognition=null,recording=false;
 const SpeechRecognition=window.SpeechRecognition||window.webkitSpeechRecognition;
 if(SpeechRecognition){
  recognition=new SpeechRecognition();recognition.lang='fa-IR';recognition.interimResults=false;recognition.continuous=false;
  recognition.onstart=()=>{recording=true;mic?.classList.add('recording');mic?.setAttribute('aria-label','توقف گفت‌وگوی صوتی');setAiState('listening')};
  recognition.onend=()=>{recording=false;mic?.classList.remove('recording');mic?.setAttribute('aria-label','شروع گفت‌وگوی صوتی');if(input?.value.trim())setAiState('thinking');else setAiState('ready')};
  recognition.onresult=e=>{const text=e.results?.[0]?.[0]?.transcript||'';input.value=text;if(text.trim()){setTimeout(()=>send?.click(),250)}};
  recognition.onerror=()=>{recording=false;mic?.classList.remove('recording');setAiState('ready')};
 }
 mic?.addEventListener('click',()=>{if(!recognition){setAiState('ready');input.placeholder='مرورگر شما از ورود صوتی پشتیبانی نمی‌کند';return}try{recording?recognition.stop():recognition.start()}catch(e){}});

 send?.addEventListener('click',()=>{if(input?.value.trim()){lastSpoken='سؤال شما دریافت شد. دستیار پالاز در حال بررسی محصول و شرایط فضای شماست.';input.value='';setAiState('thinking');setTimeout(()=>{setAiState('answering');speakText(lastSpoken)},700)}}); 
 speak?.addEventListener('click',()=>speakText(lastSpoken));
 input?.addEventListener('keydown',e=>{if(e.key==='Enter')send?.click()});
});
</script></body></html>