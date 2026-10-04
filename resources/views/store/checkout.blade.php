@extends('layouts.store')
@section('title','ثبت سفارش | PALAZ ONLINE')
@section('content')
<section class="page-head checkout-head"><div class="container"><span class="eyebrow">PALAZ ONLINE / CHECKOUT</span><h1>اطلاعات سفارش</h1><p>اطلاعات مشتری، خدمات موردنیاز و روش پرداخت را تکمیل کنید.</p></div></section>
<section class="section checkout-section"><div class="container">
@if(session('installation_error'))
<div class="checkout-alert checkout-alert-error">{{ session('installation_error') }}</div>
@endif
</div><div class="container checkout-layout">
<form class="checkout-form" method="POST" action="{{ route('checkout.place') }}">
@csrf
<div class="checkout-block"><span class="eyebrow">01 / CUSTOMER</span><h2>اطلاعات مشتری</h2><div class="form-two"><label>نام و نام خانوادگی<input name="name" value="{{ old('name') }}" required></label><label>شماره تماس<input name="phone" value="{{ old('phone') }}" required inputmode="tel"></label></div></div>
<div class="checkout-block"><span class="eyebrow">02 / DELIVERY</span><h2>آدرس تحویل</h2><div class="form-two"><label>شهر<input name="city" value="{{ old('city') }}" required></label><label>کد پستی<input name="postal_code" value="{{ old('postal_code') }}" inputmode="numeric"></label></div><label>آدرس کامل<textarea name="address" rows="4" required>{{ old('address') }}</textarea></label></div>
<div class="checkout-block"><span class="eyebrow">03 / SERVICES</span><h2>خدمات پروژه</h2><div class="checkout-options"><label><input type="radio" name="service" value="none" {{ old('service','none') === 'none' ? 'checked' : '' }}><span><b>بدون خدمات</b><small>فقط خرید محصول</small></span></label><label><input type="radio" name="service" value="measurement" {{ old('service') === 'measurement' ? 'checked' : '' }}><span><b>اندازه‌گیری</b><small>درخواست برای ادامه فرآیند</small></span></label><label><input type="radio" name="service" value="installation" {{ old('service') === 'installation' ? 'checked' : '' }}><span><b>نصب</b><small>درخواست نصب و اجرا</small></span></label><label><input type="radio" name="service" value="design" {{ old('service') === 'design' ? 'checked' : '' }}><span><b>طراحی و محاسبه</b><small>ادامه از مسیر طراحی پالاز</small></span></label></div></div>
<div class="checkout-block installation-details" id="installation-details" hidden>
<span class="eyebrow">INSTALLATION / PROJECT</span>
<h2>اطلاعات نصب</h2>
<p class="installation-hint">طاقه‌ها هنگام خرید انتخاب شده‌اند؛ متراژ نصب از همان انتخاب‌ها به‌صورت خودکار محاسبه و برای فرآیند اجرا ارسال می‌شود. نیازی به ورود دوباره متراژ یا انتخاب طاقه نیست.</p>
<div class="installation-auto-summary">
    <div><span>متراژ کل نصب</span><b>{{ $installationArea > 0 ? number_format($installationArea, 2) . ' مترمربع' : 'بر اساس سفارش محاسبه می‌شود' }}</b></div>
    @if($installationRollQuantity > 0)<div><span>تعداد طاقه</span><b>{{ $installationRollQuantity }} عدد</b></div>@endif
</div>
<label>توضیحات اولیه نصب <span class="optional">اختیاری</span><textarea name="installation_description" rows="3" placeholder="مثلاً توضیح کوتاه درباره محل یا زمان مناسب اجرا...">{{ old('installation_description') }}</textarea></label>
</div>
<div class="checkout-block"><span class="eyebrow">04 / PAYMENT</span><h2>روش پرداخت</h2><label class="payment-choice"><input type="radio" name="payment" value="offline" checked><span><b>پرداخت پس از تأیید سفارش</b><small>درگاه بانکی در این مرحله متصل نیست.</small></span></label><button class="btn btn-primary wide" type="submit">ثبت سفارش و دریافت کد پیگیری ←</button></div>
</form>
<aside class="checkout-summary"><span class="eyebrow">YOUR ORDER</span><h2>جزئیات سفارش</h2>
@foreach($items as $item)
@php($isRoll = ($item['calculation_type'] ?? null) === 'roll')
@php($length = $item['roll_length'] ?? null)
@php($lineTotal = $item['price'] !== null ? (($isRoll && $length) ? (float)$item['price'] * 3 * $length * $item['quantity'] : (float)$item['price'] * $item['quantity']) : null)
<div class="checkout-item">
@if(!empty($item['image']))<div class="checkout-item-image" style="background-image:url('{{ $item['image'] }}')"></div>@endif
<div class="checkout-item-info"><b>{{ $item['name'] }}</b><small>کد: {{ $item['code'] ?? '—' }}</small>@if($isRoll && $length)<small>طاقه: عرض ۳ × طول {{ strtr((string)$length,['0'=>'۰','1'=>'۱','2'=>'۲','3'=>'۳','4'=>'۴','5'=>'۵','6'=>'۶','7'=>'۷','8'=>'۸','9'=>'۹']) }} متر</small>@endif<small>تعداد: {{ $item['quantity'] }}</small></div>
<div class="checkout-item-price">@if($lineTotal !== null){{ number_format($lineTotal) }} تومان@else استعلام قیمت@endif</div>
</div>
@endforeach
<div class="summary-line"><span>جمع محصولات</span><b>{{ $itemsTotal !== null ? number_format($itemsTotal) . ' تومان' : 'استعلام قیمت' }}</b></div>
<div class="summary-line total"><span>مبلغ نهایی</span><b>{{ $itemsTotal !== null ? number_format($itemsTotal) . ' تومان' : 'استعلام قیمت' }}</b></div>
<a href="{{ route('cart') }}">← بازگشت به سبد خرید</a></aside>
</div></section>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const details = document.getElementById('installation-details');
    const radios = document.querySelectorAll('input[name="service"]');

    function syncInstallationFields() {
        const selected = document.querySelector('input[name="service"]:checked')?.value;
        const active = selected === 'installation';
        details.hidden = !active;
        details.querySelectorAll('input, textarea').forEach((field) => {
            field.disabled = !active;
            field.required = false;
        });
    }

    radios.forEach((radio) => radio.addEventListener('change', syncInstallationFields));
    syncInstallationFields();
});
</script>
<style>
 .checkout-alert{margin-bottom:16px;padding:14px 16px;border-radius:12px;font-size:13px;line-height:1.8}.checkout-alert-error{border:1px solid #e6c8c2;background:#fff7f5;color:#8b2f24}.installation-auto-summary{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:14px 0 16px}.installation-auto-summary>div{padding:12px 14px;border:1px solid #e7ddd7;border-radius:10px;background:#fff}.installation-auto-summary span{display:block;font-size:11px;color:#756d68;margin-bottom:5px}.installation-auto-summary b{font-size:14px}.installation-details{margin-top:16px;border:1px solid #eadfd8;background:#fcfaf8}.installation-hint{color:#756d68;font-size:12px;line-height:1.8;margin:0 0 16px}.optional{font-size:10px;color:#958b85;font-weight:600}.checkout-item{display:grid;grid-template-columns:64px 1fr auto;gap:10px;align-items:center;padding:12px 0;border-bottom:1px solid #eee7e2}.checkout-item-image{width:64px;height:64px;border-radius:10px;background-size:cover;background-position:center}.checkout-item-info{display:flex;flex-direction:column;gap:3px;min-width:0}.checkout-item-info b{font-size:13px}.checkout-item-info small{font-size:11px;color:#756d68}.checkout-item-price{font-size:12px;font-weight:900;white-space:nowrap}.summary-line{display:flex;justify-content:space-between;gap:12px;margin-top:12px}.summary-line.total{padding-top:12px;border-top:1px solid #ddd4ce;font-size:15px}.summary-line.total b{font-size:17px}.checkout-summary>a{display:inline-block;margin-top:16px}@media(max-width:700px){.checkout-item{grid-template-columns:56px 1fr}.checkout-item-image{width:56px;height:56px}.checkout-item-price{grid-column:2}.checkout-item-info{grid-column:2}}
</style>
@endsection