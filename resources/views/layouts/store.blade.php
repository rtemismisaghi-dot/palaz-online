<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="PALAZ ONLINE — فروشگاه و خدمات تخصصی پالاز">
<title>@yield('title','PALAZ ONLINE')</title>
@vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
<div class="announcement"><span class="announcement-pulse"></span> مسیر خرید تا اجرا، یک تجربه واحد <span>•</span> مشاوره تخصصی پالاز</div>
<header class="site-header">
<div class="container header-main">
<a href="{{ route('home') }}" class="brand"><span class="brand-mark">P</span><span><strong>PALAZ</strong><small>ONLINE</small></span></a>
<form class="search" action="{{ route('shop') }}"><span>⌕</span><input name="q" value="{{ request('q') }}" placeholder="جستجوی محصول، مدل، رنگ یا کد..." aria-label="جستجو"><button type="submit">جستجو</button></form>
<div class="header-actions"><a href="{{ route('services') }}" class="consult-link">مشاوره</a><a href="{{ route('cart') }}" class="cart">سبد خرید <b>{{ count(session('cart', [])) }}</b></a><button class="mobile-menu" type="button" aria-label="منو">☰</button></div>
</div>
<nav class="main-nav"><div class="container nav-inner">
<a href="{{ route('home') }}" class="{{ request()->routeIs('home')?'active':'' }}">خانه</a>
<a href="{{ route('shop') }}" class="{{ request()->routeIs('shop')?'active':'' }}">فروشگاه</a>
<div class="nav-dropdown"><span>کفپوش‌ها⌄</span><div class="mega"><a href="{{ route('shop',['category'=>'carpet']) }}"><b>موکت</b><small>مجموعه موکت‌های پالاز</small></a><a href="{{ route('shop',['category'=>'laminate']) }}"><b>لمینیت</b><small>طرح‌های چوبی و کلاسیک</small></a><a href="{{ route('shop',['category'=>'spc']) }}"><b>SPC</b><small>کفپوش‌های مقاوم</small></a><a href="{{ route('shop',['category'=>'tile']) }}"><b>موکت تایل</b><small>برای فضاهای مدرن</small></a></div></div>
<a href="{{ route('shop',['category'=>'wallpaper']) }}">کاغذدیواری</a><a href="{{ route('shop',['category'=>'grass']) }}">چمن مصنوعی</a><a href="{{ route('services') }}">خدمات</a><a href="{{ route('home') }}#journey">مسیر اجرای پروژه</a>
</div></nav>
</header>
@if(session('success'))<div class="flash success">{{ session('success') }}</div>@endif
@if(session('service_success'))<div class="flash success">{{ session('service_success') }}</div>@endif
@yield('content')
<footer class="footer"><div class="container footer-grid">
<div><div class="footer-brand">PALAZ ONLINE</div><p>فروش آنلاین محصولات، مشاوره و خدمات اجرای پالاز؛ از انتخاب تا اجرا.</p><div class="footer-social"><span>Instagram</span><span>Support</span></div></div>
<div><h4>فروشگاه</h4><a href="{{ route('shop',['category'=>'carpet']) }}">موکت</a><a href="{{ route('shop',['category'=>'laminate']) }}">لمینیت</a><a href="{{ route('shop',['category'=>'spc']) }}">SPC</a><a href="{{ route('shop',['category'=>'wallpaper']) }}">کاغذدیواری</a></div>
<div><h4>خدمات</h4><a href="{{ route('services',['type'=>'measurement']) }}">اندازه‌گیری</a><a href="{{ route('services',['type'=>'installation']) }}">نصب</a><a href="{{ route('services',['type'=>'design']) }}">طراحی و محاسبه</a></div>
<div><h4>پشتیبانی</h4><a href="{{ route('cart') }}">سبد خرید</a><a href="{{ route('services') }}">درخواست خدمات</a><a href="{{ route('home') }}">خانه</a></div>
</div><div class="container footer-bottom"><span>© {{ date('Y') }} PALAZ ONLINE</span><span>ساخته‌شده برای یک مسیر متصل</span></div></footer>
</body></html>