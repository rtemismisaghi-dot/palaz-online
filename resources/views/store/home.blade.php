@extends('layouts.store')
@section('title','PALAZ ONLINE | خانه')
@section('content')
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
            <span>PALAZ ONLINE</span>
            <h1 class="desktop-hero-title">فضای خانه‌ات را<br><em>از اینجا شروع کن</em></h1>
            <h1 class="mobile-hero-title">انتخابی مطمئن<br>برای <em>فضای بهتر زندگی</em></h1>
            <p class="desktop-hero-copy">موکت، لمینیت، کاغذ دیواری و ...<br>انتخاب کن، فضای خودت را ببین و سفارش بده.</p>
            <p class="mobile-hero-copy">از خرید محصول تا اجرای کامل، همه چیز در یک مسیر با کیفیت، سریع و مطمئن.</p>
            <a class="ph-primary-btn" href="{{ route('shop') }}">مشاهده محصولات <b>‹</b></a>
        </div>
    </div>
    <div class="ph-slider-dots"><i></i><i class="on"></i><i></i></div>
</section>

<section class="ph-categories">
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

<section class="ph-products">
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

<section class="ph-promo">
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