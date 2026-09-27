@extends('layouts.store')
@section('title','PALAZ ONLINE | صفحه اصلی')
@section('content')
<style>
@media (min-width:1101px){
  .palaz-reference-home .ref-search-row{
    width:min(600px,calc(100% - 120px))!important;
    height:42px!important;
    min-height:42px!important;
    margin:0 auto 4px!important;
    padding:0!important;
    display:block!important;
  }
  .palaz-reference-home .ref-search-row .ref-search{
    position:relative!important;
    inset:auto!important;
    width:100%!important;
    max-width:none!important;
    height:42px!important;
    min-height:42px!important;
    margin:0!important;
    padding:0!important;
    transform:none!important;
    display:flex!important;
    box-sizing:border-box!important;
  }
  .palaz-reference-home .ref-search-row .ref-search input{
    flex:1 1 auto!important;
  }
  .palaz-reference-home .ref-search-row .ref-search button{
    flex:0 0 45px!important;
    width:45px!important;
  }
  .palaz-reference-home .ref-head-main{
    width:min(1120px,calc(100% - 120px))!important;
    height:58px!important;
    min-height:58px!important;
    margin:0 auto!important;
    display:flex!important;
    align-items:center!important;
    justify-content:flex-start!important;
    position:relative!important;
  }
  .palaz-reference-home .ref-actions{
    position:absolute!important;
    left:0!important;
    top:50%!important;
    transform:translateY(-50%)!important;
  }
  .palaz-reference-home .ref-logo,
  .palaz-reference-home .ref-mobile-cart,
  .palaz-reference-home .ref-mobile-btn{
    display:none!important;
  }
  .palaz-reference-home .ref-nav{
    width:min(1120px,calc(100% - 120px))!important;
    margin:0 auto 8px!important;
    padding:0!important;
    background:transparent!important;
    border:0!important;
    border-radius:0!important;
    box-shadow:none!important;
    backdrop-filter:none!important;
    -webkit-backdrop-filter:none!important;
    overflow:visible!important;
  }
  .palaz-reference-home .ref-nav:before,
  .palaz-reference-home .ref-nav:after{
    display:none!important;
    content:none!important;
  }
  .palaz-reference-home .ref-nav .ref-wrap{
    width:100%!important;
    height:50px!important;
    min-height:50px!important;
    padding:4px 10px!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    gap:10px!important;
    background:transparent!important;
    border:0!important;
    box-shadow:none!important;
  }
  .palaz-reference-home .ref-nav a,
  .palaz-reference-home .ref-nav a:hover,
  .palaz-reference-home .ref-nav a.active{
    height:40px!important;
    min-height:40px!important;
    padding:0 15px!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    border:0!important;
    outline:0!important;
    border-radius:0!important;
    background:transparent!important;
    box-shadow:none!important;
    transform:none!important;
    color:#34383d!important;
    font-size:11px!important;
    font-weight:600!important;
    position:relative!important;
    transition:color .2s ease!important;
  }
  .palaz-reference-home .ref-nav a:hover,
  .palaz-reference-home .ref-nav a.active{color:#bd1827!important}
  .palaz-reference-home .ref-nav a.active:after{
    content:""!important;
    position:absolute!important;
    left:15px!important;
    right:15px!important;
    bottom:3px!important;
    height:2px!important;
    border-radius:2px!important;
    background:#bd1827!important;
  }
}

/* Palaz experience layer — lightweight, responsive, visual-first */
.palaz-experience{font-family:inherit}
.palaz-experience .px-wrap{width:min(1240px,calc(100% - 40px));margin:auto}
.palaz-experience .px-kicker{font-size:11px;letter-spacing:.08em;color:#9b1825;font-weight:800}
.palaz-experience .px-title{font-size:clamp(28px,4vw,48px);line-height:1.2;margin:8px 0 10px;color:#202327}
.palaz-experience .px-sub{color:#73777c;line-height:1.9;margin:0}
.palaz-experience .px-btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:46px;padding:0 22px;border-radius:14px;text-decoration:none;font-weight:800;transition:.2s ease}
.palaz-experience .px-btn.red{background:#b71929;color:#fff;box-shadow:0 10px 28px rgba(183,25,41,.16)}
.palaz-experience .px-btn.soft{background:#fff;color:#25282c;border:1px solid #e8e8e8}
.palaz-experience .px-btn:hover{transform:translateY(-2px)}
.palaz-experience .px-journey{padding:58px 0 24px}
.palaz-experience .px-head{display:flex;align-items:end;justify-content:space-between;gap:20px;margin-bottom:24px}
.palaz-experience .px-head a{color:#b71929;text-decoration:none;font-weight:800}
.palaz-experience .px-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:14px}
.palaz-experience .px-card{position:relative;min-height:190px;border-radius:22px;overflow:hidden;background:#eee;text-decoration:none;color:#fff;isolation:isolate}
.palaz-experience .px-card img{width:100%;height:100%;object-fit:cover;position:absolute;inset:0;transition:transform .45s ease}
.palaz-experience .px-card:after{content:"";position:absolute;inset:0;background:linear-gradient(0deg,rgba(0,0,0,.62),rgba(0,0,0,.04) 70%)}
.palaz-experience .px-card:hover img{transform:scale(1.035)}
.palaz-experience .px-card-content{position:absolute;z-index:2;right:18px;left:18px;bottom:16px}
.palaz-experience .px-card-content strong{display:block;font-size:19px;margin-bottom:3px}
.palaz-experience .px-card-content span{font-size:12px;opacity:.85}
.palaz-experience .px-tools{padding:34px 0 64px}
.palaz-experience .px-tool-shell{display:grid;grid-template-columns:1.15fr .85fr;min-height:410px;border-radius:28px;overflow:hidden;background:#f3f1ee}
.palaz-experience .px-tool-image{position:relative;background:url('https://palazonline.com/storage/uploads/005-1-2.jpg') center/cover;min-height:410px}
.palaz-experience .px-tool-image:after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(0,0,0,.05),rgba(0,0,0,.32))}
.palaz-experience .px-tool-copy{padding:46px 42px;display:flex;flex-direction:column;justify-content:center}
.palaz-experience .px-tool-copy .px-title{font-size:clamp(28px,3.5vw,42px)}
.palaz-experience .px-pills{display:flex;flex-wrap:wrap;gap:8px;margin:20px 0 24px}
.palaz-experience .px-pill{border:1px solid #dedbd6;background:#fff;border-radius:999px;padding:10px 14px;font-size:12px;font-weight:800;color:#555}
.palaz-experience .px-pill.active{border-color:#b71929;color:#b71929;background:#fff7f8}
.palaz-experience .px-quick{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:22px}
.palaz-experience .px-quick a{padding:15px;border:1px solid #e7e4df;border-radius:16px;background:#fff;text-decoration:none;color:#292c30}

.palaz-experience .px-visualizer-shell{position:relative}
.palaz-experience .px-visualizer-preview{display:flex;align-items:center;justify-content:center;background-image:url('https://palazonline.com/storage/uploads/005-1-2.jpg');background-position:center;background-size:cover;transition:background-image .25s ease}
.palaz-experience .px-preview-empty{position:relative;z-index:3;width:min(330px,calc(100% - 40px));padding:28px 24px;text-align:center;border:1px solid rgba(255,255,255,.55);border-radius:22px;background:rgba(255,255,255,.88);box-shadow:0 18px 45px rgba(0,0,0,.12);backdrop-filter:blur(8px)}
.palaz-experience .px-preview-empty>span{display:grid;place-items:center;width:42px;height:42px;margin:0 auto 12px;border-radius:50%;background:#b71929;color:#fff;font-size:25px}
.palaz-experience .px-preview-empty strong{display:block;color:#25282c;font-size:17px;margin-bottom:5px}
.palaz-experience .px-preview-empty small{display:block;color:#777;line-height:1.8;margin-bottom:16px}
.palaz-experience .px-upload-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 18px;border-radius:12px;background:#25282c;color:#fff;font-weight:800;font-size:12px;cursor:pointer}
.palaz-experience .px-preview-badge{position:absolute;z-index:4;left:18px;top:18px;padding:7px 10px;border-radius:999px;background:rgba(0,0,0,.52);color:#fff;font-size:9px;font-weight:900;letter-spacing:.12em}
.palaz-experience .px-visualizer-pills button{font:inherit;cursor:pointer}
.palaz-experience .px-visualizer-status{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 20px;padding:11px 13px;border:1px solid #e8e4df;border-radius:13px;background:#fff}
.palaz-experience .px-visualizer-status>span{width:8px;height:8px;border-radius:50%;background:#b71929}
.palaz-experience .px-visualizer-status b{font-size:12px;color:#333}
.palaz-experience .px-visualizer-status small{width:100%;padding-right:16px;color:#888;font-size:10px}
@media(max-width:900px){.palaz-experience .px-visualizer-preview{min-height:320px}}
.palaz-experience .px-quick b{display:block;margin-bottom:4px}
.palaz-experience .px-quick span{font-size:11px;color:#888}
.palaz-experience .px-connected{padding:8px 0 58px}
.palaz-experience .px-connected-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.palaz-experience .px-connected-card{min-height:250px;border-radius:24px;padding:30px;position:relative;overflow:hidden;background:#202327;color:#fff}
.palaz-experience .px-connected-card.light{background:#f7f5f2;color:#25282c}
.palaz-experience .px-tour-card{background:#111!important;padding:0!important;min-height:250px;position:relative;isolation:isolate;}
.palaz-experience .px-tour-card iframe{position:absolute;inset:0;width:100%;height:100%;border:0;display:block;transform:scale(1.02);transform-origin:center;z-index:0;}

.palaz-experience .px-tour-card .px-tour-content{position:relative;z-index:2;pointer-events:none;padding:30px;max-width:70%;}
.palaz-experience .px-tour-card .px-tour-content .px-icon,.palaz-experience .px-tour-card .px-tour-content .px-kicker,.palaz-experience .px-tour-card .px-tour-content h3,.palaz-experience .px-tour-card .px-tour-content p{pointer-events:none;}
.palaz-experience .px-tour-card .px-btn{z-index:3;}
.palaz-experience .px-connected-card .px-icon{font-size:30px;display:block;margin-bottom:18px}
.palaz-experience .px-connected-card h3{font-size:26px;margin:0 0 8px}
.palaz-experience .px-connected-card p{line-height:1.9;max-width:520px;color:inherit;opacity:.78}
.palaz-experience .px-connected-card .px-btn{position:absolute;right:30px;bottom:26px}
.palaz-experience .px-chat{padding:0 0 64px}
.palaz-experience .px-chat-box{border:1px solid #ebe7e3;border-radius:24px;padding:26px;background:#fff;box-shadow:0 12px 40px rgba(20,20,20,.05);display:flex;align-items:center;justify-content:space-between;gap:24px}
.palaz-experience .px-chat-bubble{display:flex;gap:12px;align-items:flex-start}
.palaz-experience .px-chat-avatar{width:46px;height:46px;border-radius:50%;background:#b71929;color:#fff;display:grid;place-items:center;font-weight:900;flex:0 0 auto}
.palaz-experience .px-chat-bubble strong{display:block;margin-bottom:5px}
.palaz-experience .px-chat-bubble span{color:#777;font-size:13px;line-height:1.8}
.palaz-experience .px-advisor-teaser{border:1px solid #ebe7e3;border-radius:26px;padding:30px;background:linear-gradient(135deg,#fff 0%,#faf7f5 100%);box-shadow:0 12px 40px rgba(20,20,20,.05);display:flex;align-items:center;justify-content:space-between;gap:24px}
.palaz-experience .px-advisor-teaser .px-title{margin-bottom:8px}
.palaz-experience .px-advisor-teaser .px-title em{font-style:normal;color:#b71929}
.palaz-advisor-backdrop{position:fixed;inset:0;z-index:99999;background:rgba(20,22,25,.38);backdrop-filter:blur(5px);-webkit-backdrop-filter:blur(5px);display:flex;align-items:stretch;justify-content:flex-start;opacity:0;visibility:hidden;pointer-events:none;transition:opacity .22s ease,visibility .22s ease}
.palaz-advisor-backdrop.is-open{opacity:1;visibility:visible;pointer-events:auto}
.palaz-advisor-panel{width:min(470px,100%);height:100%;margin-left:auto;background:#fff;box-shadow:-20px 0 70px rgba(0,0,0,.18);display:flex;flex-direction:column;transform:translateX(24px);transition:transform .28s ease;direction:rtl}
.palaz-advisor-backdrop.is-open .palaz-advisor-panel{transform:translateX(0)}
.palaz-advisor-header{min-height:76px;padding:12px 18px;border-bottom:1px solid #eee9e5;display:flex;align-items:center;justify-content:space-between;gap:14px;flex:0 0 auto}
.palaz-advisor-brand{display:flex;align-items:center;gap:11px;min-width:0}
.palaz-advisor-brand img{width:86px;height:auto;max-height:42px;object-fit:contain;object-position:right center}
.palaz-advisor-brand strong{display:block;color:#202327;font-size:14px}
.palaz-advisor-brand span{display:flex;align-items:center;gap:5px;color:#8a8d91;font-size:10px;margin-top:5px}
.palaz-advisor-brand span i{width:6px;height:6px;border-radius:50%;background:#2b9a62;display:inline-block}
.palaz-advisor-close{width:38px;height:38px;border:1px solid #ebe7e3;border-radius:50%;background:#fff;color:#555;font-size:25px;line-height:1;cursor:pointer;flex:0 0 auto}
.palaz-advisor-messages{flex:1;overflow:auto;padding:24px 18px 14px;background:linear-gradient(#fbfaf9,#fff)}
.palaz-advisor-message{display:flex;align-items:flex-end;gap:9px;margin-bottom:16px}
.palaz-advisor-avatar{width:32px;height:32px;border-radius:50%;background:#25282c;color:#fff;display:grid;place-items:center;font-size:11px;font-weight:900;flex:0 0 auto}
.palaz-advisor-bubble{max-width:82%;padding:12px 15px;border-radius:17px 17px 5px 17px;background:#f2efec;color:#333;font-size:13px;line-height:1.9}
.palaz-advisor-message.user{justify-content:flex-start;direction:ltr}
.palaz-advisor-message.user .palaz-advisor-bubble{background:#b71929;color:#fff;border-radius:17px 17px 17px 5px;direction:rtl}
.palaz-advisor-message.user .palaz-advisor-avatar{background:#eee;color:#555;order:2}
.palaz-advisor-suggestions{display:flex;gap:7px;overflow-x:auto;padding:10px 18px 7px;border-top:1px solid #f0ece8;scrollbar-width:none}
.palaz-advisor-suggestions::-webkit-scrollbar{display:none}
.palaz-advisor-suggestions button{border:1px solid #e3ddd8;background:#fff;border-radius:999px;padding:8px 11px;color:#555;font:inherit;font-size:10px;white-space:nowrap;cursor:pointer}
.palaz-advisor-suggestions button:hover{border-color:#b71929;color:#b71929}
.palaz-advisor-input{margin:0 14px;padding:8px;border:1px solid #ded9d4;border-radius:17px;background:#fff;display:flex;align-items:center;gap:6px;box-shadow:0 8px 25px rgba(20,20,20,.05)}
.palaz-advisor-input input{min-width:0;flex:1;border:0;outline:0;background:transparent;color:#25282c;font:inherit;font-size:13px;padding:7px 4px}
.palaz-advisor-input input::placeholder{color:#a3a1a0}
.palaz-advisor-mic,.palaz-advisor-send{width:36px;height:36px;border:0;border-radius:11px;display:grid;place-items:center;cursor:pointer;flex:0 0 auto}
.palaz-advisor-mic{background:#f4f1ef;color:#555}
.palaz-advisor-mic.is-listening{background:#b71929;color:#fff}
.palaz-advisor-send{background:#25282c;color:#fff;font-size:20px;font-weight:800}
.palaz-advisor-mic svg{width:17px;height:17px;fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.palaz-advisor-note{text-align:center;color:#aaa;font-size:9px;padding:9px 14px 13px;line-height:1.7}
.palaz-advisor-shortcut{position:fixed;right:20px;top:92px;height:50px;padding:0 14px 0 11px;border:1px solid #eadbdd;border-radius:18px;background:linear-gradient(135deg,#fff 0%,#fff8f8 100%);color:#34383d;display:flex;align-items:center;gap:9px;font:inherit;font-size:11px;font-weight:800;cursor:pointer;box-shadow:0 10px 30px rgba(20,20,20,.14);transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease;z-index:100001;animation:palazAdvisorPulse 2.2s ease-in-out infinite}
.palaz-advisor-shortcut:hover{border-color:#b71929;color:#b71929;transform:translateY(-3px);box-shadow:0 14px 34px rgba(20,20,20,.18)}
.palaz-advisor-shortcut-icon{width:34px;height:34px;border-radius:12px;background:linear-gradient(145deg,#b71929,#8e1420);color:#fff;display:grid;place-items:center;position:relative;box-shadow:0 6px 14px rgba(183,25,41,.22)}
.palaz-advisor-shortcut-icon:after{content:"";position:absolute;inset:-5px;border:1px solid rgba(183,25,41,.35);border-radius:15px;animation:palazAdvisorRing 2.2s ease-out infinite}
.palaz-advisor-shortcut-icon img{width:24px;height:24px;object-fit:contain;display:block;filter:brightness(0) invert(1)}
@keyframes palazAdvisorPulse{0%,100%{box-shadow:0 10px 30px rgba(20,20,20,.14)}50%{box-shadow:0 10px 30px rgba(183,25,41,.24)}}
@keyframes palazAdvisorRing{0%{transform:scale(.72);opacity:.8}100%{transform:scale(1.35);opacity:0}}
@media(max-width:1100px){.palaz-advisor-shortcut{right:18px;top:84px}.palaz-advisor-shortcut>span:last-child{display:inline}}
@media(max-width:900px){
 .palaz-experience .px-wrap{width:min(100% - 28px,700px)}
 .palaz-experience .px-grid{grid-template-columns:repeat(2,1fr)}
 .palaz-experience .px-tool-shell{grid-template-columns:1fr}
 .palaz-experience .px-tool-image{min-height:270px}
 .palaz-experience .px-connected-grid{grid-template-columns:1fr}
 .palaz-experience .px-chat-box{align-items:stretch;flex-direction:column}
}
@media(max-width:560px){
 .palaz-experience .px-journey{padding-top:38px}
 .palaz-experience .px-head{display:block}
 .palaz-experience .px-head a{display:inline-block;margin-top:12px}
 .palaz-experience .px-grid{grid-template-columns:1fr 1fr;gap:9px}
 .palaz-experience .px-card{min-height:145px;border-radius:16px}
 .palaz-experience .px-card-content{right:12px;left:12px;bottom:11px}
 .palaz-experience .px-card-content strong{font-size:15px}
 .palaz-experience .px-tool-shell{border-radius:20px}
 .palaz-experience .px-tool-copy{padding:28px 22px}
 .palaz-experience .px-quick{grid-template-columns:1fr}
 .palaz-experience .px-connected-card{min-height:235px;padding:24px;border-radius:20px}
 .palaz-experience .px-connected-card .px-btn{right:24px;bottom:22px}
 .palaz-experience .px-advisor-teaser{padding:24px 20px;align-items:stretch;flex-direction:column}
 .palaz-advisor-shortcut{right:14px;top:76px;height:46px;padding:0 12px}
 .palaz-advisor-panel{width:100%}
 .palaz-advisor-header{min-height:70px}
 .palaz-advisor-brand img{width:78px}
}


@media (min-width:1101px){
  .palaz-reference-home .ref-header,
  .palaz-reference-home .ref-head-main,
  .palaz-reference-home .ref-search-row{
    overflow:visible!important;
  }
  .palaz-reference-home .ref-head-main{
    position:relative!important;
    z-index:100!important;
  }
  .palaz-reference-home .ref-logo-clean{
    position:absolute!important;
    top:6px!important;
    right:0!important;
    z-index:10000!important;
    overflow:visible!important;
    clip-path:none!important;
    transform:none!important;
  }
  .palaz-reference-home .ref-logo-clean .ref-logo-p-image{
    width:42px!important;
    height:42px!important;
    max-width:42px!important;
    max-height:42px!important;
    flex:0 0 42px!important;
    object-fit:contain!important;
    overflow:visible!important;
  }
  .palaz-reference-home .ref-search{
    z-index:2!important;
  }
}

@media (min-width:1101px){
  .palaz-reference-home .ref-head-main{padding-right:0!important;padding-left:0!important;}
  .palaz-reference-home .ref-logo-clean{
    display:flex!important;align-items:center!important;justify-content:flex-start!important;
    position:absolute!important;right:0!important;top:10px!important;transform:none!important;
    width:220px!important;height:52px!important;min-width:220px!important;
    text-decoration:none!important;z-index:20!important;overflow:visible!important;
    background:transparent!important;padding:0!important;box-sizing:border-box!important;
    direction:ltr!important;gap:12px!important;
  }
  .palaz-reference-home .ref-logo-clean .ref-logo-p-image{
    width:42px!important;height:42px!important;flex:0 0 42px!important;
    display:block!important;object-fit:contain!important;object-position:center!important;
    max-width:42px!important;max-height:42px!important;border:0!important;overflow:visible!important;
  }
  .palaz-reference-home .ref-logo-clean .ref-logo-text{
    color:#111!important;font:800 30px/1 Arial,sans-serif!important;
    letter-spacing:-1.5px!important;
  }
  .palaz-reference-home .ref-actions{left:0!important;right:auto!important;}
}
</style>
<div class="palaz-reference-home" dir="rtl">

  <header class="ref-header">
    <div class="ref-topbar">
      <div class="ref-wrap">
        <span>☎ 021-75332</span>
        <span class="ref-branch-address">⌖ شعبه آزادی: خیابان آزادی، خیابان آذربایجان، پلاک ۹۷۹</span>
        <b>پشتیبانی ۲۴ ساعته ◔</b>
        <script>
          (() => {
            const address = document.querySelector('.ref-branch-address');
            if (!address) return;
            const addresses = [
              '⌖ شعبه آزادی: خیابان آزادی، خیابان آذربایجان، پلاک ۹۷۹',
              '⌖ شعبه شریعتی: خیابان شریعتی، بالاتر از پل صدر، مجتمع الماس',
              '⌖ شعبه سهروردی: خیابان سهروردی، نرسیده به میدان پالیزی',
              '⌖ شعبه شهرک غرب: شهرک غرب، پل مدیریت، مجتمع رویال'
            ];
            let index = 0;
            window.setInterval(() => {
              index = (index + 1) % addresses.length;
              address.style.opacity = '0';
              window.setTimeout(() => {
                address.textContent = addresses[index];
                address.style.opacity = '1';
              }, 180);
            }, 4000);
          })();
        </script>
      </div>
    </div>
    <div class="ref-head-main ref-wrap">
      <button class="ref-mobile-btn" type="button" aria-label="منو">☰</button>
      <a href="{{ route('home') }}" class="ref-logo ref-logo-clean" aria-label="PALAZ ONLINE"><img class="ref-logo-p-image" src="{{ asset('images/palaz-p-mark.png') }}" alt="PALAZ"><span class="ref-logo-text">PALAZ</span></a>
      <div class="ref-actions">
        <a href="{{ route('services') }}"><i>♙</i><span>حساب کاربری</span></a>
        <a href="{{ route('home') }}#favorite"><i>♡</i><span>علاقه‌مندی‌ها</span></a>
        <a href="{{ route('cart') }}" class="ref-cart"><i>🛒</i><span>سبد خرید</span><b>{{ count(session('cart', [])) }}</b></a>
      </div>
      <button class="palaz-advisor-shortcut" type="button" aria-label="باز کردن مشاور هوشمند پالاز" aria-controls="palaz-advisor-panel" aria-expanded="false">
        <span class="palaz-advisor-shortcut-icon palaz-advisor-palaz-mark" aria-hidden="true">
          <img src="{{ asset('images/palaz-p-mark.png') }}" alt="" loading="eager">
        </span>
        <span>مشاور هوشمند</span>
      </button>
      <a href="{{ route('cart') }}" class="ref-mobile-cart">🛒<b>{{ count(session('cart', [])) }}</b></a>
    </div>
    <div class="ref-search-row">
      <form class="ref-search" action="{{ route('shop') }}">
        <input name="q" value="{{ request('q') }}" placeholder="جستجوی محصول، دسته‌بندی یا برند...">
        <button type="submit">⌕</button>
      </form>
    </div>
    <nav class="ref-nav">
      <div class="ref-wrap">
        <a class="active" href="{{ route('home') }}">صفحه اصلی</a>
        <a href="{{ route('shop') }}">فروشگاه</a>
        <a href="{{ route('shop') }}">محصولات⌄</a>
        <a href="{{ route('services',['type'=>'design']) }}">ایده‌های دکوراسیون</a>
        <a href="{{ route('services') }}">خدمات</a>
        <a href="{{ route('home') }}">درباره ما</a>
        <a href="{{ route('home') }}">تماس با ما</a>
      </div>
    </nav>
  </header>

  <main>
    <section class="ref-hero">
      <div class="ref-hero-bg"></div>
      <div class="ref-hero-overlay"></div>
      <div class="ref-wrap ref-hero-content">
        <div class="ref-hero-copy">
          <small>PALAZ ONLINE EXPERIENCE</small>
          <h1>فضای شما،<br><em>از اینجا شروع می‌شود</em></h1>
          <p>محصول را انتخاب کن، فضای خودت را تصور کن، نتیجه را ببین و اگر خواستی از انتخاب تا اجرا همراهت هستیم.</p>
          <div class="ref-buttons">
            <a class="ref-btn red" href="{{ route('shop') }}">مشاهده محصولات <b>‹</b></a>
            <a class="ref-btn light" href="{{ route('services',['type'=>'design']) }}">طراحی فضای من <b>←</b></a>
          </div>
        </div>
        <div class="ref-hero-services">
          <a href="{{ route('shop') }}"><i>♙</i><span><b>خرید محصول</b><small>موکت، لمینیت، کاغذ دیواری و...</small></span><b>›</b></a>
          <a class="selected" href="{{ route('services',['type'=>'measurement']) }}"><i>⌗</i><span><b>درخواست اندازه‌گیری</b><small>با DTZ</small></span><b>›</b></a>
          <a href="{{ route('services',['type'=>'installation']) }}"><i>⌁</i><span><b>درخواست نصب</b><small>با DTZ</small></span><b>›</b></a>
          <a href="{{ route('services',['type'=>'design']) }}"><i>▤</i><span><b>طراحی و محاسبه</b><small>DTZ Tablet</small></span><b>›</b></a>
        </div>
      </div>
      <div class="ref-dots"><b class="on"></b><b></b><b></b></div>
      <script>
        (() => {
          const hero = document.querySelector('.palaz-reference-home .ref-hero');
          const bg = hero?.querySelector('.ref-hero-bg');
          const dots = hero ? [...hero.querySelectorAll('.ref-dots b')] : [];
          if (!hero || !bg || dots.length !== 3) return;
          const images = [
            'https://palazonline.com/storage/uploads/IMG_1100-4.PNG',
            'https://palazonline.com/storage/uploads/IMG_5777.PNG',
            'https://palazonline.com/storage/uploads/IMG_5796.jpg'
          ];
          let index = 0;
          const show = (next) => {
            bg.style.opacity = '0';
            window.setTimeout(() => {
              index = next;
              bg.style.backgroundImage = `url('${images[index]}')`;
              dots.forEach((dot, i) => dot.classList.toggle('on', i === index));
              bg.style.opacity = '1';
            }, 280);
          };
          images.slice(1).forEach(src => { const image = new Image(); image.src = src; });
          window.setInterval(() => show((index + 1) % images.length), 4000);
        })();
      </script>
    </section>


    <div class="palaz-experience">
      <section class="px-journey">
        <div class="px-wrap">
          <div class="px-head">
            <div><span class="px-kicker">01 / YOUR SPACE</span><h2 class="px-title">فضای خودت را پیدا کن</h2><p class="px-sub">از خود فضا شروع کن، نه از قفسه محصولات.</p></div>
            <a href="{{ route('services',['type'=>'design']) }}">شروع انتخاب ←</a>
          </div>
          <div class="px-grid">
            <a class="px-card" href="{{ route('services',['type'=>'design']) }}"><img src="https://palazonline.com/storage/uploads/IMG_1100-4.PNG" loading="lazy" alt="خانه"><div class="px-card-content"><strong>خانه</strong><span>پذیرایی، اتاق خواب و فضاهای شخصی</span></div></a>
            <a class="px-card" href="{{ route('services',['type'=>'design']) }}"><img src="https://palazonline.com/storage/uploads/010-1.jpg" loading="lazy" alt="اداری"><div class="px-card-content"><strong>اداری</strong><span>دفتر، فضای کاری و پروژه‌های تجاری</span></div></a>
            <a class="px-card" href="{{ route('services',['type'=>'design']) }}"><img src="https://palazonline.com/storage/uploads/IMG_5777.PNG" loading="lazy" alt="هتل و پروژه"><div class="px-card-content"><strong>هتل و پروژه</strong><span>راهکارهای حرفه‌ای برای پروژه‌های بزرگ</span></div></a>
            <a class="px-card" href="{{ route('services',['type'=>'design']) }}"><img src="https://palazonline.com/storage/uploads/IMG_5796.jpg" loading="lazy" alt="فضاهای خاص"><div class="px-card-content"><strong>فضاهای خاص</strong><span>ترکیب‌های متفاوت برای سلیقه‌های متفاوت</span></div></a>
          </div>
        </div>
      </section>

      <section class="px-tools" id="visualizer">
        <div class="px-wrap">
          <div class="px-tool-shell px-visualizer-shell">
            <div class="px-tool-image px-visualizer-preview" role="img" aria-label="پیش‌نمایش فضای انتخابی">
              <div class="px-preview-empty">
                <span>＋</span>
                <strong>عکس فضای خودت را اضافه کن</strong>
                <small>یک عکس از پذیرایی، اتاق یا دفترت انتخاب کن</small>
                <label class="px-upload-btn">انتخاب عکس<input id="px-space-upload" type="file" accept="image/*" hidden></label>
              </div>
              <div class="px-preview-badge">PREVIEW</div>
            </div>
            <div class="px-tool-copy">
              <span class="px-kicker">02 / VISUALIZER</span>
              <h2 class="px-title">قبل از انتخاب،<br>ببین.</h2>
              <p class="px-sub">عکس فضای خودت را وارد کن و نوع پوشش را انتخاب کن تا اولین پیش‌نمایش را همین‌جا ببینی.</p>
              <div class="px-pills px-visualizer-pills">
                <button type="button" class="px-pill active" data-surface="carpet">موکت</button>
                <button type="button" class="px-pill" data-surface="laminate">لمینیت</button>
                <button type="button" class="px-pill" data-surface="spc">فرش‌گونه</button>
                <button type="button" class="px-pill" data-surface="wallpaper">کاغذدیواری</button>
              </div>
              <div class="px-visualizer-status"><span></span><b>آماده برای انتخاب</b><small>پیش‌نمایش اولیه؛ موتور تصویرسازی پیشرفته بعداً متصل می‌شود.</small></div>
              <a class="px-btn red" href="{{ route('services',['type'=>'design']) }}">ادامه طراحی فضای من ←</a>
              <div class="px-quick">
                <a href="{{ route('shop') }}"><b>🧩 انتخاب محصول</b><span>بعد از پیش‌نمایش، محصول واقعی را انتخاب کن.</span></a>
                <a href="{{ route('services',['type'=>'measurement']) }}"><b>⌗ درخواست اندازه‌گیری</b><span>اگر آماده اجرا هستی، اندازه‌گیری را ثبت کن.</span></a>
              </div>
            </div>
          </div>
        </div>
        <script>
          (() => {
            const root = document.querySelector('.palaz-reference-home #visualizer');
            if (!root) return;
            const preview = root.querySelector('.px-visualizer-preview');
            const empty = root.querySelector('.px-preview-empty');
            const upload = root.querySelector('#px-space-upload');
            const pills = [...root.querySelectorAll('[data-surface]')];
            const status = root.querySelector('.px-visualizer-status');
            const surfaces = {
              carpet: 'https://palazonline.com/storage/uploads/005-1-2.jpg',
              laminate: 'https://palazonline.com/storage/uploads/IMG_1100-4.PNG',
              spc: 'https://palazonline.com/storage/uploads/IMG_5777.PNG',
              wallpaper: 'https://palazonline.com/storage/uploads/IMG_5796.jpg'
            };
            let uploadedUrl = '';
            const applySurface = (key) => {
              pills.forEach(p => p.classList.toggle('active', p.dataset.surface === key));
              if (uploadedUrl) {
                preview.style.backgroundImage = `linear-gradient(rgba(0,0,0,.08),rgba(0,0,0,.08)),url('${uploadedUrl}')`;
                preview.dataset.surface = key;
                status.querySelector('b').textContent = 'فضای شما آماده پیش‌نمایش است';
              } else {
                preview.style.backgroundImage = `linear-gradient(rgba(0,0,0,.04),rgba(0,0,0,.18)),url('${surfaces[key]}')`;
                empty.style.display = 'none';
                status.querySelector('b').textContent = 'نمونه فضای انتخاب‌شده';
              }
            };
            pills.forEach(p => p.addEventListener('click', () => applySurface(p.dataset.surface)));
            upload?.addEventListener('change', e => {
              const file = e.target.files?.[0];
              if (!file) return;
              if (uploadedUrl) URL.revokeObjectURL(uploadedUrl);
              uploadedUrl = URL.createObjectURL(file);
              preview.style.backgroundImage = `linear-gradient(rgba(0,0,0,.06),rgba(0,0,0,.12)),url('${uploadedUrl}')`;
              empty.style.display = 'none';
              status.querySelector('b').textContent = 'عکس شما آماده پیش‌نمایش است';
              status.querySelector('small').textContent = 'حالا نوع پوشش را انتخاب کن.';
            });
          })();
        </script>
      </section>

      <section class="px-connected" id="tour">
        <div class="px-wrap">
          <div class="px-connected-grid">
            <article class="px-connected-card px-tour-card">
              <iframe src="https://palazonline.com/360-azadi/index.html" title="تور مجازی شعبه آزادی پالاز" loading="lazy" allow="fullscreen"></iframe>
              <div class="px-tour-content"></div>
              <a class="px-btn red" href="https://palazonline.com/page/azadi-360" target="_blank" rel="noopener noreferrer">شروع تور مجازی ←</a>
            </article>
            <article class="px-connected-card light">
              <span class="px-icon">⌁</span><span class="px-kicker">04 / PALAZ + DTZ</span>
              <h3>از انتخاب تا اجرا</h3>
              <p>انتخاب در Palaz Online و عملیات اندازه‌گیری و نصب در DTZ؛ دو سیستم مستقل با اتصال مشخص.</p>
              <a class="px-btn soft" href="{{ route('services',['type'=>'measurement']) }}">درخواست اندازه‌گیری ←</a>
            </article>
          </div>
        </div>
      </section>

      <section class="px-chat" id="smart-advisor">
        <div class="px-wrap">
          <div class="px-advisor-teaser">
            <div>
              <span class="px-kicker">03 / SMART ADVISOR</span>
              <h2 class="px-title">هر سؤالی داری، <em>بپرس.</em></h2>
              <p class="px-sub">برای انتخاب محصول، کاربرد، اندازه‌گیری و مسیر اجرا با مشاور پالاز گفتگو کن.</p>
            </div>
            <button class="px-btn red px-open-advisor" type="button">شروع گفتگو ←</button>
          </div>
        </div>
      </section>

      <div class="palaz-advisor-backdrop" id="palaz-advisor-panel" aria-hidden="true">
        <section class="palaz-advisor-panel" role="dialog" aria-modal="true" aria-labelledby="palaz-advisor-title">
          <header class="palaz-advisor-header">
            <div class="palaz-advisor-brand">
              <img src="{{ asset('images/palaz-original-logo.png') }}" alt="PALAZ ONLINE">
              <div><strong id="palaz-advisor-title">مشاور هوشمند پالاز</strong><span><i></i> آماده گفتگو</span></div>
            </div>
            <button class="palaz-advisor-close" type="button" aria-label="بستن مشاور">×</button>
          </header>

          <div class="palaz-advisor-messages" aria-live="polite">
            <div class="palaz-advisor-message assistant">
              <div class="palaz-advisor-avatar">P</div>
              <div class="palaz-advisor-bubble">سلام 👋 من مشاور پالاز هستم.<br>برای انتخاب بهترین پوشش، از فضای شما شروع کنیم؟</div>
            </div>
          </div>

          <div class="palaz-advisor-suggestions" aria-label="پیشنهادهای سریع">
            <button type="button" data-message="برای پذیرایی چه محصولی پیشنهاد می‌دهید؟">برای پذیرایی</button>
            <button type="button" data-message="برای اتاق خواب راهنمایی می‌خواهم.">برای اتاق خواب</button>
            <button type="button" data-message="می‌خواهم قیمت و محاسبه را بدانم.">قیمت و محاسبه</button>
            <button type="button" data-message="برای اندازه‌گیری راهنمایی می‌خواهم.">اندازه‌گیری</button>
          </div>

          <form class="palaz-advisor-input" autocomplete="off">
            <button class="palaz-advisor-mic" type="button" aria-label="ورودی صوتی" title="گفتگوی صوتی">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15.5a3.5 3.5 0 0 0 3.5-3.5V7a3.5 3.5 0 0 0-7 0v5a3.5 3.5 0 0 0 3.5 3.5Z"/><path d="M5.5 11.5a6.5 6.5 0 0 0 13 0M12 18v3M9 21h6"/></svg>
            </button>
            <input type="text" name="message" placeholder="پیامتان را بنویسید..." aria-label="پیام شما">
            <button class="palaz-advisor-send" type="submit" aria-label="ارسال پیام">↑</button>
          </form>
          <div class="palaz-advisor-note">مشاور پالاز می‌تواند درباره انتخاب، مقایسه، محاسبه و اجرای محصول راهنمایی کند.</div>
        </section>
      </div>
    </div>

    @php
      $refImages = [
        'carpet'=>'https://palazonline.com/storage/uploads/005-1-2.jpg',
        'laminate'=>'https://palazonline.com/storage/uploads/IMG_1100-4.PNG',
        'spc'=>'https://palazonline.com/storage/uploads/IMG_5777.PNG',
        'wallpaper'=>'https://palazonline.com/storage/uploads/IMG_5796.jpg',
        'tile'=>'https://palazonline.com/storage/uploads/4.jpg',
        'grass'=>'https://palazonline.com/storage/uploads/IMG_5795.PNG',
      ];
    @endphp

    <section class="ref-section ref-categories">
      <div class="ref-wrap">
        <div class="ref-section-head">
          <div><small>01 / COLLECTIONS</small><h2>دسته‌بندی محصولات</h2><p>محصول موردنظرت را انتخاب کن</p></div>
          <a href="{{ route('shop') }}">مشاهده همه محصولات <b>‹</b></a>
        </div>
        <div class="ref-category-grid ref-category-grid-full">
          <a class="ref-category all" href="{{ route('shop') }}"><span>⌘</span><strong>همه محصولات</strong><b>‹</b></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'carpet']) }}"><img src="{{ $refImages['carpet'] }}" alt="موکت"><div><strong>موکت</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'laminate']) }}"><img src="{{ $refImages['laminate'] }}" alt="لمینیت"><div><strong>لمینیت</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'spc']) }}"><img src="{{ $refImages['spc'] }}" alt="فرش‌گونه"><div><strong>فرش‌گونه</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'wallpaper']) }}"><img src="{{ $refImages['wallpaper'] }}" alt="کاغذ دیواری"><div><strong>کاغذ دیواری</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'tile']) }}"><img src="{{ $refImages['tile'] }}" alt="موکت تایل"><div><strong>موکت تایل</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'grass']) }}"><img src="{{ $refImages['grass'] }}" alt="چمن مصنوعی"><div><strong>چمن مصنوعی</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'spc']) }}"><img src="{{ $refImages['laminate'] }}" alt="فرش‌گونه"><div><strong>فرش‌گونه</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'carpet-tile']) }}"><img src="{{ $refImages['tile'] }}" alt="گارد و اسپاگتی"><div><strong>گارد و اسپاگتی</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'decorative']) }}"><img src="{{ $refImages['wallpaper'] }}" alt="پادری"><div><strong>پادری</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop') }}"><img src="{{ $refImages['carpet'] }}" alt="سایر محصولات"><div><strong>سایر محصولات</strong><b>‹</b></div></a>
        </div>
      </div>
    </section>

    <section class="ref-services">
      <div class="ref-wrap ref-services-layout">
        <div class="ref-services-intro">
          <small>خدمات هوشمند پالاز</small>
          <h2>از انتخاب تا اجرا،<br>با خیال راحت</h2>
          <p>در تمام مراحل همراه شما هستیم؛ از مشاوره و بازدید تا نصب و پشتیبانی.</p>
          <a href="{{ route('services') }}" class="ref-btn red">بیشتر بدانید <b>‹</b></a>
        </div>
        <div class="ref-service-grid">
          <a href="{{ route('services',['type'=>'measurement']) }}"><img src="https://palazonline.com/storage/uploads/IMG_5777.PNG" alt="اندازه گیری"><i>⌗</i><h3>اندازه‌گیری دقیق</h3><small>با DTZ</small><b>مشاهده جزئیات ←</b></a>
          <a href="{{ route('services',['type'=>'installation']) }}"><img src="https://palazonline.com/storage/uploads/IMG_1100-4.PNG" alt="نصب"><i>⌁</i><h3>نصب حرفه‌ای</h3><small>با DTZ</small><b>مشاهده جزئیات ←</b></a>
          <a href="{{ route('services',['type'=>'design']) }}"><img src="https://palazonline.com/storage/uploads/010-1.jpg" alt="طراحی"><i>▤</i><h3>طراحی و محاسبه</h3><small>DTZ Tablet</small><b>مشاهده جزئیات ←</b></a>
          <a class="ref-service-mobile-only" href="{{ route('services') }}"><img src="https://palazonline.com/storage/uploads/IMG_5796.jpg" alt="مشاوره و پشتیبانی"><i>♧</i><h3>مشاوره و پشتیبانی</h3><small>همیشه در کنار شما</small><b>مشاهده جزئیات ←</b></a>
        </div>
      </div>
    </section>

    <section class="ref-section ref-products">
      <div class="ref-wrap">
        <div class="ref-section-head product-head">
          <div><small>02 / PRODUCTS</small><h2>پیشنهادهای منتخب</h2><p>محبوب‌ترین محصولات با بهترین قیمت</p></div>
          <a href="{{ route('shop') }}">مشاهده همه <b>‹</b></a>
        </div>
        <div class="ref-product-layout">
          <div class="ref-product-grid">
            @foreach($products as $i=>$product)
              @if($i < 6)
                @php $image = $refImages[$product['category']] ?? $refImages['carpet']; @endphp
                <article class="ref-product">
                  <a href="{{ route('product',$product['id']) }}" class="ref-product-image">
                    <img src="{{ $image }}" alt="{{ $product['name'] }}" loading="lazy"><span>♡</span>
                  </a>
                  <div class="ref-product-body">
                    <small>{{ $product['category'] }}</small>
                    <h3><a href="{{ route('product',$product['id']) }}">{{ $product['name'] }}</a></h3>
                    <p>{{ $product['unit'] }}</p>
                    <div class="ref-stars">★★★★★</div>
                    <strong>{{ $product['price'] ? number_format($product['price']).' تومان' : 'تماس بگیرید' }}</strong>
                    <form method="POST" action="{{ route('cart.add',$product['id']) }}">@csrf<button type="submit">افزودن به سبد <span>🛒</span></button></form>
                  </div>
                </article>
              @endif
            @endforeach
          </div>
          <a class="ref-product-promo" href="{{ route('services',['type'=>'design']) }}">
            <div><small>الهام بگیرید</small><h3>کوراسیون<br><em>فضای واقعی</em></h3><p>ایده‌هایی برای یک زندگی زیباتر</p><span>مشاهده پروژه‌ها ←</span></div>
          </a>
        </div>
      </div>
    </section>

    <section class="ref-design">
      <div class="ref-wrap ref-design-inner">
        <div class="ref-design-image"></div>
        <div class="ref-design-copy">
          <small>PALAZ DESIGN STUDIO</small>
          <h2>فضای خودت را <em>طراحی کن</em></h2>
          <p>عکس فضای خودت را وارد کن، محصول مناسب را انتخاب کن و نتیجه را قبل از اجرا ببین.</p>
          <a href="{{ route('services',['type'=>'design']) }}" class="ref-btn red">شروع طراحی <b>‹</b></a>
        </div>
      </div>
    </section>

    <section class="ref-trust">
      <div class="ref-wrap">
        <div><i>✓</i><span><b>ضمانت کیفیت</b><small>محصولات اصیل و باکیفیت</small></span></div>
        <div><i>▣</i><span><b>ارسال سریع</b><small>به سراسر کشور</small></span></div>
        <div><i>♧</i><span><b>مشاوره تخصصی</b><small>پشتیبانی قبل و بعد خرید</small></span></div>
        <div><i>⌗</i><span><b>اندازه‌گیری دقیق</b><small>توسط کارشناسان مجرب</small></span></div>
      </div>
    </section>
  </main>

  <footer class="ref-footer">
    <div class="ref-wrap ref-footer-grid">
      <div class="ref-footer-brand"><img src="{{ asset('images/palaz-original-logo.png') }}" alt="PALAZ ONLINE"><p>پالاز، انتخابی مطمئن برای زیبایی و دوام فضای زندگی شما.</p><div>◎　◉　in　◌</div></div>
      <div><h4>دسته‌بندی محصولات</h4><a href="{{ route('shop',['category'=>'carpet']) }}">موکت</a><a href="{{ route('shop',['category'=>'laminate']) }}">لمینیت</a><a href="{{ route('shop',['category'=>'wallpaper']) }}">کاغذ دیواری</a><a href="{{ route('shop',['category'=>'grass']) }}">چمن مصنوعی</a></div>
      <div><h4>لینک‌های مفید</h4><a href="{{ route('home') }}">درباره ما</a><a href="{{ route('home') }}">تماس با ما</a><a href="{{ route('services') }}">خدمات</a><a href="{{ route('services') }}">پیگیری سفارش</a></div>
      <div class="ref-news"><h4>عضویت در خبرنامه</h4><p>برای دریافت جدیدترین محصولات و تخفیف‌ها ایمیل خود را وارد کنید.</p><form><input placeholder="ایمیل خود را وارد کنید"><button>←</button></form></div>
    </div>
    <div class="ref-wrap ref-footer-bottom"><span>© {{ date('Y') }} PALAZ ONLINE. All rights reserved.</span><span>صفحه اصلی　 |　 محصولات　 |　 خدمات　 |　 تماس با ما</span></div>
  </footer>

  <script>
    (() => {
      const backdrop = document.getElementById('palaz-advisor-panel');
      if (!backdrop) return;
      const panel = backdrop.querySelector('.palaz-advisor-panel');
      const openers = [...document.querySelectorAll('.palaz-advisor-shortcut, .px-open-advisor')];
      const close = backdrop.querySelector('.palaz-advisor-close');
      const form = backdrop.querySelector('.palaz-advisor-input');
      const input = form?.querySelector('input');
      const messages = backdrop.querySelector('.palaz-advisor-messages');
      const mic = backdrop.querySelector('.palaz-advisor-mic');
      const suggestions = [...backdrop.querySelectorAll('.palaz-advisor-suggestions button')];
      let lastFocusedElement = null;
      let previousBodyOverflow = '';

      const openAdvisor = () => {
        lastFocusedElement = document.activeElement;
        previousBodyOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        backdrop.classList.add('is-open');
        backdrop.setAttribute('aria-hidden','false');
        openers.forEach(btn => btn.setAttribute('aria-expanded','true'));
        window.setTimeout(() => input?.focus(), 180);
      };
      const closeAdvisor = () => {
        backdrop.classList.remove('is-open');
        backdrop.setAttribute('aria-hidden','true');
        openers.forEach(btn => btn.setAttribute('aria-expanded','false'));
        document.body.style.overflow = previousBodyOverflow;
        window.setTimeout(() => {
          if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
            lastFocusedElement.focus();
          }
          lastFocusedElement = null;
        }, 220);
      };
      openers.forEach(btn => btn.addEventListener('click', openAdvisor));
      close?.addEventListener('click', closeAdvisor);
      backdrop.addEventListener('click', e => { if (e.target === backdrop) closeAdvisor(); });
      document.addEventListener('keydown', e => { if (e.key === 'Escape' && backdrop.classList.contains('is-open')) closeAdvisor(); });

      const addMessage = (text, role) => {
        const row = document.createElement('div');
        row.className = 'palaz-advisor-message ' + role;
        const avatar = document.createElement('div');
        avatar.className = 'palaz-advisor-avatar';
        avatar.textContent = role === 'assistant' ? 'P' : 'شما';
        const bubble = document.createElement('div');
        bubble.className = 'palaz-advisor-bubble';
        bubble.textContent = text;
        row.appendChild(avatar);
        row.appendChild(bubble);
        messages.appendChild(row);
        messages.scrollTop = messages.scrollHeight;
      };

      const reply = () => {
        window.setTimeout(() => {
          addMessage('حتماً. چند سؤال کوتاه از فضای شما می‌پرسم تا بتوانیم گزینه‌های مناسب را دقیق‌تر بررسی کنیم.', 'assistant');
        }, 550);
      };

      form?.addEventListener('submit', e => {
        e.preventDefault();
        const value = input.value.trim();
        if (!value) return;
        addMessage(value, 'user');
        input.value = '';
        reply();
      });

      suggestions.forEach(btn => btn.addEventListener('click', () => {
        input.value = btn.dataset.message || btn.textContent.trim();
        form?.requestSubmit();
      }));

      mic?.addEventListener('click', () => {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
          input?.focus();
          input?.setAttribute('placeholder','مرورگر شما ورودی صوتی را پشتیبانی نمی‌کند');
          window.setTimeout(() => input?.setAttribute('placeholder','پیامتان را بنویسید...'), 2500);
          return;
        }
        const recognition = new SpeechRecognition();
        recognition.lang = 'fa-IR';
        recognition.interimResults = false;
        recognition.maxAlternatives = 1;
        mic.classList.add('is-listening');
        recognition.start();
        recognition.onresult = e => {
          input.value = e.results[0][0].transcript;
          input.focus();
        };
        recognition.onerror = () => mic.classList.remove('is-listening');
        recognition.onend = () => mic.classList.remove('is-listening');
      });
    })();
  </script>
</div>
@endsection