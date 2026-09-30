@extends('layouts.admin')

@section('title','داشبورد مدیریت')

@section('content')
<div class="mb-4">
    <h1 class="h3 fw-bold mb-2">کنسول مدیریت پالاز</h1>
    <p class="text-secondary mb-0">مرکز کنترل کاتالوگ، محصولات و قیمت‌گذاری فروشگاه.</p>
</div>

<div class="row g-3">
@foreach($stats as $key=>$value)
    <div class="col-6 col-xl-3">
        <div class="card stat h-100">
            <small>{{ ['categories'=>'دسته‌بندی‌ها','products'=>'محصولات','orders'=>'سفارش‌ها','services'=>'درخواست خدمات'][$key] }}</small>
            <strong>{{ number_format($value) }}</strong>
        </div>
    </div>
@endforeach
</div>

<div class="row g-3 mt-2">
    <div class="col-lg-8">
        <div class="card p-4 h-100">
            <h2 class="h5 fw-bold mb-2">مدیریت کاتالوگ</h2>
            <p class="text-secondary">محصولات واقعی پالاز از این بخش وارد، اصلاح، فعال/غیرفعال و برای قیمت‌گذاری آماده می‌شوند.</p>
            <div class="d-flex gap-2 flex-wrap">
                <a class="btn btn-palaz px-4" href="{{ route('admin.products.index') }}">مدیریت محصولات</a>
                <a class="btn btn-light border px-4" href="{{ route('admin.categories.index') }}">دسته‌بندی‌ها</a>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card p-4 h-100">
            <h2 class="h5 fw-bold mb-3">مرحله بعد</h2>
            <div class="text-secondary" style="line-height:2">
                <div>✓ ساختار دیتابیس محصولات</div>
                <div>✓ مدیریت قیمت و محاسبه</div>
                <div>✓ مدیریت دسته‌بندی</div>
                <div>→ ورود کاتالوگ پالاز</div>
            </div>
        </div>
    </div>
</div>
@endsection
