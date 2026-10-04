@extends('layouts.store')
@section('title','سبد خرید | PALAZ ONLINE')
@section('content')
<section class="page-head cart-head"><div class="container"><span class="eyebrow">PALAZ ONLINE / CART</span><h1>سبد خرید</h1><p>انتخاب‌های شما در یک مسیر؛ خدمات اجرا را هم می‌توانید به پروژه اضافه کنید.</p></div></section>
<section class="section cart-section"><div class="container">@if($items->isEmpty())
<div class="empty large cart-empty"><span>◌</span><h2>سبد خرید خالی است</h2><p>هنوز محصولی انتخاب نکرده‌اید. مجموعه پالاز را ببینید و مسیرتان را شروع کنید.</p><a class="btn btn-primary" href="{{ route('shop') }}">رفتن به فروشگاه <b>←</b></a></div>
@else
@php $cartImages=['carpet'=>'https://palazonline.com/storage/uploads/005-1-2.jpg','laminate'=>'https://palazonline.com/storage/uploads/IMG_1100-4.PNG','spc'=>'https://palazonline.com/storage/uploads/IMG_5777.PNG','wallpaper'=>'https://palazonline.com/storage/uploads/IMG_5796.jpg','tile'=>'https://palazonline.com/storage/uploads/4.jpg','grass'=>'https://palazonline.com/storage/uploads/IMG_5795.PNG']; @endphp
<div class="cart-layout"><main><div class="cart-top"><strong>{{ $items->sum('quantity') }} عدد در {{ count($items) }} انتخاب</strong><a href="{{ route('shop') }}">← ادامه خرید</a></div><div class="cart-list">@foreach($items as $item)<article class="cart-row-new"><a class="cart-product" href="{{ route('product', ['id' => ($item['model'] ?: $item['id']), 'code' => $item['code'] ?? null]) }}"><div class="mini-visual {{ $item['tone'] }}" style="background-image:linear-gradient(180deg,#00000008,#00000030),url('{{ $item['image'] ?? ($cartImages[$item['category']] ?? $cartImages['carpet']) }}');background-size:cover;background-position:center"><b>PALAZ</b></div><div><small>{{ strtoupper($item['category']) }} / COLLECTION</small><strong>{{ $item['name'] }}</strong><span>{{ $item['unit'] }}</span>@if($item['roll_length'])@php($faLength = strtr((string)$item['roll_length'], ['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']))<small>طاقه: عرض ۳ × طول {{ $faLength }} متر</small>@endif</div></a><div class="cart-qty"><small>تعداد</small><b>{{ $item['quantity'] }}</b></div><div class="cart-price"><small>قیمت</small><b>
@if($item['price'] !== null)
  @if(($item['calculation_type'] ?? null) === 'roll' && $item['roll_length'])
    {{ number_format((float)$item['price'] * 3 * $item['roll_length'] * $item['quantity']) }} تومان
  @else
    {{ number_format((float)$item['price'] * $item['quantity']) }} تومان
  @endif
@else
  استعلام قیمت
@endif
</b></div><form method="POST" action="{{ route('cart.remove', $item['id']) }}" class="cart-remove-form" onsubmit="return confirm('این محصول از سبد خرید حذف شود؟')">
@csrf
<button type="submit" class="cart-remove">حذف از سبد</button>
</form></article>@endforeach</div>
<div class="cart-services"><div><span class="eyebrow">ADD A SERVICE</span><h3>پروژه را کامل کنید</h3><p>اگر خرید شما برای یک فضای واقعی است، خدمات موردنیاز را همین حالا به مسیر اضافه کنید.</p></div><div class="cart-service-links"><a href="{{ route('services',['type'=>'measurement']) }}"><b>⌖ اندازه‌گیری</b><small>ارسال به DTZ ←</small></a><a href="{{ route('services',['type'=>'installation']) }}"><b>⌂ نصب</b><small>درخواست خدمات ←</small></a><a href="{{ route('services',['type'=>'design']) }}"><b>◇ طراحی</b><small>DTZ Tablet ←</small></a></div></div>
</main><aside class="cart-summary"><span class="eyebrow">ORDER SUMMARY</span><h2>خلاصه سفارش</h2><div class="summary-line"><span>محصولات</span><b>{{ count($items) }} مورد</b></div><div class="summary-line"><span>خدمات</span><b>انتخاب در مرحله بعد</b></div><div class="summary-line total"><span>مبلغ</span><b>محاسبه نهایی</b></div><a class="btn btn-primary wide" href="{{ route('checkout') }}">ادامه سفارش <span>←</span></a><small class="summary-note">در مرحله بعد اطلاعات مشتری، خدمات و روش پرداخت را تکمیل می‌کنید.</small></aside></div>
@endif</div></section>
@endsection<style>
.cart-remove-form{margin:0}.cart-remove{border:1px solid #e0c8c8;background:#fff7f7;color:#9f1820;border-radius:9px;padding:9px 12px;font:inherit;font-size:12px;font-weight:800;cursor:pointer}.cart-remove:hover{background:#9f1820;color:#fff}
</style>