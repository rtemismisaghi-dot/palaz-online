<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="PALAZ ONLINE — فروشگاه و خدمات تخصصی پالاز">
<title>@yield('title','PALAZ ONLINE')</title>
@vite(['resources/css/app.css','resources/js/app.js'])
<style id="palaz-header-rotator">
@media (min-width:701px){
.site-header.header-variant-1 .main-nav{background:transparent}
.site-header.header-variant-2 .main-nav{background:rgba(255,255,255,.55);backdrop-filter:blur(10px);-webkit-backdrop-filter:blur(10px)}
.site-header.header-variant-3 .main-nav{background:linear-gradient(90deg,rgba(255,255,255,.35),rgba(247,245,242,.78),rgba(255,255,255,.35))}
.site-header.header-variant-2 .nav-inner,.site-header.header-variant-3 .nav-inner{border-color:transparent}
.site-header.header-variant-2 .nav-inner{box-shadow:0 8px 24px rgba(20,20,20,.04)}
.site-header.header-variant-3 .nav-inner{box-shadow:inset 0 1px 0 rgba(255,255,255,.8),0 6px 20px rgba(20,20,20,.035)}
}
</style>

<script>
document.addEventListener('DOMContentLoaded',function(){
 const header=document.querySelector('.site-header');
 if(!header)return;
 let v=1;
 header.classList.add('header-variant-1');
 setInterval(function(){
   header.classList.remove('header-variant-1','header-variant-2','header-variant-3');
   v=v===3?1:v+1;
   header.classList.add('header-variant-'+v);
 },5000);
});
</script>

</head>
<body>
<div class="palaz-topbar"><div class="container"><span>☎ 021-12345678</span><span>⌖ تهران، جردن، خیابان پالاز</span><b>پشتیبانی ۲۴ ساعته ◔</b></div></div>
<header class="site-header">
<div class="container header-main">
<button class="mobile-menu" type="button" aria-label="منو" aria-expanded="false">☰</button>
<a href="{{ route('home') }}" class="brand-real" aria-label="PALAZ ONLINE"><img src="{{ asset('images/palaz-logo.png') }}?v=20260926" alt="" class="brand-logo"><span class="brand-wordmark"><strong>PALAZ</strong><small>ONLINE</small></span></a>
<form class="search search-real" action="{{ route('shop') }}"><span>⌕</span><input name="q" value="{{ request('q') }}" placeholder="جستجوی محصول، دسته یا برند..." aria-label="جستجو"><button type="submit">⌕</button></form>
<div class="header-actions">
<a href="{{ route('services') }}" class="header-action"><span class="action-icon">♙</span><span>حساب کاربری</span></a>
<a href="{{ route('home') }}#favorite" class="header-action favorite"><span class="action-icon">♡</span><span>علاقه‌مندی‌ها</span></a>
<a href="{{ route('cart') }}" class="header-action cart"><span class="action-icon">🛒</span><span>سبد خرید</span><b>{{ count(session('cart', [])) }}</b></a>
</div>
</div>
<nav class="main-nav"><div class="container nav-inner">
<a href="{{ route('home') }}" class="{{ request()->routeIs('home')?'active':'' }}">خانه</a>
<a href="{{ route('shop') }}" class="{{ request()->routeIs('shop')?'active':'' }}">فروشگاه</a>
<div class="nav-dropdown"><span>محصولات <small>⌄</small></span><div class="mega">
<a href="{{ route('shop',['category'=>'carpet']) }}"><b>موکت</b><small>مجموعه موکت‌های پالاز</small></a>
<a href="{{ route('shop',['category'=>'laminate']) }}"><b>لمینیت</b><small>طرح‌های چوبی و کلاسیک</small></a>
<a href="{{ route('shop',['category'=>'spc']) }}"><b>SPC</b><small>کفپوش‌های مقاوم</small></a>
<a href="{{ route('shop',['category'=>'wallpaper']) }}"><b>کاغذدیواری</b><small>طرح‌ها و رنگ‌های متنوع</small></a>
<a href="{{ route('shop',['category'=>'tile']) }}"><b>موکت تایل</b><small>راهکارهای مدولار</small></a>
<a href="{{ route('shop',['category'=>'grass']) }}"><b>چمن مصنوعی</b><small>برای فضای سبز</small></a>
</div></div>
<a href="{{ route('services') }}">خدمات</a><a href="{{ route('home') }}#journey">طراحی فضای من</a><a href="{{ route('home') }}">مجله پالاز</a><a href="{{ route('home') }}">درباره پالاز</a>
</div></nav>
</header>
@if(session('success'))<div class="flash success">{{ session('success') }}</div>@endif
@if(session('service_success'))<div class="flash success">{{ session('service_success') }}</div>@endif
@yield('content')
<footer class="footer"><div class="container footer-grid">
<div><div class="footer-brand">PALAZ <span>ONLINE</span></div><p>پالاز؛ همراه مطمئن شما در انتخاب، خرید و اجرای پوشش‌های فضای زندگی.</p><div class="footer-social">◎　◉　in　◌</div></div>
<div><h4>فروشگاه</h4><a href="{{ route('shop',['category'=>'carpet']) }}">موکت</a><a href="{{ route('shop',['category'=>'laminate']) }}">لمینیت</a><a href="{{ route('shop',['category'=>'spc']) }}">SPC</a><a href="{{ route('shop',['category'=>'wallpaper']) }}">کاغذدیواری</a></div>
<div><h4>خدمات حرفه‌ای</h4><a href="{{ route('services',['type'=>'measurement']) }}">اندازه‌گیری</a><a href="{{ route('services',['type'=>'installation']) }}">نصب حرفه‌ای</a><a href="{{ route('services',['type'=>'design']) }}">طراحی و محاسبه</a><a href="{{ route('services') }}">مشاوره تخصصی</a></div>
<div><h4>راهنمای مشتری</h4><a href="{{ route('cart') }}">سبد خرید</a><a href="{{ route('services') }}">پیگیری خدمات</a><a href="{{ route('home') }}">درباره پالاز</a><a href="{{ route('home') }}">تماس با ما</a></div>
<div class="footer-news"><h4>عضویت در خبرنامه</h4><p>از جدیدترین محصولات و پیشنهادها باخبر شوید.</p><form><input placeholder="ایمیل خود را وارد کنید"><button>→</button></form></div>
</div><div class="container footer-bottom"><span>© {{ date('Y') }} PALAZ ONLINE. All rights reserved.</span><span>طراحی و توسعه برای یک تجربه متصل</span></div></footer>
</body></html>