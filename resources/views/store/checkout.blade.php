@extends('layouts.store')
@section('title','ثبت سفارش | PALAZ ONLINE')
@section('content')
<section class="page-head checkout-head"><div class="container"><span class="eyebrow">PALAZ ONLINE / CHECKOUT</span><h1>ثبت سفارش</h1><p>اطلاعات تحویل، خدمات موردنیاز و روش پرداخت را مشخص کنید.</p></div></section>
<section class="section checkout-section"><div class="container checkout-layout">
<form class="checkout-form" method="POST" action="{{ route('checkout.place') }}">@csrf
<div class="checkout-block"><span class="eyebrow">01 / CUSTOMER</span><h2>اطلاعات مشتری</h2><div class="form-two"><label>نام و نام خانوادگی<input name="name" required></label><label>شماره تماس<input name="phone" required inputmode="tel"></label></div></div>
<div class="checkout-block"><span class="eyebrow">02 / DELIVERY</span><h2>آدرس تحویل</h2><div class="form-two"><label>شهر<input name="city" required></label><label>کد پستی<input name="postal_code" inputmode="numeric"></label></div><label>آدرس کامل<textarea name="address" rows="4" required></textarea></label></div>
<div class="checkout-block"><span class="eyebrow">03 / SERVICES</span><h2>خدمات پروژه</h2><div class="checkout-options"><label><input type="radio" name="service" value="none" checked><span><b>بدون خدمات</b><small>فقط خرید محصول</small></span></label><label><input type="radio" name="service" value="measurement"><span><b>اندازه‌گیری</b><small>ارسال درخواست به DTZ</small></span></label><label><input type="radio" name="service" value="installation"><span><b>نصب</b><small>ارسال درخواست به DTZ</small></span></label><label><input type="radio" name="service" value="design"><span><b>طراحی و محاسبه</b><small>ورود به DTZ Tablet</small></span></label></div></div>
<div class="checkout-block"><span class="eyebrow">04 / PAYMENT</span><h2>روش پرداخت</h2><label class="payment-choice"><input type="radio" name="payment" value="offline" checked><span><b>پرداخت پس از تأیید سفارش</b><small>در این نسخه، درگاه بانکی هنوز متصل نشده است.</small></span></label><button class="btn btn-primary wide" type="submit">ثبت سفارش و دریافت کد پیگیری ←</button></div>
</form>
<aside class="checkout-summary"><span class="eyebrow">YOUR ORDER</span><h2>انتخاب‌های شما</h2>@foreach($items as $item)<div class="checkout-item"><span>{{ $item['name'] }}</span><small>{{ $item['unit'] }}</small></div>@endforeach<div class="summary-line total"><span>مبلغ نهایی</span><b>پس از تأیید محاسبه می‌شود</b></div><a href="{{ route('cart') }}">← بازگشت به سبد خرید</a></aside>
</div></section>
@endsection