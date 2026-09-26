@extends('layouts.store')
@section('title','فروشگاه | PALAZ ONLINE')
@section('content')
@php
$shopImages=['carpet'=>'https://palazonline.com/storage/uploads/005-1-2.jpg','laminate'=>'https://palazonline.com/storage/uploads/IMG_1100-4.PNG','spc'=>'https://palazonline.com/storage/uploads/IMG_5777.PNG','wallpaper'=>'https://palazonline.com/storage/uploads/IMG_5796.jpg','tile'=>'https://palazonline.com/storage/uploads/4.jpg','grass'=>'https://palazonline.com/storage/uploads/IMG_5795.PNG'];
@endphp
<section class="page-head shop-head"><div class="container"><div><span class="eyebrow">PALAZ ONLINE / COLLECTION</span><h1>فروشگاه</h1><p>محصول را انتخاب کنید؛ برای اندازه‌گیری، طراحی یا نصب هم می‌توانید مسیر پروژه را ادامه دهید.</p></div><div class="shop-head-mark">P<span>ONLINE</span></div></div></section>
<section class="section shop-section"><div class="container">
<div class="shop-toolbar"><div><strong>{{ count($products) }} محصول</strong>@if(request('q'))<span class="result-query">نتیجه جستجو برای «{{ request('q') }}»</span>@endif</div><label class="shop-sort">مرتب‌سازی <select id="shopSort" aria-label="مرتب‌سازی محصولات"><option value="default">پیش‌فرض</option><option value="name">نام محصول</option></select></label></div>
<div class="shop-layout">
<aside class="shop-filter"><div class="filter-title"><span>FILTER</span><strong>فیلتر محصولات</strong></div><div class="filter-group"><b>دسته‌بندی</b><a class="{{ !$category?'selected':'' }}" href="{{ route('shop') }}">همه محصولات <span>↗</span></a>@foreach(['carpet'=>'موکت','laminate'=>'لمینیت','spc'=>'SPC','wallpaper'=>'کاغذدیواری','tile'=>'موکت تایل','grass'=>'چمن مصنوعی'] as $key=>$label)<a class="{{ $category===$key?'selected':'' }}" href="{{ route('shop',['category'=>$key]) }}">{{ $label }} <span>↗</span></a>@endforeach</div><div class="filter-group"><b>خدمات همراه</b><label><input type="checkbox"> نیاز به اندازه‌گیری</label><label><input type="checkbox"> نیاز به نصب</label><label><input type="checkbox"> مشاوره خرید</label></div><a class="filter-service" href="{{ route('services') }}">پروژه دارید؟<strong>مسیر خدمات را ببینید ←</strong></a></aside>
<main><div class="category-chips"><a class="{{ !$category?'selected':'' }}" href="{{ route('shop') }}">همه</a>@foreach(['carpet'=>'موکت','laminate'=>'لمینیت','spc'=>'SPC','wallpaper'=>'کاغذدیواری','tile'=>'موکت تایل','grass'=>'چمن مصنوعی'] as $key=>$label)<a class="{{ $category===$key?'selected':'' }}" href="{{ route('shop',['category'=>$key]) }}">{{ $label }}</a>@endforeach</div>
<div class="product-grid shop-grid" id="shopProducts">
@forelse($products as $index=>$product)
@php $image=$shopImages[$product['category']] ?? $shopImages['carpet']; @endphp
<article class="product-card shop-product" data-product-name="{{ $product['name'] }}" data-product-index="{{ $index }}">
<a href="{{ route('product',$product['id']) }}">
<div class="product-visual product-{{ $product['category'] }} {{ $product['tone'] }}" style="background-image:linear-gradient(180deg,#00000008,#00000035),url('{{ $image }}');background-size:cover;background-position:center;"><span>{{ sprintf('%02d',$index+1) }}</span><b>PALAZ</b><i>مشاهده سریع</i><span class="product-hover-line"></span></div>
<div class="product-info"><div class="product-meta"><small>{{ strtoupper($product['category']) }} / COLLECTION</small><small>PALAZ</small></div><h3>{{ $product['name'] }}</h3><p>{{ $product['unit'] }}</p><strong>مشاهده جزئیات <i>←</i></strong></div>
</a></article>
@empty<div class="empty">محصولی با این مشخصات پیدا نشد.</div>@endforelse
</div></main></div></div></section>
<section class="shop-service-strip"><div class="container"><span class="eyebrow">NEED HELP?</span><strong>برای انتخاب متراژ، مدل یا خدمات اجرا مطمئن نیستید؟</strong><a href="{{ route('services') }}">مشاوره تخصصی ←</a></div></section>
@endsection