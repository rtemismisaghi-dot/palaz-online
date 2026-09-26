@extends('layouts.store')
@section('title','PALAZ ONLINE | خانه')
@section('content')
<div class="desktop-home-v2" dir="rtl">
  <section class="v2-hero">
    <div class="v2-hero-image"></div>
    <div class="v2-hero-panel">
      <span class="v2-eyebrow">PALAZ ONLINE / HOME COLLECTION</span>
      <h1>فضای خانه‌ات را<br><b>از اینجا شروع کن</b></h1>
      <p>محصولات پالاز را انتخاب کن، فضای خودت را طراحی کن و اجرای حرفه‌ای را به ما بسپار.</p>
      <div class="v2-actions">
        <a href="{{ route('shop') }}">مشاهده محصولات <span>←</span></a>
        <a href="{{ route('services',['type'=>'measurement']) }}">درخواست اندازه‌گیری</a>
      </div>
      <div class="v2-proof"><span><b>01</b> انتخاب</span><span><b>02</b> اندازه‌گیری</span><span><b>03</b> اجرا</span></div>
    </div>
  </section>

  @php
    $v2Images = [
      'carpet'=>'https://palazonline.com/storage/uploads/005-1-2.jpg',
      'laminate'=>'https://palazonline.com/storage/uploads/IMG_1100-4.PNG',
      'spc'=>'https://palazonline.com/storage/uploads/IMG_5777.PNG',
      'wallpaper'=>'https://palazonline.com/storage/uploads/IMG_5796.jpg',
      'tile'=>'https://palazonline.com/storage/uploads/4.jpg',
      'grass'=>'https://palazonline.com/storage/uploads/IMG_5795.PNG'
    ];
  @endphp

  <section class="v2-collections">
    <div class="v2-heading"><div><small>01 / COLLECTIONS</small><h2>دسته‌بندی محصولات</h2></div><a href="{{ route('shop') }}">مشاهده همه محصولات ←</a></div>
    <div class="v2-collection-grid">
      @foreach($categories as $slug=>$category)
        <a href="{{ route('shop',['category'=>$slug]) }}" class="v2-collection">
          <img src="{{ $v2Images[$slug] ?? $v2Images['carpet'] }}" alt="{{ $category['title'] }}">
          <div><small>{{ str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }}</small><strong>{{ $category['title'] }}</strong><span>مشاهده ←</span></div>
        </a>
      @endforeach
    </div>
  </section>

  <section class="v2-feature">
    <div class="v2-feature-copy"><small>PALAZ / SELECTED</small><h2>انتخابی برای<br><b>فضای بهتر زندگی</b></h2><p>مجموعه‌ای از محصولات منتخب پالاز برای کف، دیوار و فضای سبز.</p><a href="{{ route('shop') }}">ورود به فروشگاه ←</a></div>
    <div class="v2-feature-products">
      @foreach($products as $i=>$product)
        @if($i<4)
        @php $image=$v2Images[$product['category']] ?? $v2Images['carpet']; @endphp
        <article class="v2-product">
          <a href="{{ route('product',$product['id']) }}" class="v2-product-img"><img src="{{ $image }}" alt="{{ $product['name'] }}"><span>♡</span></a>
          <div class="v2-product-info"><small>{{ $product['category'] }}</small><h3>{{ $product['name'] }}</h3><p>{{ $product['unit'] }}</p><strong>{{ $product['price'] ? number_format($product['price']).' تومان' : 'تماس بگیرید' }}</strong></div>
        </article>
        @endif
      @endforeach
    </div>
  </section>

  <section class="v2-services">
    <div class="v2-heading"><div><small>02 / SERVICES</small><h2>از انتخاب تا اجرا</h2></div><a href="{{ route('services') }}">همه خدمات ←</a></div>
    <div class="v2-service-grid">
      <a href="{{ route('services',['type'=>'measurement']) }}"><b>01</b><span>⌗</span><h3>اندازه‌گیری دقیق</h3><p>اندازه‌گیری حرفه‌ای با مسیر هوشمند DTZ.</p><i>شروع درخواست ←</i></a>
      <a href="{{ route('services',['type'=>'design']) }}"><b>02</b><span>▤</span><h3>طراحی و محاسبه</h3><p>طراحی فضا و محاسبه متراژ پیش از اجرا.</p><i>مشاهده سرویس ←</i></a>
      <a href="{{ route('services',['type'=>'installation']) }}"><b>03</b><span>⌁</span><h3>نصب حرفه‌ای</h3><p>اجرای نهایی با تیم تخصصی و هماهنگ.</p><i>درخواست نصب ←</i></a>
    </div>
  </section>

  <section class="v2-studio">
    <div class="v2-studio-image"></div>
    <div class="v2-studio-copy"><small>PALAZ DESIGN STUDIO</small><h2>کوراسیون فقط انتخاب محصول نیست؛<br><b>ساختن فضای شماست.</b></h2><p>با پالاز، انتخاب، اندازه‌گیری، طراحی و اجرا در یک مسیر به هم متصل می‌شوند.</p><a href="{{ route('services',['type'=>'design']) }}">طراحی فضای من ←</a></div>
  </section>

  <section class="v2-benefits">
    <div><b>✓</b><strong>اصالت کالا</strong><small>تضمین کیفیت و اصالت</small></div>
    <div><b>⌗</b><strong>اندازه‌گیری دقیق</strong><small>با تیم متخصص</small></div>
    <div><b>⌁</b><strong>نصب حرفه‌ای</strong><small>اجرای مطمئن</small></div>
    <div><b>♧</b><strong>مشاوره تخصصی</strong><small>همراه شما</small></div>
    <div><b>◈</b><strong>پشتیبانی</strong><small>در تمام مسیر</small></div>
  </section>
</div>

<div class="palaz-home" dir="rtl">
<section class="ph-hero">
    <div class="ph-hero-bg" aria-hidden="true"></div>
    <div class="container ph-hero-inner">
        <div class="ph-hero-tools">
            <a href="{{ route('shop') }}" class="ph-tool"><span class="ph-tool-icon">▣</span><span><b>خرید محصول</b><small>موکت، لمینیت، کاغذدیواری و...</small></span><i>‹</i></a>
            <a href="{{ route('services',['type'=>'measurement']) }}" class="ph-tool active"><span class="ph-tool-icon">⌗</span><span><b>درخواست اندازه‌گیری</b><small>با DTZ</small></span><i>‹</i></a>
            <a href="{{ route('services',['type'=>'installation']) }}" class="ph-tool"><span class="ph-tool-icon">⌁</span><span><b>درخواست نصب</b><small>با DTZ</small></span><i>‹</i></a>
            <a href="{{ route('services',['type'=>'design']) }}" class="ph-tool"><span class="ph-tool-icon">▤</span><span><b>طراحی و محاسبه</b><small>DTZ Tablet</small></span><i>‹</i></a>
        </div>
        <div class="ph-hero-copy">
            <span class="desktop-kicker">PALAZ / INTERIOR COLLECTION 2026</span>
            <span>PALAZ ONLINE</span>
            <h1 class="desktop-hero-title">فضای خانه‌ات را<br><em>از اینجا شروع کن</em></h1>
            <h1 class="mobile-hero-title">انتخابی مطمئن<br>برای <em>فضای بهتر زندگی</em></h1>
            <p class="desktop-hero-copy">موکت، لمینیت، کاغذ دیواری و ...<br>انتخاب کن، فضای خودت را ببین و سفارش بده.</p>
            <p class="mobile-hero-copy">از خرید محصول تا اجرای کامل، همه چیز در یک مسیر با کیفیت، سریع و مطمئن.</p>
            <div class="desktop-hero-actions"><a class="ph-primary-btn" href="{{ route('shop') }}">مشاهده محصولات <b>‹</b></a><a class="desktop-hero-secondary" href="{{ route('services') }}">خدمات اجرا <b>←</b></a></div><div class="desktop-hero-meta"><div><b>01</b><span>انتخاب محصول</span></div><div><b>02</b><span>اندازه‌گیری</span></div><div><b>03</b><span>اجرای حرفه‌ای</span></div></div>
        </div>
    </div>
    <div class="ph-slider-dots"><i></i><i class="on"></i><i></i></div>
</section>

<section class="ph-categories"><div class="desktop-section-label"><span>01 / COLLECTIONS</span><strong>برای هر فضا، یک انتخاب</strong></div>
    <div class="container ph-category-row">
        <a class="ph-all-cat" href="{{ route('shop') }}"><span>‹</span><b>همه محصولات</b></a>
        @php
            $catImages = [
                'carpet'=>'https://palazonline.com/storage/uploads/005-1-2.jpg',
                'laminate'=>'https://palazonline.com/storage/uploads/IMG_1100-4.PNG',
                'spc'=>'https://palazonline.com/storage/uploads/IMG_5777.PNG',
                'wallpaper'=>'https://palazonline.com/storage/uploads/IMG_5796.jpg',
                'tile'=>'https://palazonline.com/storage/uploads/4.jpg',
                'grass'=>'https://palazonline.com/storage/uploads/IMG_5795.PNG',
            ];
        @endphp
        @foreach($categories as $slug=>$category)
            <a class="ph-cat cat-{{ $slug }}" href="{{ route('shop',['category'=>$slug]) }}">
                <img src="{{ $catImages[$slug] ?? $catImages['carpet'] }}" alt="{{ $category['title'] }}" loading="lazy">
                <strong>{{ $category['title'] }}</strong>
            </a>
        @endforeach
        <a class="ph-cat ph-cat-extra" href="{{ route('shop') }}"><img src="https://palazonline.com/storage/uploads/010-1.jpg" alt="سایر محصولات" loading="lazy"><strong>سایر محصولات</strong></a>
    </div>
</section>

<section class="ph-services" id="journey">
    <div class="container">
        <div class="ph-section-intro">
            <span>خدمات هوشمند پالاز</span>
            <h2>از انتخاب تا اجرا، با خیال راحت</h2>
            <p>در تمام مراحل همراه شما هستیم؛ از مشاوره و بازدید تا نصب و پشتیبانی.</p>
            <a href="{{ route('services') }}" class="ph-outline-btn">بیشتر بدانید <b>‹</b></a>
        </div>
        <div class="ph-service-cards">
            <a href="{{ route('services',['type'=>'measurement']) }}" class="ph-service-card">
                <img src="https://palazonline.com/storage/uploads/IMG_5777.PNG" alt="اندازه‌گیری" loading="lazy">
                <span class="ph-round-icon">⌗</span><div><h3>اندازه‌گیری</h3><small>با DTZ</small><b>مشاهده جزئیات ←</b></div>
            </a>
            <a href="{{ route('services',['type'=>'installation']) }}" class="ph-service-card">
                <img src="https://palazonline.com/storage/uploads/IMG_1100-4.PNG" alt="نصب" loading="lazy">
                <span class="ph-round-icon">⌁</span><div><h3>نصب</h3><small>با DTZ</small><b>مشاهده جزئیات ←</b></div>
            </a>
            <a href="{{ route('services',['type'=>'design']) }}" class="ph-service-card">
                <img src="https://palazonline.com/storage/uploads/010-1.jpg" alt="طراحی و محاسبه" loading="lazy">
                <span class="ph-round-icon">▤</span><div><h3>طراحی و محاسبه</h3><small>DTZ Tablet</small><b>مشاهده جزئیات ←</b></div>
            </a>
        </div>
    </div>
</section>

<section class="ph-products"><div class="desktop-section-label"><span>02 / PRODUCTS</span><strong>انتخاب‌های منتخب پالاز</strong></div>
    <div class="container">
        <div class="ph-section-head"><div><span>محصولات منتخب</span><h2>پرفروش‌ترین‌ها با بهترین قیمت</h2></div><a href="{{ route('shop') }}">مشاهده همه <b>‹</b></a></div>
        <div class="ph-product-grid">
            @foreach($products as $i=>$product)
                @php $image = $catImages[$product['category']] ?? $catImages['carpet']; @endphp
                <article class="ph-product">
                    <a href="{{ route('product',$product['id']) }}" class="ph-product-image"><img src="{{ $image }}" alt="{{ $product['name'] }}" loading="lazy"><button type="button" aria-label="علاقه‌مندی">♡</button></a>
                    <div class="ph-product-body"><small>{{ $product['category'] }}</small><a href="{{ route('product',$product['id']) }}"><h3>{{ $product['name'] }}</h3></a><p>{{ $product['unit'] }}</p><div class="ph-stars">★★★★★</div><div class="ph-product-bottom"><strong>{{ $product['price'] ? number_format($product['price']).' تومان' : 'تماس بگیرید' }}</strong><form method="POST" action="{{ route('cart.add',$product['id']) }}">@csrf<button type="submit">افزودن به سبد <span>🛒</span></button></form></div></div>
                </article>
            @endforeach
        </div>
    </div>
</section>

<section class="ph-promo"><div class="desktop-promo-tag">PALAZ DESIGN STUDIO</div>
    <div class="container ph-promo-inner">
        <div><span>الهام بگیرید</span><h2>کوراسیون، یعنی ساختن<br><em>فضای واقعی شما</em></h2><p>ایده‌هایی برای یک زندگی زیباتر، با انتخاب درست کفپوش و پوشش دیوار.</p><a href="{{ route('shop') }}" class="ph-white-btn">مشاهده گالری <b>‹</b></a></div>
    </div>
</section>

<section class="ph-trust">
    <div class="container">
        <div><b>◈</b><strong>اصالت کالا</strong><small>تضمین کیفیت و اصالت</small></div>
        <div><b>⌁</b><strong>نصب حرفه‌ای</strong><small>توسط تیم متخصص</small></div>
        <div><b>⌗</b><strong>اندازه‌گیری دقیق</strong><small>با اپراتورهای مجرب</small></div>
        <div><b>♧</b><strong>مشاوره تخصصی</strong><small>همیشه در کنار شما</small></div>
        <div><b>✓</b><strong>پشتیبانی دائمی</strong><small>همراه شما در مسیر</small></div>
    </div>
</section>
</div>
@endsection