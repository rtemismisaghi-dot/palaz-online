@extends('layouts.store')
@section('title','PALAZ ONLINE | صفحه اصلی')
@section('content')
<div class="palaz-reference-home" dir="rtl">

  <header class="ref-header">
    <div class="ref-topbar">
      <div class="ref-wrap">
        <span>☎ 021-12345678</span>
        <span>⌖ تهران، جردن، خیابان پالاز</span>
        <b>پشتیبانی ۲۴ ساعته ◔</b>
      </div>
    </div>
    <div class="ref-head-main ref-wrap">
      <button class="ref-mobile-btn" type="button" aria-label="منو">☰</button>
      <a href="{{ route('home') }}" class="ref-logo"><img src="{{ asset('images/palaz-original-logo.png') }}" alt="PALAZ ONLINE"></a>
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
      <div class="ref-dots"><b></b><b class="on"></b><b></b></div>
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
        <div class="ref-category-grid">
          <a class="ref-category all" href="{{ route('shop') }}"><span>⌘</span><strong>همه محصولات</strong><b>‹</b></a>
          @foreach($categories as $slug=>$category)
            <a class="ref-category" href="{{ route('shop',['category'=>$slug]) }}">
              <img src="{{ $refImages[$slug] ?? $refImages['carpet'] }}" alt="{{ $category['title'] }}" loading="lazy">
              <div><strong>{{ $category['title'] }}</strong><b>‹</b></div>
            </a>
          @endforeach
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
          <a href="{{ route('services',['type'=>'design']) }}"><img src="https://palazonline.com/storage/uploads/010-1.jpg" alt="طراحی"><i>▤</i><h3>طراحی و محاسبه</h3><small>DTZ Tablet</small><b>مشاهده جزئیات ←</b></a>\n          <a class="ref-service-mobile-only" href="{{ route('services') }}"><img src="https://palazonline.com/storage/uploads/IMG_5796.jpg" alt="مشاوره و پشتیبانی"><i>♧</i><h3>مشاوره و پشتیبانی</h3><small>همیشه در کنار شما</small><b>مشاهده جزئیات ←</b></a>
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
              @if($i < 5)
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