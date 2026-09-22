@extends('layouts.store')
@section('title','سبد خرید | PALAZ ONLINE')
@section('content')
<section class="page-head"><div class="container"><span class="eyebrow">PALAZ ONLINE / CART</span><h1>سبد خرید</h1></div></section>
<section class="section"><div class="container">@if($items->isEmpty())<div class="empty large">سبد خرید شما خالی است.<br><a class="btn btn-primary" href="{{ route('shop') }}">رفتن به فروشگاه</a></div>@else<div class="cart-list">@foreach($items as $item)<a class="cart-row" href="{{ route('product',$item['id']) }}"><div class="mini-visual {{ $item['tone'] }}">P</div><div><strong>{{ $item['name'] }}</strong><small>{{ $item['unit'] }}</small></div><span>مشاهده ←</span></a>@endforeach</div><div class="checkout-note">مرحله بعدی: آدرس، خدمات موردنیاز و پرداخت. این بخش در مرحله فروش واقعی به درگاه و مدیریت سفارش متصل می‌شود.</div>@endif</div></section>
@endsection
