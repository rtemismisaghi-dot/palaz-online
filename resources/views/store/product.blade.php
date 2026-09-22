@extends('layouts.store')
@section('title',$product['name'].' | PALAZ ONLINE')
@section('content')
<section class="product-detail"><div class="container detail-grid"><div class="detail-visual {{ $product['tone'] }}"><span>PALAZ</span><small>PALAZ COLLECTION</small></div><div class="detail-copy"><span class="eyebrow">{{ strtoupper($product['category']) }}</span><h1>{{ $product['name'] }}</h1><p class="lead">{{ $product['description'] }}</p><div class="detail-box"><div><small>قیمت</small><strong>{{ $product['unit'] }}</strong></div><div><small>خدمات</small><strong>اندازه‌گیری / نصب / طراحی</strong></div></div><form method="POST" action="{{ route('cart.add',$product['id']) }}">@csrf<button class="btn btn-primary wide">افزودن به سبد خرید</button></form><a class="service-link" href="{{ route('services') }}">برای انتخاب دقیق، مشاوره بگیرید ←</a></div></div></section>
<section class="section compact"><div class="container feature-strip"><div><b>01</b><span>مشاوره تخصصی</span></div><div><b>02</b><span>اندازه‌گیری</span></div><div><b>03</b><span>محاسبه پروژه</span></div><div><b>04</b><span>نصب حرفه‌ای</span></div></div></section>
@endsection
