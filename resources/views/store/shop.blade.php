@extends('layouts.store')
@section('title','فروشگاه | PALAZ ONLINE')
@section('content')
@php
$shopImages=['carpet'=>'https://palazonline.com/storage/uploads/005-1-2.jpg','laminate'=>'https://palazonline.com/storage/uploads/IMG_1100-4.PNG','spc'=>'https://palazonline.com/storage/uploads/IMG_5777.PNG','wallpaper'=>'https://palazonline.com/storage/uploads/IMG_5796.jpg','tile'=>'https://palazonline.com/storage/uploads/4.jpg','grass'=>'https://palazonline.com/storage/uploads/IMG_5795.PNG'];
$categories=['carpet'=>'موکت','laminate'=>'لمینیت','spc'=>'SPC','wallpaper'=>'کاغذ دیواری','tile'=>'موکت تایل','grass'=>'چمن مصنوعی'];
@endphp

<section class="shop-storefront">
  <div class="container">
    <div class="shop-storefront-top">
      <div>
        <span class="eyebrow">PALAZ ONLINE / ONLINE STORE</span>
        <h1>فروشگاه آنلاین</h1>
        <p>اگر محصولتان را می‌شناسید، مستقیم از اینجا انتخاب و خرید را شروع کنید.</p>
      </div>
      <a href="{{ route('cart') }}" class="shop-cart-button"><span>🛒</span> سبد خرید <b>{{ count(session('cart', [])) }}</b></a>
    </div>

    <div class="shop-search-panel">
      <form class="shop-search-form" action="{{ route('shop') }}">
        <span class="shop-search-icon">⌕</span>
        <input name="q" value="{{ request('q') }}" placeholder="نام محصول، کد، آلبوم یا برند را جستجو کنید..." aria-label="جستجوی محصول">
        <button type="submit">جستجو</button>
      </form>
      <button class="visual-search-button" type="button" id="visualSearchButton">
        <span>◉</span>
        <strong>جستجوی تصویری</strong>
        <small>با عکس محصول را پیدا کنید</small>
      </button>
      <input id="visualSearchInput" type="file" accept="image/*" hidden>
    </div>

    <div class="shop-category-row">
      <a class="{{ !$category?'selected':'' }}" href="{{ route('shop') }}">همه محصولات</a>
      @foreach($categories as $key=>$label)
        <a class="{{ $category===$key?'selected':'' }}" href="{{ route('shop',['category'=>$key]) }}">{{ $label }}</a>
      @endforeach
    </div>

    <div class="shop-main-grid">
      <aside class="shop-filter">
        <div class="filter-title"><span>FILTER</span><strong>فیلتر محصولات</strong></div>
        <div class="filter-group">
          <b>دسته‌بندی</b>
          <a class="{{ !$category?'selected':'' }}" href="{{ route('shop') }}">همه محصولات <span>↗</span></a>
          @foreach($categories as $key=>$label)
            <a class="{{ $category===$key?'selected':'' }}" href="{{ route('shop',['category'=>$key]) }}">{{ $label }} <span>↗</span></a>
          @endforeach
        </div>
        <div class="filter-group">
          <b>نوع نمایش</b>
          <label><input type="checkbox"> فقط محصولات موجود</label>
          <label><input type="checkbox"> محصولات ویژه</label>
        </div>
        <div class="filter-group">
          <b>خدمات همراه</b>
          <label><input type="checkbox"> نیاز به اندازه‌گیری</label>
          <label><input type="checkbox"> نیاز به نصب</label>
        </div>
        <a class="filter-service" href="{{ route('services') }}">پروژه دارید؟<strong>مسیر خدمات را ببینید ←</strong></a>
      </aside>

      <main class="shop-products-area">
        <div class="shop-products-head">
          <div>
            <strong>{{ count($products) }} محصول</strong>
            @if(request('q'))<span>نتیجه جستجو برای «{{ request('q') }}»</span>@endif
          </div>
          <label>مرتب‌سازی
            <select id="shopSort" aria-label="مرتب‌سازی محصولات">
              <option value="default">پیش‌فرض</option>
              <option value="name">نام محصول</option>
            </select>
          </label>
        </div>

        <div class="product-grid shop-grid" id="shopProducts">
          @forelse($products as $index=>$product)
            @php
              $image=$shopImages[$product['category']] ?? $shopImages['carpet'];
              $price=$product['price'] ?? null;
            @endphp
            <article class="product-card shop-product" data-product-name="{{ $product['name'] }}" data-product-index="{{ $index }}">
              <a href="{{ route('product',$product['id']) }}">
                <div class="product-visual product-{{ $product['category'] }} {{ $product['tone'] }}" style="background-image:linear-gradient(180deg,#00000008,#00000045),url('{{ $image }}');background-size:cover;background-position:center;">
                  <span>{{ sprintf('%02d',$index+1) }}</span>
                  <b>PALAZ</b>
                  <i>مشاهده سریع</i>
                </div>
                <div class="product-info">
                  <div class="product-meta"><small>{{ strtoupper($product['category']) }} / COLLECTION</small><small>PALAZ</small></div>
                  <h3>{{ $product['name'] }}</h3>
                  <p>{{ $product['unit'] }}</p>
                  @if($price !== null)<strong class="shop-price">{{ number_format((float)$price) }} <small>تومان</small></strong>@else<strong class="shop-price">استعلام قیمت</strong>@endif
                  <div class="shop-card-actions"><span>مشاهده</span><span>مقایسه</span><span>🛒</span></div>
                </div>
              </a>
            </article>
          @empty
            <div class="empty">محصولی با این مشخصات پیدا نشد.</div>
          @endforelse
        </div>
      </main>
    </div>
  </div>
</section>

<section class="shop-service-strip"><div class="container"><span class="eyebrow">PALAZ SUPPORT</span><strong>محصول را پیدا کردید؟ برای اندازه‌گیری و اجرای پروژه هم کنار شما هستیم.</strong><a href="{{ route('services') }}">خدمات پالاز ←</a></div></section>

<script>
document.addEventListener('DOMContentLoaded',function(){
  const button=document.getElementById('visualSearchButton');
  const input=document.getElementById('visualSearchInput');
  if(button && input) button.addEventListener('click',()=>input.click());
  if(input) input.addEventListener('change',function(){
    if(this.files && this.files[0]){
      button.querySelector('strong').textContent='تصویر انتخاب شد';
      button.querySelector('small').textContent='جستجوی تصویری در حال آماده‌سازی است';
    }
  });
  const sort=document.getElementById('shopSort');
  const grid=document.getElementById('shopProducts');
  if(sort && grid) sort.addEventListener('change',function(){
    if(this.value!=='name') return;
    [...grid.querySelectorAll('.shop-product')]
      .sort((a,b)=>(a.dataset.productName||'').localeCompare(b.dataset.productName||'','fa'))
      .forEach(el=>grid.appendChild(el));
  });
});
</script>
@endsection