<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="PALAZ ONLINE — فروش آنلاین محصولات و خدمات پالاز">
    <title>PALAZ ONLINE</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<header class="site-header">
    <div class="announcement"><span class="announcement-pulse"></span> ارسال و مشاوره تخصصی پالاز در سراسر ایران</div>

    <div class="container header-main">
        <button class="mobile-menu" type="button" aria-label="باز کردن منو" aria-expanded="false">☰</button>

        <a href="{{ route('home') }}" class="brand brand-real" aria-label="پالاز آنلاین">
            <img class="brand-logo" src="{{ asset("images/palaz-logo.png") }}" alt="">
            <span class="brand-wordmark"><strong>PALAZ</strong><small>ONLINE</small></span>
        </a>

        <div class="search search-real">
            <span aria-hidden="true">⌕</span>
            <input type="search" placeholder="جستجوی محصول، مدل یا رنگ..." aria-label="جستجوی محصول">
            <button type="button" aria-label="جستجو">جستجو</button>
        </div>

        <nav class="header-actions" aria-label="دسترسی سریع">
            <a href="#" class="header-action"><span class="action-icon">♙</span><span>حساب من</span></a>
            <a href="#" class="header-action"><span class="action-icon">♡</span><span>علاقه‌مندی</span></a>
            <a href="{{ route('cart') }}" class="header-action cart"><span class="action-icon">🛒</span><span>سبد خرید</span><b>0</b></a>
        </nav>
    </div>

    <nav class="main-nav" aria-label="منوی اصلی">
        <div class="container nav-inner">
            <a class="active" href="{{ route('home') }}">خانه</a>
            <a href="{{ route('shop') }}">فروشگاه</a>
            <div class="nav-dropdown">
                <span>محصولات <small>⌄</small></span>
                <div class="mega" aria-label="دسته‌بندی محصولات">
                    <a href="{{ route('shop') }}"><b>موکت</b><small>کالکشن‌های موکت پالاز</small></a>
                    <a href="{{ route('shop') }}"><b>لمینیت</b><small>کفپوش‌های لمینیت</small></a>
                    <a href="{{ route('shop') }}"><b>SPC</b><small>کفپوش مدرن و مقاوم</small></a>
                    <a href="{{ route('shop') }}"><b>کاغذدیواری</b><small>طرح‌ها و رنگ‌های متنوع</small></a>
                    <a href="{{ route('shop') }}"><b>موکت تایل</b><small>راهکارهای مدولار</small></a>
                    <a href="{{ route('shop') }}"><b>چمن مصنوعی</b><small>برای فضای سبز</small></a>
                </div>
            </div>
            <a href="{{ route('services') }}">خدمات</a>
            <a href="#">طراحی فضای من</a>
            <a href="#">مجله پالاز</a>
            <a href="#">درباره پالاز</a>
        </div>
    </nav>
</header>

<main>
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">PALAZ ONLINE</span>
            <h1>خانه‌ای که<br><em>با انتخاب شما</em><br>ساخته می‌شود.</h1>
            <p>محصول، مشاوره، اندازه‌گیری و نصب؛ همه در یک مسیر ساده و حرفه‌ای.</p>
            <div class="hero-buttons"><a class="btn btn-primary" href="#">مشاهده محصولات</a><a class="btn btn-ghost" href="#">مشاوره خرید ←</a></div>
        </div>
        <div class="hero-art" aria-hidden="true"><div class="art-card art-one"></div><div class="art-card art-two"></div><div class="art-card art-three"></div><div class="art-caption">DESIGNED<br>FOR LIVING</div></div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow">EXPLORE</span><h2>انتخاب شما، شروع مسیر است</h2></div><a href="#">مشاهده همه ←</a></div>
        <div class="category-grid">
            @foreach(['موکت','لمینیت','SPC','کاغذدیواری','موکت تایل','چمن مصنوعی','پادری','خانه پالاز'] as $i => $category)
                <a class="category-card c{{ $i+1 }}" href="#"><span>{{ str_pad($i+1,2,'0',STR_PAD_LEFT) }}</span><strong>{{ $category }}</strong><b>مشاهده ←</b></a>
            @endforeach
        </div>
    </div>
</section>

<section class="service-section">
    <div class="container">
        <div class="section-head light"><div><span class="eyebrow">CONNECTED SERVICES</span><h2>از انتخاب تا اجرا، کنار شما هستیم</h2></div></div>
        <div class="service-grid">
            <a href="#" class="service-card"><span class="service-icon">⌖</span><div><h3>اندازه‌گیری دقیق</h3><p>درخواست اندازه‌گیری مستقیماً به DTZ ارسال می‌شود.</p><small>ارسال به DTZ ←</small></div></a>
            <a href="#" class="service-card"><span class="service-icon">⌂</span><div><h3>نصب حرفه‌ای</h3><p>هماهنگی نصب و پیگیری در مسیر خدمات پالاز.</p><small>ارسال به DTZ ←</small></div></a>
            <a href="#" class="service-card"><span class="service-icon">◇</span><div><h3>طراحی و محاسبه</h3><p>پلان و محاسبات پروژه در DTZ Tablet انجام می‌شود.</p><small>ورود به DTZ Tablet ←</small></div></a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow">SELECTED</span><h2>محصولات منتخب پالاز</h2></div><a href="#">فروشگاه ←</a></div>
        <div class="product-grid">
            @foreach(['موکت پالاز مدل آرتا','لمینیت پالاز مدل کلاسیک','کاغذدیواری پالاز','موکت تایل پالاز'] as $i => $product)
            <article class="product-card"><div class="product-visual pv{{ $i+1 }}"><span>PALAZ</span></div><div class="product-info"><small>PALAZ COLLECTION</small><h3>{{ $product }}</h3><p>انتخاب رنگ و مشخصات</p><strong>مشاهده محصول ←</strong></div></article>
            @endforeach
        </div>
    </div>
</section>
</main>

<footer class="footer"><div class="container footer-grid"><div><div class="footer-brand">PALAZ ONLINE</div><p>فروش آنلاین محصولات و خدمات پالاز.</p></div><div><h4>فروشگاه</h4><a href="#">موکت</a><a href="#">لمینیت</a><a href="#">SPC</a></div><div><h4>خدمات</h4><a href="#">مشاوره</a><a href="#">اندازه‌گیری</a><a href="#">نصب</a></div><div><h4>پشتیبانی</h4><a href="#">پیگیری سفارش</a><a href="#">تماس با ما</a><a href="#">سوالات متداول</a></div></div></footer>
</body>
</html>
