@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>محصولات</h1>
        <p class="text-secondary mb-0">کاتالوگ واقعی از این پنل کنترل می‌شود؛ داده‌ها در پایگاه‌داده نگهداری می‌شوند.</p>
    </div>
    <a class="btn btn-palaz" href="{{ route('admin.products.create') }}">+ محصول جدید</a>
</div>

<form class="card p-3 mb-3" method="get">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label class="form-label">جستجو</label>
            <input name="q" class="form-control" value="{{ request('q') }}" placeholder="نام یا کد محصول">
        </div>
        <div class="col-md-3">
            <label class="form-label">دسته</label>
            <select name="category_id" class="form-select">
                <option value="">همه</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">وضعیت</label>
            <select name="status" class="form-select">
                <option value="">همه</option>
                <option value="active" @selected(request('status') === 'active')>فعال</option>
                <option value="inactive" @selected(request('status') === 'inactive')>غیرفعال</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">مرتب‌سازی</label>
            <select name="sort" class="form-select">
                <option value="id">جدیدترین</option>
                <option value="name" @selected(request('sort') === 'name')>نام</option>
                <option value="price" @selected(request('sort') === 'price')>قیمت</option>
            </select>
        </div>
        <div class="col-md-1">
            <button class="btn btn-outline-dark w-100">اعمال</button>
        </div>
    </div>
</form>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>تصویر</th><th>محصول</th><th>دسته</th><th>قیمت</th><th>محاسبه</th><th>وضعیت</th><th></th></tr></thead>
            <tbody>
            @forelse($products as $product)
                <tr>
                    <td style="width:80px">@if($product->media->firstWhere('is_cover', true) ?? $product->media->first())<img src="{{ asset('storage/'.(($product->media->firstWhere('is_cover', true) ?? $product->media->first())->path)) }}" class="rounded-3" style="width:64px;height:64px;object-fit:cover">@else<span class="text-secondary small">بدون عکس</span>@endif</td>
                    <td>{{ $product->name }}<small class="d-block text-secondary">{{ $product->slug }}</small></td>
                    <td>{{ $product->category?->name }}</td>
                    <td>{{ $product->price !== null ? number_format($product->price) : 'تماس' }}<small class="d-block text-secondary">{{ $product->unit }}</small></td>
                    <td>{{ $product->pricingRule?->calculation_type ?? 'ثبت نشده' }} @if($product->pricingRule) · {{ $product->pricingRule->waste_percent }}٪ @endif</td>
                    <td>{{ $product->is_active ? 'فعال' : 'غیرفعال' }}</td>
                    <td class="text-nowrap">
                        <a class="me-2" href="{{ route('admin.products.edit',$product) }}">ویرایش</a>
                        <form class="d-inline" method="post" action="{{ route('admin.products.destroy',$product) }}" onsubmit="return confirm('این محصول حذف شود؟')">
                            @csrf @method('DELETE')
                            <button class="btn btn-link text-danger p-0">حذف</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center py-5">محصولی پیدا نشد.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="p-3">{{ $products->links() }}</div>
</div>
@endsection
