@extends('layouts.admin')

@section('title','پنل فروش پالاز')

@section('content')
@php
    $grouped = $products->groupBy(fn($p) => $p->category_id);
@endphp

<style>
.sales-page{--red:#9f1820;--ink:#241f1d;--muted:#756d68;--line:#ebe5e0;background:#f7f4f1;margin:-28px -30px;padding:24px;min-height:calc(100vh - 72px)}
.sales-top{display:flex;gap:14px;align-items:center;margin-bottom:16px}
.sales-search{flex:1;background:#fff;border:1px solid var(--line);border-radius:16px;padding:14px 18px;font-size:15px}
.sales-layout{display:grid;grid-template-columns:minmax(0,1fr) 360px;gap:18px}
.sales-browser,.sales-cart{background:#fff;border:1px solid var(--line);border-radius:20px}
.sales-browser{padding:18px;min-width:0}
.sales-cart{padding:18px;position:sticky;top:16px;height:max-content}
.sales-cats{display:flex;gap:8px;overflow:auto;padding-bottom:8px;margin-bottom:8px}
.sales-cat{border:1px solid var(--line);background:#fff;border-radius:12px;padding:10px 16px;white-space:nowrap;font-weight:700;cursor:pointer}
.sales-cat.active{background:var(--red);color:#fff;border-color:var(--red)}
.model-strip{display:flex;gap:8px;overflow-x:auto;padding:10px 0 16px;border-bottom:1px solid var(--line);margin-bottom:16px}
.model-chip{border:1px solid #ddd4ce;background:#faf8f6;border-radius:999px;padding:9px 16px;white-space:nowrap;cursor:pointer}
.model-chip.active{background:#241f1d;color:#fff;border-color:#241f1d}
.sales-filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
.sales-filters input{max-width:150px}
.sales-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.sales-card{border:1px solid var(--line);border-radius:16px;overflow:hidden;background:#fff}
.sales-photo{height:145px;background:#f1ece8;display:grid;place-items:center;overflow:hidden}
.sales-photo img{width:100%;height:100%;object-fit:cover}
.sales-photo span{font-size:12px;color:#9a9089}
.sales-card-body{padding:12px}
.sales-card h3{font-size:14px;margin:0 0 5px;font-weight:800}
.sales-code{font-size:12px;color:var(--muted)}
.sales-price{font-weight:900;margin-top:9px}
.sales-unit{font-size:11px;color:var(--muted)}
.sales-add{width:100%;margin-top:10px;border:0;border-radius:10px;background:var(--red);color:#fff;padding:9px;font-weight:700}
.cart-empty{color:var(--muted);padding:30px 5px;text-align:center}
.cart-item{border-bottom:1px solid var(--line);padding:12px 0}
.cart-item-top{display:flex;justify-content:space-between;gap:8px}
.qty{display:flex;align-items:center;gap:8px;margin-top:8px}
.qty button{width:30px;height:30px;border:1px solid var(--line);background:#fff;border-radius:8px}
.cart-total{display:flex;justify-content:space-between;font-weight:900;font-size:18px;padding-top:16px}
@media(max-width:1100px){.sales-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.sales-layout{grid-template-columns:1fr}.sales-cart{position:static}}
@media(max-width:700px){.sales-page{margin:-14px;padding:14px}.sales-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.sales-photo{height:120px}}
</style>

<div class="sales-page">
    <div class="sales-top">
        <form class="d-flex gap-2 flex-grow-1" method="get">
            <input class="sales-search" name="q" value="{{ request('q') }}" placeholder="جستجوی سریع نام، مدل یا کد محصول...">
            <button class="btn btn-palaz px-4">جستجو</button>
        </form>
    </div>

    <div class="sales-layout">
        <section class="sales-browser">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h4 fw-bold mb-1">انتخاب محصول</h1>
                    <div class="small text-secondary">دسته ← مدل ← کد محصول</div>
                </div>
                <span class="badge text-bg-light">{{ $products->count() }} محصول فعال</span>
            </div>

            <div class="sales-cats" id="salesCats">
                <button class="sales-cat active" data-category="all">همه</button>
                @foreach($categories as $category)
                    @if($grouped->has($category->id))
                        <button class="sales-cat" data-category="{{ $category->id }}">{{ $category->name }}</button>
                    @endif
                @endforeach
            </div>

            <div class="model-strip" id="modelStrip">
                <button class="model-chip active" data-model="all">همه مدل‌ها</button>
                @foreach($products->groupBy('category_id') as $categoryId => $categoryProducts)
                    @foreach($categoryProducts->groupBy('name') as $model => $modelProducts)
                        <button class="model-chip d-none" data-category="{{ $categoryId }}" data-model="{{ md5($model) }}">{{ $model }}</button>
                    @endforeach
                @endforeach
            </div>

            <div class="sales-filters">
                <input id="minPrice" type="number" class="form-control" placeholder="حداقل قیمت">
                <input id="maxPrice" type="number" class="form-control" placeholder="حداکثر قیمت">
                <button id="clearFilters" class="btn btn-light">پاک کردن فیلتر</button>
            </div>

            <div class="sales-grid" id="salesGrid">
                @foreach($products as $product)
                    @php
                        $cover = $product->media->firstWhere('is_cover', true) ?? $product->media->first();
                        $price = (float) ($product->price ?? 0);
                        $modelKey = md5($product->name);
                        $rule = $product->pricingRule;
                        $stock = $rule?->calculation_type === 'roll'
                            ? $product->inventoryRolls->sum('quantity')
                            : null;
                    @endphp
                    <article class="sales-card product-card"
                             data-category="{{ $product->category_id }}"
                             data-model="{{ $modelKey }}"
                             data-price="{{ $price }}"
                             data-name="{{ e($product->name) }}"
                             data-code="{{ e($product->slug) }}"
                             data-product-id="{{ $product->id }}"
                             data-unit="{{ e($product->unit) }}"
                             data-calculation="{{ e($rule?->calculation_type ?? 'fixed') }}"
                             data-package="{{ e($product->attributes['package_factor'] ?? '') }}">
                        <div class="sales-photo">
                            @if($cover)
                                <img src="{{ str_starts_with($cover->path, 'http') ? $cover->path : asset($cover->path) }}" alt="{{ $cover->alt ?: $product->name }}" loading="lazy">
                            @else
                                <span>تصویر محصول</span>
                            @endif
                        </div>
                        <div class="sales-card-body">
                            <h3>{{ $product->name }}</h3>
                            <div class="sales-code">کد: {{ $product->slug }}</div>
                            <div class="sales-price">{{ $price ? number_format($price) . ' تومان' : 'تماس' }}</div>
                            <div class="sales-unit">{{ $product->unit }}</div>
                            @if($stock !== null)
                                <div class="small text-secondary mt-1">موجودی: {{ number_format($stock) }} طاقه</div>
                            @endif
                            <button class="sales-add add-product">افزودن به فروش</button>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <aside class="sales-cart">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="h5 fw-bold mb-0">سبد فروش</h2>
                <span id="cartCount" class="badge text-bg-dark">۰</span>
            </div>
            <div id="cartItems"><div class="cart-empty">هنوز محصولی انتخاب نشده است.</div></div>
            <div class="cart-total"><span>جمع</span><span id="cartTotal">۰ تومان</span></div>
            <button class="btn btn-palaz w-100 mt-3 py-3" id="submitSale">ثبت سفارش</button>
        </aside>
    </div>
</div>

<script>
(() => {
    const cats=[...document.querySelectorAll('.sales-cat')];
    const models=[...document.querySelectorAll('.model-chip')];
    const cards=[...document.querySelectorAll('.product-card')];
    const min=document.getElementById('minPrice'), max=document.getElementById('maxPrice');
    let category='all', model='all', cart=[];

    function refreshModels(){
        models.forEach(m=>{
            if(m.dataset.model==='all'){m.classList.toggle('d-none',false); return;}
            m.classList.toggle('d-none', category!=='all' && m.dataset.category!==category);
        });
        const active=models.find(m=>m.dataset.model===model);
        if(active && !active.classList.contains('d-none')) return;
        model='all'; models.forEach(m=>m.classList.toggle('active',m.dataset.model==='all'));
    }

    function filter(){
        const lo=parseFloat(min.value)||0, hi=parseFloat(max.value)||Infinity;
        cards.forEach(c=>{
            const okCat=category==='all'||c.dataset.category===category;
            const okModel=model==='all'||c.dataset.model===model;
            const p=parseFloat(c.dataset.price)||0;
            c.style.display=okCat&&okModel&&p>=lo&&p<=hi?'block':'none';
        });
        refreshModels();
    }

    cats.forEach(c=>c.addEventListener('click',()=>{
        category=c.dataset.category;
        cats.forEach(x=>x.classList.toggle('active',x===c));
        filter();
    }));
    models.forEach(m=>m.addEventListener('click',()=>{
        model=m.dataset.model;
        models.forEach(x=>x.classList.toggle('active',x===m));
        filter();
    }));
    [min,max].forEach(x=>x.addEventListener('input',filter));
    document.getElementById('clearFilters').onclick=()=>{min.value='';max.value='';category='all';model='all';cats.forEach(x=>x.classList.toggle('active',x.dataset.category==='all'));models.forEach(x=>x.classList.toggle('active',x.dataset.model==='all'));filter();};

    function renderCart(){
        const box=document.getElementById('cartItems');
        if(!cart.length){box.innerHTML='<div class="cart-empty">هنوز محصولی انتخاب نشده است.</div>';return;}
        box.innerHTML=cart.map((x,i)=>'<div class="cart-item"><div class="cart-item-top"><strong>'+x.name+'</strong><span>'+Number(x.price*x.qty).toLocaleString('fa-IR')+'</span></div><div class="small text-secondary">کد '+x.code+'</div><div class="qty"><button onclick="window.salesQty('+i+',-1)">−</button><strong>'+x.qty+'</strong><button onclick="window.salesQty('+i+',1)">+</button><span class="small text-secondary">'+x.unit+'</span></div></div>').join('');
        document.getElementById('cartCount').textContent=cart.reduce((s,x)=>s+x.qty,0).toLocaleString('fa-IR');
        document.getElementById('cartTotal').textContent=cart.reduce((s,x)=>s+x.price*x.qty,0).toLocaleString('fa-IR')+' تومان';
    }
    window.salesQty=(i,d)=>{cart[i].qty+=d;if(cart[i].qty<=0)cart.splice(i,1);renderCart();};
    document.querySelectorAll('.add-product').forEach(btn=>btn.addEventListener('click',()=>{
        const c=btn.closest('.product-card'), id=c.dataset.productId;
        const found=cart.find(x=>x.id===id);
        if(found)found.qty++; else cart.push({id,name:c.dataset.name,code:c.dataset.code,price:parseFloat(c.dataset.price)||0,unit:c.dataset.unit,qty:1});
        renderCart();
    }));
    document.getElementById('submitSale').onclick=()=>alert('مرحله بعد: اتصال این سبد به ثبت سفارش و محاسبه تخصصی هر واحد فروش.');
    refreshModels();
})();
</script>
@endsection
