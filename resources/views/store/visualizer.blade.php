@extends('layouts.store')

@section('content')
<style>
.palaz-visualizer-page{background:#f8f6f3;min-height:100vh;padding:42px 0 80px}
.palaz-visualizer-page .px-wrap{width:min(1180px,calc(100% - 32px));margin:0 auto}
.palaz-visualizer-page .px-kicker{font-size:10px;font-weight:900;letter-spacing:.12em;color:#a51f32}
.palaz-visualizer-page .px-title{margin:8px 0 12px;color:#25282c;line-height:1.25}
.palaz-visualizer-page .px-sub{color:#777;line-height:2;font-size:13px}
.palaz-visualizer-page .px-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 17px;border-radius:12px;text-decoration:none;font:inherit;font-size:12px;font-weight:800}
.palaz-visualizer-page .px-btn.red{background:#a51f32;color:#fff}
.palaz-visualizer-page .px-btn.soft{background:#fff;color:#333;border:1px solid #ddd8d3}
.palaz-visualizer-page .px-page-head{display:flex;align-items:flex-end;justify-content:space-between;gap:24px;margin-bottom:24px}
.palaz-visualizer-page .px-page-head-copy{max-width:700px}
.palaz-visualizer-page .px-page-head h1{font-size:clamp(30px,4vw,52px);margin:8px 0 10px}

.palaz-visualizer-page .px-tool-shell{display:grid;grid-template-columns:1.15fr .85fr;min-height:410px;border-radius:28px;overflow:hidden;background:#f3f1ee}
.palaz-visualizer-page .px-tool-image{position:relative;overflow:hidden;min-height:560px}
.palaz-visualizer-page .px-tool-image:after{content:"";position:absolute;inset:0;background:linear-gradient(90deg,rgba(0,0,0,.05),rgba(0,0,0,.32))}
.palaz-visualizer-page .px-tool-copy{padding:46px 42px;display:flex;flex-direction:column;justify-content:center}
.palaz-visualizer-page .px-tool-copy .px-title{font-size:clamp(28px,3.5vw,42px)}
.palaz-visualizer-page .px-pills{display:flex;flex-wrap:wrap;gap:8px;margin:20px 0 24px}
.palaz-visualizer-page .px-pill{border:1px solid #dedbd6;background:#fff;border-radius:999px;padding:10px 14px;font-size:12px;font-weight:800;color:#555}
.palaz-visualizer-page .px-pill.active{border-color:#b71929;color:#b71929;background:#fff7f8}
.palaz-visualizer-page .px-quick{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:22px}
.palaz-visualizer-page .px-quick a{padding:15px;border:1px solid #e7e4df;border-radius:16px;background:#fff;text-decoration:none;color:#292c30}

.palaz-visualizer-page .px-visualizer-surface-tabs{display:flex;gap:7px;flex-wrap:wrap;margin:18px 0 12px}
.palaz-visualizer-page .px-surface-tab{border:1px solid #dedbd6;background:#fff;border-radius:999px;padding:9px 14px;font:inherit;font-size:11px;font-weight:800;color:#555;cursor:pointer}
.palaz-visualizer-page .px-surface-tab.active{background:#fff4f5;border-color:#b71929;color:#b71929}
.palaz-visualizer-page .px-visualizer-products{display:none!important;gap:8px;overflow-x:auto;padding:4px 1px 8px;scrollbar-width:thin;min-height:66px}
.palaz-visualizer-page .px-visualizer-products.is-ready{display:none!important}
.palaz-visualizer-page .px-product-chip{display:flex;align-items:center;gap:8px;min-width:180px;max-width:220px;padding:7px;border:1px solid #e5e0dc;border-radius:15px;background:#fff;color:#292c30;text-align:right;cursor:pointer;flex:0 0 auto}
.palaz-visualizer-page .px-product-chip.active{border-color:#b71929;box-shadow:0 5px 18px rgba(183,25,41,.12)}
.palaz-visualizer-page .px-product-chip.compared{box-shadow:inset 0 0 0 1px rgba(183,25,41,.16)}
.palaz-visualizer-page .px-product-chip img{width:42px;height:42px;border-radius:10px;object-fit:cover;flex:0 0 42px}
.palaz-visualizer-page .px-product-chip span{min-width:0;display:block}
.palaz-visualizer-page .px-product-chip b,.palaz-visualizer-page .px-product-chip small{display:block;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.palaz-visualizer-page .px-product-chip b{font-size:11px}
.palaz-visualizer-page .px-product-chip small{margin-top:3px;color:#999;font-size:9px}
.palaz-visualizer-page .px-product-chip i{font-style:normal;color:#b71929;font-size:16px;margin-right:auto}
.palaz-visualizer-page .px-product-loading{padding:13px 2px;color:#888;font-size:10px}
.palaz-visualizer-page .px-visualizer-actions{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
.palaz-visualizer-page .px-visualizer-actions .px-btn{border:0;cursor:pointer}
.palaz-visualizer-page .px-visualizer-compare{position:absolute;z-index:5;right:16px;bottom:16px;display:flex;gap:6px;padding:6px;border-radius:13px;background:rgba(255,255,255,.9);box-shadow:0 8px 25px rgba(0,0,0,.16)}
.palaz-visualizer-page .px-visualizer-compare button{border:1px solid #e2ddd9;border-radius:9px;background:#fff;padding:7px 9px;font:inherit;font-size:9px;font-weight:800;color:#555;cursor:pointer}
.palaz-visualizer-page .px-visualizer-compare button.active{border-color:#b71929;color:#b71929}.palaz-visualizer-page .px-visualizer-compare .px-compare-open{background:#25282c;color:#fff;border-color:#25282c}
.palaz-visualizer-page .px-compare-modal{position:fixed;inset:0;z-index:9999;display:grid;place-items:center;padding:20px}
.palaz-visualizer-page .px-compare-modal[hidden]{display:none}
.palaz-visualizer-page .px-compare-backdrop{position:absolute;inset:0;background:rgba(16,17,19,.62);backdrop-filter:blur(5px)}
.palaz-visualizer-page .px-compare-dialog{position:relative;width:min(1080px,100%);max-height:min(88vh,820px);overflow:auto;border-radius:26px;background:#f7f5f2;box-shadow:0 30px 90px rgba(0,0,0,.3);padding:22px}
.palaz-visualizer-page .px-compare-header{display:flex;align-items:center;justify-content:space-between;gap:20px;margin-bottom:16px}
.palaz-visualizer-page .px-compare-header strong{display:block;margin-top:5px;font-size:18px;color:#24272b}
.palaz-visualizer-page .px-compare-close{width:38px;height:38px;border:1px solid #ddd8d3;border-radius:50%;background:#fff;font-size:24px;line-height:1;cursor:pointer}
.palaz-visualizer-page .px-compare-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px}
.palaz-visualizer-page .px-compare-view{position:relative;min-height:420px;overflow:hidden;border-radius:20px;background:#ddd}
.palaz-visualizer-page .px-compare-image{position:absolute;inset:0;background-position:center;background-size:cover}
.palaz-visualizer-page .px-compare-image:after{content:"";position:absolute;left:7%;right:7%;bottom:7%;height:52%;background-image:var(--compare-texture);background-size:cover;background-position:center;mix-blend-mode:multiply;opacity:.82;clip-path:var(--palaz-floor-clip,polygon(4% 18%,96% 18%,100% 100%,0 100%))}
.palaz-visualizer-page .px-compare-label{position:absolute;z-index:3;right:14px;top:14px;padding:9px 12px;border-radius:999px;background:rgba(255,255,255,.92);font-size:11px;font-weight:900;color:#25282c;box-shadow:0 8px 25px rgba(0,0,0,.12)}
.palaz-visualizer-page .px-compare-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:14px;color:#777;font-size:10px}
@media(max-width:700px){.palaz-visualizer-page .px-compare-grid{grid-template-columns:1fr}.palaz-visualizer-page .px-compare-view{min-height:300px}.palaz-visualizer-page .px-compare-footer{flex-direction:column;align-items:stretch}.palaz-visualizer-page .px-compare-footer .px-btn{width:100%}}

.palaz-visualizer-page .px-visualizer-preview.has-product:after{transition:opacity .45s ease,filter .45s ease,transform .55s cubic-bezier(.2,.75,.25,1)}
.palaz-visualizer-page .px-visualizer-preview.has-product{transition:background-image .35s ease,box-shadow .45s ease}
.palaz-visualizer-page .px-visualizer-preview.visualizer-switching:after{animation:palazFloorReveal .6s cubic-bezier(.2,.75,.25,1)}
.palaz-visualizer-page .px-visualizer-preview.visualizer-switching{box-shadow:0 18px 55px rgba(0,0,0,.12)}
@keyframes palazFloorReveal{0%{opacity:.15;transform:scale(1.035);filter:saturate(.72) blur(.7px)}55%{opacity:var(--palaz-floor-opacity,.82);transform:scale(1.001);filter:saturate(var(--palaz-floor-saturation,.94)) contrast(1.02) blur(0)}100%{opacity:var(--palaz-floor-opacity,.82);transform:scale(1.002)}}
@media(prefers-reduced-motion:reduce){.palaz-visualizer-page .px-visualizer-preview.has-product:after,.palaz-visualizer-page .px-visualizer-preview.visualizer-switching:after{animation:none;transition:none}}.palaz-visualizer-page .px-visualizer-preview.has-product:after{opacity:var(--palaz-floor-opacity,.78)!important;mix-blend-mode:var(--palaz-floor-blend,multiply);filter:saturate(var(--palaz-floor-saturation,.94)) contrast(1.02);clip-path:var(--palaz-floor-clip,polygon(4% 18%,96% 18%,100% 100%,0 100%));transform:scale(1.002);transform-origin:center}
.palaz-visualizer-page .px-visualizer-preview.has-product:before{content:"";position:absolute;inset:0;z-index:1;pointer-events:none;background:linear-gradient(135deg,rgba(255,255,255,.10),transparent 38%,rgba(0,0,0,.08));opacity:.72}
.palaz-visualizer-page .px-visualizer-preview.has-product:after{content:"";position:absolute;z-index:2;left:11%;right:11%;bottom:10%;height:48%;background-image:var(--palaz-texture);background-size:cover;background-position:center;mix-blend-mode:multiply;opacity:.82;clip-path:var(--palaz-floor-clip,polygon(4% 18%,96% 18%,100% 100%,0 100%));pointer-events:none;box-shadow:0 -10px 35px rgba(0,0,0,.08) inset}
@media(max-width:900px){.palaz-visualizer-page .px-product-chip{min-width:165px}.palaz-visualizer-page .px-visualizer-preview.has-product:after{left:6%;right:6%;bottom:8%;height:48%}}
@media(max-width:560px){.palaz-visualizer-page .px-product-chip{min-width:155px}.palaz-visualizer-page .px-visualizer-actions{display:grid;grid-template-columns:1fr}.palaz-visualizer-page .px-visualizer-actions .px-btn{width:100%}}
.palaz-visualizer-page .px-visualizer-shell{position:relative}
.palaz-visualizer-page .px-visualizer-preview{display:flex;align-items:center;justify-content:center;background-image:url('https://palazonline.com/storage/uploads/005-1-2.jpg');background-position:center;background-size:cover;transition:background-image .25s ease}
.palaz-visualizer-page .px-preview-empty{position:relative;z-index:3;width:min(330px,calc(100% - 40px));padding:28px 24px;text-align:center;border:1px solid rgba(255,255,255,.55);border-radius:22px;background:rgba(255,255,255,.88);box-shadow:0 18px 45px rgba(0,0,0,.12);backdrop-filter:blur(8px)}
.palaz-visualizer-page .px-preview-empty>span{display:grid;place-items:center;width:42px;height:42px;margin:0 auto 12px;border-radius:50%;background:#b71929;color:#fff;font-size:25px}
.palaz-visualizer-page .px-preview-empty strong{display:block;color:#25282c;font-size:17px;margin-bottom:5px}
.palaz-visualizer-page .px-preview-empty small{display:block;color:#777;line-height:1.8;margin-bottom:16px}
.palaz-visualizer-page .px-upload-btn{display:inline-flex;align-items:center;justify-content:center;min-height:42px;padding:0 18px;border-radius:12px;background:#25282c;color:#fff;font-weight:800;font-size:12px;cursor:pointer}
.palaz-visualizer-page .px-preview-badge{position:absolute;z-index:4;left:18px;top:18px;padding:7px 10px;border-radius:999px;background:rgba(0,0,0,.52);color:#fff;font-size:9px;font-weight:900;letter-spacing:.12em}
.palaz-visualizer-page .px-visualizer-pills button{font:inherit;cursor:pointer}
.palaz-visualizer-page .px-visualizer-status{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin:0 0 20px;padding:11px 13px;border:1px solid #e8e4df;border-radius:13px;background:#fff}
.palaz-visualizer-page .px-visualizer-status>span{width:8px;height:8px;border-radius:50%;background:#b71929}
.palaz-visualizer-page .px-visualizer-status b{font-size:12px;color:#333}
.palaz-visualizer-page .px-visualizer-status small{width:100%;padding-right:16px;color:#888;font-size:10px}
@media(max-width:900px){.palaz-visualizer-page .px-visualizer-preview{min-height:320px}}
.palaz-visualizer-page .px-quick b{display:block;margin-bottom:4px}
.palaz-visualizer-page .px-quick span{font-size:11px;color:#888}
.palaz-visualizer-page .px-connected{padding:8px 0 58px}
.palaz-visualizer-page .px-connected-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.palaz-visualizer-page .px-connected-card{min-height:250px;border-radius:24px;padding:30px;position:relative;overflow:hidden;background:#202327;color:#fff}
.palaz-visualizer-page .px-tool-shell{grid-template-columns:1.15fr .85fr}
@media(max-width:900px){.palaz-visualizer-page .px-tool-shell{grid-template-columns:1fr}.palaz-visualizer-page .px-page-head{display:block}.palaz-visualizer-page .px-page-head .px-btn{margin-top:16px}}

.palaz-visualizer-page{position:relative;overflow:hidden}
.palaz-visualizer-page:before{content:"";position:absolute;top:0;right:-180px;width:520px;height:520px;border-radius:50%;background:rgba(183,25,41,.055);pointer-events:none}
.palaz-visualizer-page:after{content:"VISUALIZER";position:absolute;top:245px;left:-70px;transform:rotate(-90deg);font-size:74px;font-weight:900;letter-spacing:.18em;color:rgba(37,40,44,.035);pointer-events:none}
.palaz-visualizer-page .px-page-head{position:relative;z-index:2}
.palaz-visualizer-page .px-tool-shell{position:relative;z-index:2;border:1px solid rgba(215,210,204,.75);box-shadow:0 28px 80px rgba(37,40,44,.10)}
.palaz-visualizer-page .px-tool-image{background:#d8d2cb}
.palaz-visualizer-page .px-tool-image:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,rgba(0,0,0,.04),rgba(0,0,0,.08) 48%,rgba(0,0,0,.38));z-index:1;pointer-events:none}
.palaz-visualizer-page .px-preview-empty{z-index:5;align-self:flex-end;margin-bottom:30px;width:min(390px,calc(100% - 48px));padding:18px 20px;text-align:right;display:grid;grid-template-columns:44px 1fr auto;align-items:center;gap:12px;border-radius:18px;background:rgba(255,255,255,.91);backdrop-filter:blur(14px);box-shadow:0 18px 45px rgba(0,0,0,.20)}
.palaz-visualizer-page .px-preview-empty>span{margin:0;width:44px;height:44px}
.palaz-visualizer-page .px-preview-empty strong{font-size:14px;margin:0}
.palaz-visualizer-page .px-preview-empty small{font-size:9px;margin:3px 0 0;line-height:1.7}
.palaz-visualizer-page .px-upload-btn{min-height:38px;padding:0 13px;font-size:10px;white-space:nowrap}
.palaz-visualizer-page .px-sample-note{position:absolute;z-index:5;top:22px;right:22px;display:flex;flex-direction:column;gap:2px;padding:10px 13px;border-radius:12px;background:rgba(25,26,28,.72);color:#fff;backdrop-filter:blur(10px);direction:rtl}
.palaz-visualizer-page .px-sample-note b{font-size:10px}
.palaz-visualizer-page .px-sample-note span{font-size:8px;color:rgba(255,255,255,.72)}
.palaz-visualizer-page .px-tool-copy{background:linear-gradient(145deg,#fff 0%,#f7f4f0 100%);position:relative}
.palaz-visualizer-page .px-tool-copy:before{content:"";position:absolute;top:42px;right:0;width:3px;height:88px;background:#b71929;border-radius:4px 0 0 4px}
.palaz-visualizer-page .px-tool-copy>*{position:relative}
.palaz-visualizer-page .px-quick a{transition:transform .2s ease,box-shadow .2s ease,border-color .2s ease}
.palaz-visualizer-page .px-quick a:hover{transform:translateY(-3px);box-shadow:0 12px 28px rgba(37,40,44,.08);border-color:#d4cec7}
@media(max-width:900px){
  .palaz-visualizer-page:after{display:none}
  .palaz-visualizer-page .px-tool-image{min-height:430px}
}
@media(max-width:560px){
  .palaz-visualizer-page .px-preview-empty{grid-template-columns:38px 1fr;gap:9px;margin-bottom:18px;width:calc(100% - 28px)}
  .palaz-visualizer-page .px-preview-empty>span{width:38px;height:38px}
  .palaz-visualizer-page .px-upload-btn{grid-column:1/-1;width:100%}
  .palaz-visualizer-page .px-sample-note{top:12px;right:12px}
}
.palaz-visualizer-page .px-visualizer-kicker{display:flex;align-items:center;gap:12px;margin-bottom:4px}.palaz-visualizer-page .px-visualizer-kicker-image{width:54px;height:54px;flex:0 0 54px;border-radius:14px;background:url('https://palazonline.com/storage/uploads/005-1-2.jpg') center/cover no-repeat;box-shadow:0 8px 22px rgba(37,40,44,.14);border:3px solid #fff}.palaz-visualizer-page .px-visualizer-kicker .px-kicker{margin:0}@media(max-width:560px){.palaz-visualizer-page .px-visualizer-kicker-image{width:46px;height:46px;flex-basis:46px;border-radius:12px}} </style>
<div class="palaz-reference-home palaz-experience palaz-visualizer-page">
  <div class="px-wrap">
    <header class="px-page-head">
      <div class="px-page-head-copy">
        <span class="px-kicker">PALAZ / VISUALIZER</span>
        <h1>فضای خودت را واقعاً ببین.</h1>
        <p class="px-sub">عکس فضای خودت را وارد کن، کف را مشخص کن و مدل‌های واقعی موکت، لمینیت و SPC را روی همان فضا امتحان کن.</p>
      </div>
      <a class="px-btn soft" href="{{ route('home') }}">بازگشت به صفحه اصلی ←</a>
    </header>
      <main class="px-tools">
        <div class="px-wrap">
          <div class="px-tool-shell px-visualizer-shell">
            <div class="px-tool-image px-visualizer-preview" role="img" aria-label="پیش‌نمایش فضای انتخابی">
              <div class="px-preview-empty">
                <span>⌂</span>
                <strong>فضای نمونه پالاز</strong>
                <small>برای شروع، این فضای آماده را ببین یا عکس فضای خودت را وارد کن.</small>
                <label class="px-upload-btn">عکس فضای خودم<input id="px-space-upload" type="file" accept="image/jpeg,image/png,image/webp" hidden></label>
              </div>
              <div class="px-sample-note"><b>فضای الهام‌بخش</b><span>یک نمونه واقعی برای شروع Visualizer</span></div>
              <div class="px-preview-badge">PALAZ VISUALIZER</div>
              <div class="px-visualizer-compare" hidden>
                <button type="button" data-compare="0"></button>
                <button type="button" data-compare="1"></button>
                <button type="button" class="px-compare-open" aria-label="مقایسه دو مدل">مقایسه</button>
              </div>

              <div class="px-compare-modal" hidden aria-hidden="true">
                <div class="px-compare-backdrop"></div>
                <section class="px-compare-dialog" role="dialog" aria-modal="true" aria-labelledby="px-compare-title">
                  <header class="px-compare-header">
                    <div><span class="px-kicker">PRODUCT COMPARISON</span><strong id="px-compare-title">دو مدل را روی فضای خودت مقایسه کن</strong></div>
                    <button type="button" class="px-compare-close" aria-label="بستن">×</button>
                  </header>
                  <div class="px-compare-grid">
                    <article class="px-compare-view" data-compare-view="0">
                      <div class="px-compare-image"></div>
                      <div class="px-compare-label"></div>
                    </article>
                    <article class="px-compare-view" data-compare-view="1">
                      <div class="px-compare-image"></div>
                      <div class="px-compare-label"></div>
                    </article>
                  </div>
                  <footer class="px-compare-footer">
                    <span>هر مدل را انتخاب کن تا در Visualizer اصلی هم نمایش داده شود.</span>
                    <button type="button" class="px-btn red px-compare-use">استفاده از مدل انتخاب‌شده ←</button>
                  </footer>
                </section>
              </div>
            </div>

            <div class="px-tool-copy">
              <div class="px-visualizer-kicker"><div class="px-visualizer-kicker-image"></div><span class="px-kicker">02 / VISUALIZER</span></div>
              <h2 class="px-title">فضای خودت را<br>واقعاً ببین.</h2>
              <p class="px-sub">عکس فضای خودت را وارد کن، کف را مشخص کن و مدل‌های واقعی موکت، لمینیت و SPC را روی همان فضا امتحان کن.</p>

              <div class="px-visualizer-surface-tabs" role="tablist" aria-label="نوع کفپوش">
                <button type="button" class="px-surface-tab active" data-surface="carpet">موکت</button>
                <button type="button" class="px-surface-tab" data-surface="laminate">لمینیت</button>
                <button type="button" class="px-surface-tab" data-surface="spc">SPC</button>
              </div>

              <div class="px-visualizer-status">
                <span></span><b>عکس فضا را اضافه کن</b>
                <small>بعد از انتخاب عکس، مدل‌های واقعی کاتالوگ پالاز برای همان نوع کف نمایش داده می‌شوند.</small>
              </div>

              <div class="px-visualizer-products" aria-live="polite"></div>

              <div class="px-visualizer-actions">
                <button class="px-btn red px-visualizer-advisor" type="button">مشاوره با AI Advisor ←</button>
                <a class="px-btn soft" href="{{ route('services',['type'=>'design']) }}">ادامه طراحی ←</a>
              </div>

              <div class="px-quick">
                <a href="{{ route('shop') }}"><b>🧩 مشاهده محصول</b><span>مدل انتخاب‌شده را در فروشگاه ببین.</span></a>
                <a href="{{ route('services',['type'=>'measurement']) }}"><b>⌗ درخواست اندازه‌گیری</b><span>اگر آماده اجرا هستی، اندازه‌گیری را ثبت کن.</span></a>
              </div>
            </div>
          </div>
        </div>

        <script>
          (() => {
            const root = document.querySelector('.palaz-visualizer-page');
            if (!root) return;

            const preview = root.querySelector('.px-visualizer-preview');
            const empty = root.querySelector('.px-preview-empty');
            const upload = root.querySelector('#px-space-upload');
            const tabs = [...root.querySelectorAll('.px-surface-tab')];
            const productsEl = root.querySelector('.px-visualizer-products');
            productsEl.classList.remove('is-ready');
            const status = root.querySelector('.px-visualizer-status');
            const compare = root.querySelector('.px-visualizer-compare');
            const compareOpen = root.querySelector('.px-compare-open');
            const compareModal = root.querySelector('.px-compare-modal');
            const compareClose = root.querySelector('.px-compare-close');
            const compareUse = root.querySelector('.px-compare-use');
            const advisorButton = root.querySelector('.px-visualizer-advisor');

            const fallbackImages = {
              carpet: 'https://palazonline.com/storage/uploads/005-1-2.jpg',
              laminate: 'https://palazonline.com/storage/uploads/IMG_1100-4.PNG',
              spc: 'https://palazonline.com/storage/uploads/IMG_5777.PNG'
            };

            let surface = 'carpet';
            let uploadedUrl = '';
            let selectedProduct = null;
            let products = [];
            let compareProducts = [];
            let floorPolygon = null;

            window.palazVisualizerState = () => ({
              surface,
              space_analyzed: Array.isArray(floorPolygon) && floorPolygon.length >= 4,
              product: selectedProduct ? {
                id: selectedProduct.id,
                name: selectedProduct.name,
                tone: selectedProduct.tone || ''
              } : null,
              compare: compareProducts.slice(0, 2).map(item => ({
                id: item.id,
                name: item.name,
                tone: item.tone || ''
              }))
            });

            const imageUrl = value => {
              if (!value) return fallbackImages[surface];
              if (/^https?:\/\//i.test(value) || value.startsWith('data:') || value.startsWith('blob:')) return value;
              return value.startsWith('/') ? value : '/storage/' + value.replace(/^storage\//, '');
            };

            const setStatus = (title, detail) => {
              status.querySelector('b').textContent = title;
              status.querySelector('small').textContent = detail;
            };

            const paintPreview = () => {
              const base = uploadedUrl || fallbackImages[surface];
              const texture = selectedProduct ? imageUrl(selectedProduct.image) : null;
              const floorPoints = Array.isArray(floorPolygon) && floorPolygon.length >= 4
                ? floorPolygon.map(point => [(Number(point[0]) || 0), (Number(point[1]) || 0)])
                : null;

              if (texture) {
                // نسخه اول Visualizer: عکس کاربر حفظ می‌شود و تکسچر محصول
                // با ماسک نرم روی ناحیه کف قرار می‌گیرد. موتور تشخیص دقیق
                // گوشه‌های کف در مرحله Vision به همین API متصل خواهد شد.
                preview.style.backgroundImage =
                  'linear-gradient(rgba(20,20,20,.04),rgba(20,20,20,.04)),url("' + base + '")';
                preview.dataset.texture = texture;
                preview.style.setProperty('--palaz-texture', 'url("' + texture + '")');
                if (floorPoints) {
                  const points = floorPoints.map(point => point[0] + '% ' + point[1] + '%').join(', ');
                  preview.style.setProperty('--palaz-floor-clip', 'polygon(' + points + ')');
                  preview.style.setProperty('--palaz-floor-opacity', '0.82');
                  preview.style.setProperty('--palaz-floor-blend', surface === 'carpet' ? 'multiply' : 'soft-light');
                  preview.style.setProperty('--palaz-floor-saturation', surface === 'carpet' ? '0.94' : '0.88');
                } else {
                  preview.style.removeProperty('--palaz-floor-clip');
                  preview.style.removeProperty('--palaz-floor-opacity');
                  preview.style.removeProperty('--palaz-floor-blend');
                  preview.style.removeProperty('--palaz-floor-saturation');
                }
                preview.classList.add('has-product');
              } else {
                preview.style.backgroundImage =
                  'linear-gradient(rgba(0,0,0,.04),rgba(0,0,0,.18)),url("' + base + '")';
                preview.classList.remove('has-product');
                preview.style.removeProperty('--palaz-texture');
                preview.style.removeProperty('--palaz-floor-clip');
              }

              if (selectedProduct) {
                setStatus(
                  selectedProduct.name + ' روی فضای شما',
                  'برای مقایسه، یک مدل دیگر را انتخاب کن.'
                );
              } else if (uploadedUrl) {
                setStatus('عکس شما آماده است', 'حالا یک مدل واقعی از کاتالوگ پالاز انتخاب کن.');
              } else {
                setStatus('عکس فضا را اضافه کن', 'بعد از انتخاب عکس، مدل‌های واقعی کاتالوگ پالاز نمایش داده می‌شوند.');
              }
            };

            const renderProducts = () => {
              if (!products.length) {
                productsEl.innerHTML = '<div class="px-product-loading">برای این دسته هنوز مدلی در کاتالوگ ثبت نشده است.</div>';
                return;
              }

              productsEl.innerHTML = products.map((product, index) => {
                const active = selectedProduct?.id === product.id;
                const compared = compareProducts.some(item => item.id === product.id);
                return '<button type="button" class="px-product-chip' + (active ? ' active' : '') + (compared ? ' compared' : '') + '" data-product-index="' + index + '">' +
                  '<img src="' + imageUrl(product.image) + '" alt="" loading="lazy">' +
                  '<span><b>' + product.name + '</b><small>' + (product.tone || 'مدل پالاز') + '</small></span>' +
                  '<i>' + (compared ? '✓' : '＋') + '</i>' +
                '</button>';
              }).join('');

              productsEl.querySelectorAll('[data-product-index]').forEach(button => {
                button.addEventListener('click', () => {
                  const product = products[Number(button.dataset.productIndex)];
                  if (!product) return;

                  const previousProductId = selectedProduct?.id ?? null;
                  selectedProduct = product;
                  preview.classList.remove('visualizer-switching');
                  if (previousProductId !== product.id && uploadedUrl && floorPolygon) {
                    void preview.offsetWidth;
                    preview.classList.add('visualizer-switching');
                    window.setTimeout(() => preview.classList.remove('visualizer-switching'), 700);
                  }
                  if (!compareProducts.some(item => item.id === product.id)) {
                    compareProducts = [product, ...compareProducts].slice(0, 2);
                  }
                  renderProducts();
                  renderCompare();
                  paintPreview();
                });
              });
            };

            const renderCompare = () => {
              if (compareProducts.length < 2) {
                compare.hidden = true;
                if (compareModal) compareModal.hidden = true;
                return;
              }

              compare.hidden = false;
              compare.querySelectorAll('button').forEach((button, index) => {
                const product = compareProducts[index];
                button.textContent = product ? product.name : '';
                button.hidden = !product;
                button.classList.toggle('active', product?.id === selectedProduct?.id);
                button.onclick = () => {
                  if (product) {
                    selectedProduct = product;
                    paintPreview();
                    renderProducts();
                    renderCompare();
                  }
                };
              });
            };

            const renderCompareModal = () => {
              if (!compareModal || compareProducts.length < 2) return;
              const base = uploadedUrl || fallbackImages[surface];
              const points = Array.isArray(floorPolygon) && floorPolygon.length >= 4
                ? floorPolygon.map(point => (Number(point[0]) || 0) + '% ' + (Number(point[1]) || 0) + '%').join(', ')
                : '4% 18%,96% 18%,100% 100%,0 100%';
              compareModal.querySelectorAll('[data-compare-view]').forEach((view, index) => {
                const product = compareProducts[index];
                const image = view.querySelector('.px-compare-image');
                const label = view.querySelector('.px-compare-label');
                if (!product) return;
                image.style.backgroundImage = 'linear-gradient(rgba(0,0,0,.04),rgba(0,0,0,.18)),url("' + base + '")';
                image.style.setProperty('--compare-texture', 'url("' + imageUrl(product.image) + '")');
                image.style.setProperty('--palaz-floor-clip', 'polygon(' + points + ')');
                label.textContent = product.name + (product.tone ? ' • ' + product.tone : '');
                view.onclick = () => {
                  selectedProduct = product;
                  paintPreview();
                  renderProducts();
                  renderCompare();
                  renderCompareModal();
                };
              });
            };

            const openCompare = () => {
              if (compareProducts.length < 2) return;
              renderCompareModal();
              compareModal.hidden = false;
              compareModal.setAttribute('aria-hidden','false');
              document.body.style.overflow = 'hidden';
            };
            const closeCompare = () => {
              if (!compareModal) return;
              compareModal.hidden = true;
              compareModal.setAttribute('aria-hidden','true');
              document.body.style.overflow = '';
            };

            compareOpen?.addEventListener('click', openCompare);
            compareClose?.addEventListener('click', closeCompare);
            compareModal?.querySelector('.px-compare-backdrop')?.addEventListener('click', closeCompare);
            document.addEventListener('keydown', event => {
              if (event.key === 'Escape' && compareModal && !compareModal.hidden) closeCompare();
            });
            compareUse?.addEventListener('click', () => {
              closeCompare();
              root.scrollIntoView({ behavior:'smooth', block:'center' });
            });

            const loadProducts = async () => {
              productsEl.classList.add('is-ready');
              productsEl.innerHTML = '<div class="px-product-loading">در حال دریافت مدل‌های واقعی پالاز…</div>';
              try {
                const response = await fetch('{{ route('visualizer.products') }}?category=' + encodeURIComponent(surface), {
                  headers: { 'Accept': 'application/json' }
                });
                if (!response.ok) throw new Error('products_failed');
                const data = await response.json();
                products = Array.isArray(data.products) ? data.products : [];
                selectedProduct = null;
                compareProducts = [];
                renderProducts();
                renderCompare();
                paintPreview();
              } catch (error) {
                productsEl.innerHTML = '<div class="px-product-loading">دریافت مدل‌ها انجام نشد. دوباره تلاش کن.</div>';
              }
            };

            tabs.forEach(tab => tab.addEventListener('click', () => {
              surface = tab.dataset.surface || 'carpet';
              tabs.forEach(item => item.classList.toggle('active', item === tab));
              loadProducts();
            }));

            upload?.addEventListener('change', event => {
              const file = event.target.files?.[0];
              if (!file) return;

              if (!['image/jpeg','image/png','image/webp'].includes(file.type)) {
                setStatus('فرمت عکس مناسب نیست', 'فقط JPG، PNG یا WEBP انتخاب کن.');
                return;
              }

              if (file.size > 8 * 1024 * 1024) {
                setStatus('حجم عکس زیاد است', 'برای عملکرد بهتر، عکس زیر ۸ مگابایت انتخاب کن.');
                return;
              }

              if (uploadedUrl) URL.revokeObjectURL(uploadedUrl);
              uploadedUrl = URL.createObjectURL(file);
              empty.style.display = 'none';
              floorPolygon = null;
              setStatus('در حال دیدن فضای شما…', 'هوش مصنوعی در حال تشخیص محدوده کف است.');
              const formData = new FormData();
              formData.append('image', file);
              fetch('{{ route('advisor.analyze-space') }}', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: formData
              }).then(response => response.ok ? response.json() : Promise.reject(new Error('vision_failed')))
                .then(data => {
                  if (Array.isArray(data.floor_polygon) && data.floor_polygon.length >= 4) {
                    floorPolygon = data.floor_polygon;
                    setStatus('کف فضا تشخیص داده شد', data.message || 'حالا یک مدل واقعی انتخاب کن.');
                  } else {
                    setStatus('عکس فضا آماده است', data.message || 'یک مدل انتخاب کن؛ نمایش اولیه ادامه پیدا می‌کند.');
                  }
                  paintPreview();
                  loadProducts();
                })
                .catch(() => {
                  setStatus('عکس فضا آماده است', 'تشخیص خودکار کف در دسترس نبود؛ نمایش اولیه ادامه پیدا می‌کند.');
                  paintPreview();
                });
              selectedProduct = null;
              compareProducts = [];
              renderCompare();
              renderProducts();
              paintPreview();
            });

            advisorButton?.addEventListener('click', () => {
              const opener = document.querySelector('.palaz-advisor-shortcut, .px-open-advisor');
              opener?.click();
            });

            loadProducts();
          })();
        </script>
  </div>
</div>
@endsection
