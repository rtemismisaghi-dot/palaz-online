<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="PALAZ ONLINE — فروشگاه و خدمات تخصصی پالاز">
<title>@yield('title','PALAZ ONLINE')</title>
@vite(['resources/css/app.css','resources/js/app.js'])
</head>
<body>
<div class="announcement">ارسال و مشاوره تخصصی پالاز در سراسر ایران <span>•</span> مسیر خرید تا اجرا در یک تجربه واحد</div>
<header class="site-header">
<div class="container header-main">
<a href="{{ route('home') }}" class="brand"><span class="brand-mark">P</span><span><strong>PALAZ</strong><small>ONLINE</small></span></a>
<form class="search" action="{{ route('shop') }}"><span>⌕</span><input name="q" value="{{ request('q') }}" placeholder="جستجوی محصول، مدل یا رنگ..." aria-label="جستجو"></form>
<div class="header-actions"><a href="{{ route('services') }}">خدمات</a><a href="{{ route('cart') }}" class="cart">سبد خرید <b>{{ count(session('cart', [])) }}</b></a></div>
</div>
<nav class="main-nav"><div class="container nav-inner">
<a href="{{ route('home') }}" class="{{ request()->routeIs('home')?'active':'' }}">خانه</a>
<a href="{{ route('shop') }}" class="{{ request()->routeIs('shop')?'active':'' }}">فروشگاه</a>
@foreach(['carpet'=>'موکت','laminate'=>'لمینیت','spc'=>'SPC','wallpaper'=>'کاغذدیواری','tile'=>'موکت تایل','grass'=>'چمن مصنوعی'] as $key=>$label)
<a href="{{ route('shop',['category'=>$key]) }}">{{ $label }}</a>
@endforeach
<a href="{{ route('services') }}" class="{{ request()->routeIs('services')?'active':'' }}">خدمات</a>
</div></nav>
</header>
@if(session('success'))<div class="flash success">{{ session('success') }}</div>@endif
@if(session('service_success'))<div class="flash success">{{ session('service_success') }}</div>@endif
@yield('content')
<footer class="footer"><div class="container footer-grid">
<div><div class="footer-brand">PALAZ ONLINE</div><p>فروش آنلاین محصولات، مشاوره و خدمات اجرای پالاز.</p></div>
<div><h4>فروشگاه</h4><a href="{{ route('shop',['category'=>'carpet']) }}">موکت</a><a href="{{ route('shop',['category'=>'laminate']) }}">لمینیت</a><a href="{{ route('shop',['category'=>'spc']) }}">SPC</a></div>
<div><h4>خدمات</h4><a href="{{ route('services') }}">مشاوره</a><a href="{{ route('services',['type'=>'measurement']) }}">اندازه‌گیری</a><a href="{{ route('services',['type'=>'installation']) }}">نصب</a></div>
<div><h4>پشتیبانی</h4><a href="{{ route('cart') }}">سبد خرید</a><a href="{{ route('services') }}">درخواست خدمات</a><a href="{{ route('home') }}">بازگشت به خانه</a></div>
</div><div class="container footer-bottom">© {{ date('Y') }} PALAZ ONLINE</div></footer>
</body></html>
