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
            <img class="brand-logo" src="data:image/png;base64,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" alt="پالاز">
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
