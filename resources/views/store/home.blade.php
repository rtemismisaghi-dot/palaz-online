@extends('layouts.store')
@section('title','PALAZ ONLINE | صفحه اصلی')
@section('content')
<style>
@media (min-width:1101px){
  .palaz-reference-home .ref-nav{
    position:relative!important;
    width:min(980px,calc(100% - 180px))!important;
    margin:10px auto 14px!important;
    padding:0!important;
    background:rgba(255,255,255,.46)!important;
    border:1px solid rgba(255,255,255,.78)!important;
    border-radius:22px!important;
    box-shadow:0 10px 32px rgba(20,25,30,.10),inset 0 1px 0 rgba(255,255,255,.9),inset 0 -1px 0 rgba(255,255,255,.35)!important;
    backdrop-filter:blur(22px) saturate(150%)!important;
    -webkit-backdrop-filter:blur(22px) saturate(150%)!important;
    overflow:hidden!important;
  }
  .palaz-reference-home .ref-nav:before{
    content:""!important;
    position:absolute!important;
    inset:0!important;
    background:linear-gradient(180deg,rgba(255,255,255,.28),rgba(255,255,255,.08))!important;
    pointer-events:none!important;
  }
  .palaz-reference-home .ref-nav .ref-wrap{
    position:relative!important;
    z-index:1!important;
    width:100%!important;
    height:58px!important;
    min-height:58px!important;
    padding:7px 10px!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    gap:3px!important;
    background:transparent!important;
    border:0!important;
  }
  .palaz-reference-home .ref-nav a{
    height:42px!important;
    min-height:42px!important;
    padding:0 17px!important;
    display:flex!important;
    align-items:center!important;
    justify-content:center!important;
    border:1px solid transparent!important;
    border-radius:14px!important;
    background:transparent!important;
    color:#30343a!important;
    font-size:11px!important;
    font-weight:600!important;
    transition:all .22s ease!important;
  }
  .palaz-reference-home .ref-nav a:hover{
    color:#b91e2d!important;
    background:rgba(255,255,255,.48)!important;
    border-color:rgba(255,255,255,.72)!important;
    box-shadow:0 5px 16px rgba(20,25,30,.07)!important;
    transform:translateY(-1px)!important;
  }
  .palaz-reference-home .ref-nav a.active{
    color:#fff!important;
    background:linear-gradient(135deg,#df2433,#bd1827)!important;
    border-color:rgba(255,255,255,.65)!important;
    box-shadow:0 6px 18px rgba(189,24,39,.22)!important;
  }
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
      <form class="ref-search" action="{{ route('shop') }}">
        <input name="q" value="{{ request('q') }}" placeholder="جستجوی محصول، دسته‌بندی یا برند...">
        <button type="submit">⌕</button>
      </form>
      <div class="ref-actions">
        <a href="{{ route('services') }}"><i>♙</i><span>حساب کاربری</span></a>
        <a href="{{ route('home') }}#favorite"><i>♡</i><span>علاقه‌مندی‌ها</span></a>
        <a href="{{ route('cart') }}" class="ref-cart"><i>🛒</i><span>سبد خرید</span><b>{{ count(session('cart', [])) }}</b></a>
      </div>
      <a href="{{ route('cart') }}" class="ref-mobile-cart">🛒<b>{{ count(session('cart', [])) }}</b></a>
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
          <small>PALAZ ONLINE</small>
          <h1>انتخاب مطمئن<br><em>برای فضای بهتر زندگی</em></h1>
          <p>محصولات باکیفیت، خدمات حرفه‌ای و اجرای تخصصی؛<br>همه چیز در یک مسیر با کیفیت، سریع و مطمئن.</p>
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
          <a class="ref-category" href="{{ route('shop',['category'=>'spc']) }}"><img src="{{ $refImages['spc'] }}" alt="SPC"><div><strong>SPC</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'wallpaper']) }}"><img src="{{ $refImages['wallpaper'] }}" alt="کاغذ دیواری"><div><strong>کاغذ دیواری</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'tile']) }}"><img src="{{ $refImages['tile'] }}" alt="موکت تایل"><div><strong>موکت تایل</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'grass']) }}"><img src="{{ $refImages['grass'] }}" alt="چمن مصنوعی"><div><strong>چمن مصنوعی</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'parquet']) }}"><img src="{{ $refImages['laminate'] }}" alt="پارکت"><div><strong>پارکت</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'carpet-tile']) }}"><img src="{{ $refImages['tile'] }}" alt="کفپوش ورزشی"><div><strong>کفپوش ورزشی</strong><b>‹</b></div></a>
          <a class="ref-category" href="{{ route('shop',['category'=>'decorative']) }}"><img src="{{ $refImages['wallpaper'] }}" alt="دکوراسیون"><div><strong>دکوراسیون</strong><b>‹</b></div></a>
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
</div>
@endsection