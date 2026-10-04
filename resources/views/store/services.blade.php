@extends('layouts.store')
@section('title','خدمات پالاز | PALAZ ONLINE')
@section('content')
@php
  $type = request('type', 'measurement');
  $productQuery = $selectedProduct ? ['product' => $selectedProduct['id'], 'code' => $selectedProduct['code'] ?? null] : [];
@endphp

<section class="services-hero">
  <div class="container">
    <span class="eyebrow">PALAZ ONLINE / SERVICES</span>
    <div class="services-hero-grid">
      <div>
        <h1>از انتخاب محصول<br><strong>تا اجرای پروژه.</strong></h1>
        <p>خدمت موردنیازتان را انتخاب کنید؛ اطلاعات محصول و پروژه در همان مسیر همراه شما می‌ماند.</p>
      </div>
      @if($selectedProduct)
        <div class="selected-product-card">
          <div class="selected-product-image">
            @if(!empty($selectedProduct['image']))<img src="{{ $selectedProduct['image'] }}" alt="{{ $selectedProduct['name'] }}">@else<span>PALAZ</span>@endif
          </div>
          <div>
            <small>محصول انتخاب‌شده</small>
            <strong>{{ $selectedProduct['name'] }}</strong>
            <span>کد {{ $selectedProduct['code'] ?? $selectedProduct['id'] }}</span>
          </div>
        </div>
      @endif
    </div>
  </div>
</section>

<section class="section services-page">
  <div class="container">
    <div class="service-choice-grid service-choice-grid-modern">
      <a class="service-choice-card {{ $type==='measurement'?'chosen':'' }}" href="{{ route('services', array_merge(['type'=>'measurement'], $productQuery)) }}">
        <span class="service-number">01</span><span class="service-icon">⌖</span>
        <div><small>DTZ</small><h3>اندازه‌گیری</h3><p>اگر متراژ دقیق ندارید، پروژه را برای اندازه‌گیری آماده می‌کنیم.</p><b>{{ $type==='measurement'?'انتخاب شده':'انتخاب' }} ←</b></div>
      </a>
      <a class="service-choice-card {{ $type==='installation'?'chosen':'' }}" href="{{ route('services', array_merge(['type'=>'installation'], $productQuery)) }}">
        <span class="service-number">02</span><span class="service-icon">⌂</span>
        <div><small>DTZ</small><h3>نصب و اجرا</h3><p>درخواست نصب مستقیماً وارد سیستم تخصصی اجرای پالاز می‌شود.</p><b>{{ $type==='installation'?'انتخاب شده':'انتخاب' }} ←</b></div>
      </a>
      <a class="service-choice-card {{ $type==='design'?'chosen':'' }}" href="{{ route('services', array_merge(['type'=>'design'], $productQuery)) }}">
        <span class="service-number">03</span><span class="service-icon">◇</span>
        <div><small>DTZ TABLET</small><h3>طراحی و محاسبه</h3><p>برای طراحی، پلان و محاسبات پروژه مسیر تخصصی آماده است.</p><b>{{ $type==='design'?'انتخاب شده':'انتخاب' }} ←</b></div>
      </a>
    </div>
  </div>
</section>

<section class="section request-section">
  <div class="container request-layout request-layout-modern">
    <div class="request-side">
      <span class="eyebrow">START / {{ strtoupper($type) }}</span>
      <h2>{{ $type === 'installation' ? 'درخواست نصب را شروع کنید.' : 'اطلاعات پروژه را ثبت کنید.' }}</h2>
      <p>فرم را کوتاه نگه داشته‌ایم. اطلاعات محصول انتخاب‌شده خودکار منتقل می‌شود و لازم نیست دوباره آن را وارد کنید.</p>
      <div class="request-flow">
        <span><b>01</b>محصول</span><i>→</i><span><b>02</b>خدمت</span><i>→</i><span><b>03</b>اطلاعات پروژه</span><i>→</i><span><b>04</b>ثبت</span>
      </div>
      @if($selectedProduct)
        <div class="flow-product"><span>محصول شما</span><strong>{{ $selectedProduct['name'] }}</strong><small>کد {{ $selectedProduct['code'] ?? $selectedProduct['id'] }}</small></div>
      @endif
    </div>

    <form class="request-form request-form-modern" method="POST" action="{{ route('services.request') }}">
      @csrf
      <input type="hidden" name="type" value="{{ $type }}">
      <input type="hidden" name="product_id" value="{{ $selectedProduct['id'] ?? '' }}">
      <input type="hidden" name="product_code" value="{{ $selectedProduct['code'] ?? '' }}">
      <input type="hidden" name="product_title" value="{{ $selectedProduct['name'] ?? '' }}">
      <input type="hidden" name="product_model" value="{{ $selectedProduct['model'] ?? '' }}">

      @if(session('service_success'))
        <div class="service-alert success">{{ session('service_success') }}</div>
      @endif
      @if(session('service_error'))
        <div class="service-alert error">{{ session('service_error') }}</div>
      @endif
      @if($errors->any())
        <div class="service-alert error">{{ $errors->first() }}</div>
      @endif

      <div class="form-head"><span class="eyebrow">PROJECT DETAILS</span><h2>اطلاعات شما</h2></div>
      <div class="form-two">
        <label>نام و نام خانوادگی<input name="name" value="{{ old('name') }}" required placeholder="نام شما"></label>
        <label>شماره تماس<input name="phone" value="{{ old('phone') }}" required placeholder="09xxxxxxxxx" inputmode="tel"></label>
      </div>
      <div class="form-two">
        <label>شهر<input name="city" value="{{ old('city') }}" required placeholder="مثلاً تهران"></label>
        <label>متراژ تقریبی <span class="optional">اختیاری</span><input name="area" value="{{ old('area') }}" type="number" min="0" step="0.01" placeholder="مثلاً ۶۰"></label>
      </div>
      <label>آدرس محل پروژه<textarea name="address" required rows="3" placeholder="آدرس کامل محل اجرا"></textarea></label>
      <div class="form-two">
        <label>تعداد <span class="optional">اختیاری</span><input name="quantity" value="{{ old('quantity', 1) }}" type="number" min="0" step="1"></label>
        <label>توضیحات <span class="optional">اختیاری</span><input name="description" value="{{ old('description') }}" placeholder="مثلاً طبقه دوم، آسانسور دارد..."></label>
      </div>
      <label class="consent"><input type="checkbox" required> <span>اطلاعات برای پیگیری و اجرای درخواست خدمات استفاده می‌شود.</span></label>
      <button class="btn btn-primary wide service-submit" type="submit">
        {{ $type === 'installation' ? 'ثبت درخواست نصب' : 'ثبت درخواست و دریافت کد پیگیری' }} <span>←</span>
      </button>
      <small class="form-note">درخواست نصب پس از ثبت مستقیماً به سامانه تخصصی DTZ ارسال می‌شود.</small>
    </form>
  </div>
</section>

<style>
.services-hero{padding:78px 0 42px;background:linear-gradient(180deg,#fbf8f5 0%,#f4efeb 100%);border-bottom:1px solid #e9e0da}.services-hero-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:30px;align-items:end}.services-hero h1{font-size:clamp(38px,5vw,72px);line-height:1.08;letter-spacing:-2px;margin:16px 0}.services-hero h1 strong{font-weight:900}.services-hero p{max-width:650px;color:#756d68;font-size:17px;line-height:1.9}.selected-product-card{display:flex;align-items:center;gap:14px;padding:14px;border:1px solid #e3d9d2;border-radius:20px;background:#ffffffd9;box-shadow:0 14px 45px #34271b0d}.selected-product-image{width:78px;height:78px;border-radius:14px;overflow:hidden;background:#eee7e2;display:grid;place-items:center;font-weight:900}.selected-product-image img{width:100%;height:100%;object-fit:cover}.selected-product-card small,.selected-product-card span{display:block;color:#7b716b;font-size:11px}.selected-product-card strong{display:block;font-size:15px;margin:5px 0}.service-choice-grid-modern{grid-template-columns:repeat(3,1fr)}.service-choice-card{position:relative}.service-choice-card.chosen{border-color:#9f1820;box-shadow:0 15px 40px #9f18201a}.service-number{position:absolute;top:18px;left:18px;font-size:11px;color:#a79d97;font-weight:800}.service-icon{font-size:30px}.request-layout-modern{align-items:start}.request-form-modern{background:#fff;border:1px solid #e5dcd6;border-radius:24px;padding:26px;box-shadow:0 20px 60px #35271b12}.request-form-modern label{color:#312a26;font-weight:800;font-size:13px}.request-form-modern input,.request-form-modern select,.request-form-modern textarea{width:100%;margin-top:7px;border:1px solid #ded5cf;border-radius:12px;background:#fbfaf9;padding:12px 13px;font:inherit;outline:none;transition:.2s}.request-form-modern input:focus,.request-form-modern textarea:focus{border-color:#9f1820;background:#fff;box-shadow:0 0 0 4px #9f18200d}.request-form-modern textarea{resize:vertical}.optional{font-size:10px;color:#958b85;font-weight:600}.flow-product{margin-top:24px;padding:16px;border-radius:16px;background:#fff;border:1px solid #e5dcd6;display:grid;gap:5px}.flow-product span,.flow-product small{color:#857a73;font-size:11px}.flow-product strong{font-size:15px}.service-alert{padding:13px 15px;border-radius:13px;margin-bottom:16px;font-size:13px;font-weight:700}.service-alert.success{background:#eef8f0;color:#28623b}.service-alert.error{background:#fff0f0;color:#8d2027}.form-note{display:block;text-align:center;color:#8b817b;margin-top:10px;font-size:11px}.request-flow{display:flex;align-items:center;gap:10px;flex-wrap:wrap}.request-flow span{display:flex;align-items:center;gap:7px;color:#6f6660;font-size:11px}.request-flow b{width:26px;height:26px;border-radius:50%;display:grid;place-items:center;background:#eee7e2;color:#3d3530}.request-flow i{color:#aaa098;font-style:normal}@media(max-width:900px){.services-hero-grid,.request-layout-modern{grid-template-columns:1fr}.service-choice-grid-modern{grid-template-columns:1fr}.services-hero{padding-top:50px}.services-hero h1{font-size:44px}.request-form-modern{padding:20px}}@media(max-width:600px){.services-hero h1{font-size:37px;letter-spacing:-1px}.form-two{grid-template-columns:1fr!important}.request-form-modern{border-radius:18px;padding:16px}.request-flow{gap:7px}.request-flow i{display:none}.selected-product-card{padding:11px}.selected-product-image{width:62px;height:62px}}
</style>
@endsection