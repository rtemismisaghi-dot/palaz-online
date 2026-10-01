@extends('layouts.store')
@section('title',$product['name'].' | PALAZ ONLINE')
@section('content')
@php
  $modelKey = $model ?: $product['id'];
  $productUrl = route('product', ['id' => $modelKey, 'code' => $product['code'] ?? null]);
  $isRoll = ($product['calculation_type'] ?? null) === 'roll';
@endphp
<section class="product-detail product-detail-new">
  <div class="container detail-grid">
    <div class="detail-visual-wrap">
      <div class="detail-visual product-detail-{{ $product['category'] }} {{ $product['tone'] }}" data-product-image>
        @if(!empty($product['image']))
          <img src="{{ $product['image'] }}" alt="{{ $product['name'] }}">
        @else
          <span>PALAZ</span>
        @endif
        <small>{{ $model ?: 'PALAZ COLLECTION' }} / کد {{ $product['code'] ?? $product['id'] }}</small>
      </div>
      @if(count($variants) > 1)
        <div class="variant-strip">
          @foreach($variants as $variant)
            <a class="variant-thumb {{ ($variant['code'] ?? '') === ($product['code'] ?? '') ? 'selected' : '' }}"
               href="{{ route('product', ['id' => $modelKey, 'code' => $variant['code'] ?? null]) }}"
               title="کد {{ $variant['code'] ?? $variant['id'] }}">
              @if(!empty($variant['image']))<img src="{{ $variant['image'] }}" alt="کد {{ $variant['code'] ?? $variant['id'] }}">@endif
              <b>{{ $variant['code'] ?? $variant['id'] }}</b>
            </a>
          @endforeach
        </div>
      @endif
    </div>

    <div class="detail-copy">
      <div class="detail-breadcrumb">
        <a href="{{ route('shop') }}">فروشگاه</a><span>/</span><span>{{ $product['category'] }}</span>
        @if($model)<span>/</span><span>{{ $model }}</span>@endif
      </div>
      <span class="eyebrow">{{ strtoupper($product['category']) }} / {{ $model ?: 'PRODUCT' }}</span>
      <h1>{{ $product['name'] }}</h1>
      @if($product['code'])<div class="selected-code">کد انتخاب‌شده: <strong>{{ $product['code'] }}</strong></div>@endif
      <p class="lead">{{ $product['description'] }}</p>

      @if(count($variants) > 1)
        <div class="option-block model-variants">
          <div class="option-title"><label>{{ $model ?: 'مدل' }}</label><span>{{ count($variants) }} کد / رنگ</span></div>
          <div class="code-list">
            @foreach($variants as $variant)
              <a class="code-chip {{ ($variant['code'] ?? '') === ($product['code'] ?? '') ? 'selected' : '' }}"
                 href="{{ route('product', ['id' => $modelKey, 'code' => $variant['code'] ?? null]) }}">
                <span class="code-chip-image">
                  @if(!empty($variant['image']))<img src="{{ $variant['image'] }}" alt="">@endif
                </span>
                <span>کد {{ $variant['code'] ?? $variant['id'] }}</span>
              </a>
            @endforeach
          </div>
        </div>
      @endif

      <form method="POST" action="{{ route('cart.add',$product['id']) }}" class="product-buy-form" style="margin-top:28px">
        @csrf
        @if($isRoll)
          <div class="roll-buy-grid">
            <div class="quantity">
              <label>تعداد طاقه</label>
              <div class="quantity-control">
                <button type="button" onclick="this.nextElementSibling.stepDown()">−</button>
                <input type="number" name="quantity" value="1" min="1" aria-label="تعداد طاقه">
                <button type="button" onclick="this.previousElementSibling.stepUp()">+</button>
              </div>
            </div>
            <div class="roll-length">
              <label for="roll_length">انتخاب طاقه</label>
              <select id="roll_length" name="roll_length">
                @foreach(range(1,15) as $length)
                  <option value="{{ $length }}" {{ $length === 3 ? 'selected' : '' }}>عرض ۳ × طول {{ $length }} متر</option>
                @endforeach
              </select>
            </div>
          </div>
        @else
          <div class="buy-row">
            <div class="quantity"><label>تعداد</label><div class="quantity-control"><button type="button" onclick="this.nextElementSibling.stepDown()">−</button><input type="number" name="quantity" value="1" min="1"><button type="button" onclick="this.previousElementSibling.stepUp()">+</button></div></div>
          </div>
        @endif
        <div class="price-buy-row">
          <div class="detail-buy-price">
            @if($product['price'] !== null)
              @if($isRoll)
                <small>قیمت هر مترمربع</small>
                <strong>{{ number_format((float)$product['price']) }} تومان</strong>
                <span class="roll-total-price">قیمت هر طاقه: <b data-roll-price>{{ number_format((float)$product['price'] * 3 * 3) }}</b> تومان</span>
              @else
                <strong>{{ number_format((float)$product['price']) }} تومان / {{ $product['unit'] }}</strong>
              @endif
            @else
              <strong>استعلام قیمت</strong>
            @endif
          </div>
          <button class="btn btn-primary buy-btn" type="submit">افزودن به سبد خرید <span>←</span></button>
        </div>
      </form>

      @if($isRoll)
        <div class="roll-note">قیمت و مقدار نهایی طاقه بر اساس طول انتخابی شما در مسیر سفارش مشخص می‌شود.</div>
      @endif

      <div class="project-options">
        <div><span>⌖</span><a href="{{ route('services',['type'=>'measurement']) }}"><b>نیاز به اندازه‌گیری؟</b><small>درخواست را به DTZ بفرستید ←</small></a></div>
        <div><span>◇</span><a href="{{ route('services',['type'=>'design']) }}"><b>نیاز به محاسبه یا طراحی؟</b><small>شروع پروژه در DTZ Tablet ←</small></a></div>
        <div><span>⌂</span><a href="{{ route('services',['type'=>'installation']) }}"><b>نیاز به نصب دارید؟</b><small>درخواست خدمات نصب ←</small></a></div>
      </div>
    </div>
  </div>
</section>

<section class="section detail-info-section">
  <div class="container detail-info-grid">
    <div><span class="eyebrow">ABOUT THE PRODUCT</span><h2>{{ $model ?: 'انتخابی برای فضای شما' }}</h2><p>تمام کدها و رنگ‌های این مدل در همین صفحه در دسترس هستند؛ با انتخاب هر کد، تصویر و مشخصات صفحه روی همان رنگ قرار می‌گیرد.</p></div>
    <div class="spec-table">
      <div><span>کد محصول</span><b>{{ $product['code'] ?? $product['id'] }}</b></div>
      <div><span>مدل</span><b>{{ $model ?: $product['name'] }}</b></div>
      <div><span>دسته‌بندی</span><b>{{ $product['category'] }}</b></div>
      <div><span>خدمات قابل درخواست</span><b>اندازه‌گیری / طراحی / نصب</b></div>
    </div>
  </div>
</section>
<section class="section compact"><div class="container feature-strip"><div><b>01</b><span>مشاوره تخصصی</span></div><div><b>02</b><span>اندازه‌گیری</span></div><div><b>03</b><span>محاسبه پروژه</span></div><div><b>04</b><span>نصب حرفه‌ای</span></div></div></section>

<style>
.detail-visual{overflow:hidden;position:relative}.detail-visual img{width:100%;height:100%;object-fit:cover;display:block}.detail-visual small{position:absolute;right:16px;bottom:14px;background:#ffffffdd;padding:7px 10px;border-radius:8px;font-weight:800}
.variant-strip{display:flex;gap:8px;overflow:auto;margin-top:10px;padding-bottom:4px}.variant-thumb{position:relative;min-width:70px;height:70px;border:2px solid #e6ddd7;border-radius:10px;overflow:hidden;background:#f5f0ec;text-decoration:none;color:#241f1d}.variant-thumb.selected{border-color:#9f1820}.variant-thumb img{width:100%;height:100%;object-fit:cover}.variant-thumb b{position:absolute;left:4px;bottom:4px;background:#fffdfddd;padding:2px 5px;border-radius:5px;font-size:10px}
.selected-code{display:inline-flex;margin:2px 0 10px;padding:7px 11px;background:#f4efeb;border-radius:9px;font-size:13px}.selected-code strong{margin-right:5px}
.option-title{display:flex;justify-content:space-between;align-items:center;margin-bottom:9px}.option-title span{font-size:11px;color:#7b716b}.code-list{display:flex;flex-wrap:wrap;gap:8px}.code-chip{display:flex;align-items:center;gap:7px;border:1px solid #ded5cf;border-radius:10px;padding:5px 9px;background:#fff;text-decoration:none;color:#241f1d;font-weight:800;font-size:12px}.code-chip.selected{border-color:#9f1820;background:#fff5f5}.code-chip-image{width:30px;height:30px;border-radius:7px;overflow:hidden;background:#eee6e1}.code-chip-image img{width:100%;height:100%;object-fit:cover}
.roll-buy-grid{display:grid;grid-template-columns:1fr 1.4fr;gap:10px;margin-bottom:12px;align-items:end}.roll-buy-grid .quantity,.roll-buy-grid .roll-length{min-width:0}.roll-buy-grid label,.quantity>label,.roll-length label{display:block;font-size:12px;font-weight:800;margin-bottom:6px}.quantity-control{display:flex;align-items:center;border:1px solid #ddd4ce;border-radius:10px;overflow:hidden;height:44px}.quantity-control button{width:38px;height:100%;border:0;background:#f5f0ec;font-size:20px}.quantity-control input{width:55px;height:100%;border:0;text-align:center;outline:0}.roll-length select{width:100%;height:44px;border:1px solid #ddd4ce;border-radius:10px;padding:0 10px;background:#fff}.price-buy-row{display:flex;flex-direction:column;align-items:stretch;gap:10px;margin-top:4px}.detail-buy-price{font-weight:900;display:flex;flex-direction:column;gap:3px;padding:12px 14px;background:#f7f3ef;border:1px solid #e6ddd7;border-radius:12px}.detail-buy-price small{font-size:11px;color:#756d68;font-weight:700}.detail-buy-price strong{font-size:20px}.roll-total-price{font-size:12px;color:#756d68}.roll-total-price b{font-size:15px;color:#241f1d}.price-buy-row .buy-btn{width:100%;min-height:48px}.roll-note{font-size:12px;color:#756d68;background:#f8f4f1;border-radius:10px;padding:9px 11px;margin:10px 0 14px}
@media(max-width:600px){.roll-buy-grid{grid-template-columns:1fr}.price-buy-row{flex-direction:column;align-items:stretch}.price-buy-row .buy-btn{width:100%}}
</style>
@if($isRoll && $product['price'] !== null)
<script>
document.addEventListener('DOMContentLoaded', function () {
  const select = document.getElementById('roll_length');
  const price = {{ (float) $product['price'] }};
  const total = document.querySelector('[data-roll-price]');
  if (!select || !total) return;
  const updateRollPrice = () => {
    const length = Math.max(1, Math.min(15, Number(select.value) || 3));
    total.textContent = new Intl.NumberFormat('fa-IR').format(price * 3 * length);
  };
  select.addEventListener('change', updateRollPrice);
  updateRollPrice();
});
</script>
@endif
@endsection