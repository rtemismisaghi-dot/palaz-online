@extends('layouts.store')
@section('title','فروشگاه | PALAZ ONLINE')
@section('content')
@php
$products = collect($products);
$categories = $products->groupBy('category');
$albumGroups = $products->filter(fn ($p) => filled($p['attributes']['album'] ?? null))->groupBy(fn ($p) => (string) $p['attributes']['album']);
$categoryLabels = [
 'carpet'=>'موکت','laminate'=>'لمینت','spc'=>'فرش‌گونه','wallpaper'=>'کاغذ دیواری',
 'tile'=>'موکت تایل','grass'=>'چمن مصنوعی','doormat'=>'پادری','guard'=>'گارد','spaghetti'=>'اسپاگتی',
 'carpet-rug'=>'فرش‌موکت'
];
$activeLabel=$categoryLabels[$category] ?? 'همه محصولات';
@endphp

<style>
.store-shop{background:#f8f5f2;min-height:100vh;padding:28px 0 70px;color:#241f1d}
.store-hero{display:flex;justify-content:space-between;align-items:end;gap:20px;margin-bottom:22px}
.store-hero h1{font-size:34px;margin:5px 0;font-weight:900}.store-hero p{color:#756d68;margin:0}
.store-cart{background:#9f1820;color:#fff;padding:13px 18px;border-radius:14px;text-decoration:none;font-weight:800;white-space:nowrap}
.store-search{background:#fff;border:1px solid #ebe5e0;border-radius:18px;padding:10px;display:flex;gap:10px;margin-bottom:18px;box-shadow:0 8px 25px #241f1d08}
.store-search input{flex:1;border:0;outline:0;padding:12px;font-size:15px}.store-search button{border:0;background:#241f1d;color:#fff;border-radius:12px;padding:0 24px}
.category-rail{display:flex;gap:9px;overflow-x:auto;padding:4px 0 15px;scrollbar-width:thin}
.category-pill{background:#fff;border:1px solid #e5ddd7;border-radius:999px;padding:11px 19px;white-space:nowrap;text-decoration:none;color:#403a36;font-weight:800}
.category-pill.active{background:#9f1820;color:#fff;border-color:#9f1820}
.store-layout{display:grid;grid-template-columns:250px minmax(0,1fr);gap:18px}
.store-filters,.store-content{background:#fff;border:1px solid #ebe5e0;border-radius:20px}
.store-filters{padding:18px;height:max-content;position:sticky;top:16px}.filter-section{padding:14px 0;border-bottom:1px solid #eee6e1}.filter-section:last-child{border:0}.filter-section strong{display:block;margin-bottom:11px}.filter-section label{display:block;margin:8px 0;color:#625b56;font-size:14px}
.price-inputs{display:flex;gap:7px}.price-inputs input{width:50%;border:1px solid #ddd4ce;border-radius:9px;padding:9px}
.store-content{padding:18px;min-width:0}
.model-head{display:flex;justify-content:space-between;align-items:center;margin-bottom:7px}.model-head strong{font-size:17px}.model-rail{display:flex;gap:9px;overflow-x:auto;padding:5px 0 16px;border-bottom:1px solid #eee6e1}
.model-pill{border:1px solid #ddd4ce;background:#faf8f6;border-radius:12px;padding:10px 15px;white-space:nowrap;cursor:pointer;font-weight:700}
.model-pill.active{background:#241f1d;color:#fff;border-color:#241f1d}.model-count{font-size:11px;opacity:.65;margin-right:5px}
.result-head{display:flex;justify-content:space-between;align-items:center;padding:18px 0 12px}.result-head span{color:#756d68;font-size:13px}
.store-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:13px}
.store-card{border:1px solid #ebe5e0;border-radius:16px;overflow:hidden;background:#fff;transition:.18s}.store-card:hover{transform:translateY(-2px);box-shadow:0 10px 25px #241f1d10}
.card-image{height:190px;background:#f0ebe7;position:relative;overflow:hidden}.card-image img{width:100%;height:100%;object-fit:cover}.code-badge{position:absolute;top:10px;right:10px;background:#fffdfbcc;border-radius:8px;padding:5px 8px;font-size:11px;font-weight:800}
.card-body{padding:12px}.card-body small{color:#756d68}.card-body h3{font-size:14px;margin:5px 0;font-weight:900}.card-price{font-weight:900;margin-top:8px}.card-unit{font-size:11px;color:#756d68}.card-actions{display:flex;gap:6px;margin-top:10px}.card-actions a,.card-actions button{flex:1;border:1px solid #e1d8d2;background:#fff;border-radius:9px;padding:8px;text-align:center;text-decoration:none;color:#241f1d;font-size:12px}.card-actions .buy{background:#9f1820;color:#fff;border-color:#9f1820}
.empty-state{padding:60px;text-align:center;color:#756d68}
@media(max-width:1050px){.store-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.store-layout{grid-template-columns:210px minmax(0,1fr)}}
@media(max-width:760px){.store-shop{padding:16px 0 45px}.store-hero{align-items:start}.store-hero h1{font-size:25px}.store-cart{padding:10px 13px}.store-layout{display:block}.store-filters{position:static;margin-bottom:12px}.store-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.card-image{height:145px}.store-content{padding:12px}.model-rail{margin-bottom:2px}}
</style>

<section class="store-shop">
<div class="container">
  <div class="store-hero">
    <div><small>PALAZ ONLINE / STORE</small><h1>فروشگاه آنلاین</h1><p>دسته را انتخاب کنید، مدل را ببینید و بعد کد محصول را انتخاب کنید.</p></div>
    <a class="store-cart" href="{{ route('cart') }}">🛒 سبد خرید <span>{{ count(session('cart',[])) }}</span></a>
  </div>

  <form class="store-search" action="{{ route('shop') }}" method="get">
    @if($category)<input type="hidden" name="category" value="{{ $category }}">@endif
    <input name="q" value="{{ request('q') }}" placeholder="نام مدل، کد یا محصول را جستجو کنید...">
    <button>جستجو</button>
  </form>

  <nav class="category-rail">
    <a class="category-pill {{ !$category?'active':'' }}" href="{{ route('shop') }}">همه</a>
    @foreach($categoryLabels as $key=>$label)
      @if($categories->has($key))
        <a class="category-pill {{ $category===$key?'active':'' }}" href="{{ route('shop',['category'=>$key]) }}">{{ $label }}</a>
      @endif
    @endforeach
  </nav>

  <div class="store-layout">
    <aside class="store-filters">
      <div class="filter-section"><strong>فیلتر قیمت</strong><div class="price-inputs"><input id="minPrice" type="number" placeholder="از"><input id="maxPrice" type="number" placeholder="تا"></div></div>
      <div class="filter-section"><strong>مدل انتخاب‌شده</strong><div id="selectedModel">همه مدل‌ها</div></div>
      <div class="filter-section"><strong>محصولات</strong><label><input id="onlyAvailable" type="checkbox"> فقط موجود</label><label><input id="featured" type="checkbox"> منتخب پالاز</label></div>
      <div class="filter-section"><strong>خدمات</strong><label><input type="checkbox"> اندازه‌گیری</label><label><input type="checkbox"> نصب</label></div>
    </aside>

    <main class="store-content">
      <div class="model-head"><strong>مدل‌ها</strong><span>{{ $activeLabel }}</span></div>
      <div class="model-rail" id="modelRail">
        <button class="model-pill active" data-model="all">همه مدل‌ها</button>
        @foreach($albumGroups as $album => $albumProducts)
          <button class="model-pill" data-model="{{ md5($album) }}">
            {{ $album }} <span class="model-count">{{ $albumProducts->count() }}</span>
          </button>
        @endforeach
      </div>

      <div class="result-head"><strong id="resultCount">{{ count($products) }} محصول</strong><span>ابتدا مدل، سپس کد محصول را انتخاب کنید.</span></div>

      <div class="store-grid" id="productGrid">
        @forelse($products as $index=>$product)
          <article class="store-card product-item" data-model="{{ md5($product['attributes']['album'] ?? $product['name']) }}" data-name="{{ e($product['name']) }}" data-price="{{ (float)($product['price'] ?? 0) }}" data-id="{{ $product['id'] }}">
            <a href="{{ route('product',$product['id']) }}" class="card-image">
              @if(!empty($product['image']))
                <img src="{{ str_starts_with($product['image'],'http') ? $product['image'] : asset($product['image']) }}" alt="{{ $product['name'] }}" loading="lazy">
              @endif
              <span class="code-badge">کد {{ $product['attributes']['code'] ?? $product['id'] }}</span>
            </a>
            <div class="card-body">
              <small>{{ $product['attributes']['album'] ?? $activeLabel }}</small>
              <h3>{{ $product['name'] }}</h3>
              @if(!empty($product['attributes']['code']))<div class="card-unit">کد محصول: {{ $product['attributes']['code'] }}</div>@endif
              <div class="card-unit">{{ $product['unit'] }}</div>
              <div class="card-price">{{ $product['price'] !== null ? number_format((float)$product['price']).' تومان' : 'استعلام قیمت' }}</div>
              <div class="card-actions">
                <a href="{{ route('product',$product['id']) }}">مشاهده</a>
                <a href="{{ route('product',$product['id']) }}">مقایسه</a>
                <a class="buy" href="{{ route('product',$product['id']) }}">انتخاب</a>
              </div>
            </div>
          </article>
        @empty
          <div class="empty-state">محصولی پیدا نشد.</div>
        @endforelse
      </div>
    </main>
  </div>
</div>
</section>

<script>
document.addEventListener('DOMContentLoaded',()=>{
 const cards=[...document.querySelectorAll('.product-item')], pills=[...document.querySelectorAll('.model-pill')];
 const min=document.getElementById('minPrice'),max=document.getElementById('maxPrice'),selected=document.getElementById('selectedModel'),count=document.getElementById('resultCount');
 let model='all';
 function apply(){
   const lo=Number(min.value)||0, hi=Number(max.value)||Infinity;
   let n=0;
   cards.forEach(c=>{const ok=(model==='all'||c.dataset.model===model)&&(+c.dataset.price>=lo&&+c.dataset.price<=hi);c.style.display=ok?'block':'none';if(ok)n++;});
   count.textContent=n.toLocaleString('fa-IR')+' محصول';
 }
 pills.forEach(p=>p.addEventListener('click',()=>{model=p.dataset.model;pills.forEach(x=>x.classList.toggle('active',x===p));selected.textContent=p.textContent;apply();}));
 [min,max].forEach(x=>x.addEventListener('input',apply));
});
</script>
@endsection