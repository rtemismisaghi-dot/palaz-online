@extends('layouts.admin')

@section('content')
<h1 class="mb-4">{{ $product->exists ? 'ویرایش محصول' : 'محصول جدید' }}</h1>

<form method="post" action="{{ $product->exists ? route('admin.products.update',$product) : route('admin.products.store') }}" class="card p-4">
    @csrf @if($product->exists) @method('PUT') @endif
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label">نام محصول / مدل</label><input name="name" class="form-control" value="{{ old('name',$product->name) }}" required></div>
        <div class="col-md-6"><label class="form-label">دسته</label><select name="category_id" class="form-select" required>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$product->category_id)==$category->id)>{{ $category->name }}</option>@endforeach</select></div>
        <div class="col-md-6"><label class="form-label">Slug / کد</label><input name="slug" class="form-control" value="{{ old('slug',$product->slug) }}" required></div>
        <div class="col-md-3"><label class="form-label">قیمت پایه</label><input type="number" min="0" step="0.01" name="price" class="form-control" value="{{ old('price',$product->price) }}"></div>
        <div class="col-md-3"><label class="form-label">واحد نمایش</label><input name="unit" class="form-control" value="{{ old('unit',$product->unit ?? 'تماس برای قیمت') }}" required></div>
        <div class="col-md-6"><label class="form-label">نوع محاسبه</label><select name="calculation_type" class="form-select"><option value="fixed" @selected(old('calculation_type',$rule->calculation_type)=='fixed')>ثابت / عددی</option><option value="area" @selected(old('calculation_type',$rule->calculation_type)=='area')>بر اساس مترمربع</option><option value="roll" @selected(old('calculation_type',$rule->calculation_type)=='roll')>بر اساس رول / طاقه</option><option value="quantity" @selected(old('calculation_type',$rule->calculation_type)=='quantity')>بر اساس تعداد</option></select></div>
        <div class="col-md-3"><label class="form-label">واحد محاسبه</label><input name="calculation_unit" class="form-control" value="{{ old('calculation_unit',$rule->unit ?? 'item') }}" required></div>
        <div class="col-md-3"><label class="form-label">پرت / ضریب اضافه ٪</label><input type="number" min="0" max="100" step=".01" name="waste_percent" class="form-control" value="{{ old('waste_percent',$rule->waste_percent ?? 0) }}"></div>
        <div class="col-12 mt-2" id="rollInventorySection">
            <div class="border rounded-3 p-3 bg-light">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <strong>موجودی روزانه طاقه موکت</strong>
                        <div class="small text-secondary">عرض ثابت ۳ متر؛ تعداد طاقه برای طول‌های ۱ تا ۱۵ متر را هر روز از همین بخش به‌روزرسانی کنید.</div>
                    </div>
                    <span class="badge text-bg-dark">۳ × ۱ تا ۳ × ۱۵</span>
                </div>
                <div class="row g-2">
                    @foreach(range(1, 15) as $length)
                        @php
                            $roll = $product->inventoryRolls->first(fn($item) => (int) $item->width === 3 && (int) $item->length === $length);
                            $qty = old("roll_stock.$length", $roll?->quantity ?? 0);
                        @endphp
                        <div class="col-6 col-md-4 col-lg-3 col-xl-2">
                            <label class="form-label small mb-1">طاقه ۳ × {{ $length }} متر</label>
                            <input type="number" min="0" max="100000" step="1" name="roll_stock[{{ $length }}]" class="form-control" value="{{ $qty }}">
                        </div>
                    @endforeach
                </div>
                <div class="small text-secondary mt-2">موجودی به‌صورت «تعداد طاقه» ثبت می‌شود؛ متراژ کل از روی همین مقادیر محاسبه خواهد شد.</div>
            </div>
        </div>
        <div class="col-12"><label class="form-label">ویژگی‌های اختصاصی محصول (JSON)</label><textarea name="attributes_json" rows="4" class="form-control" placeholder='{"رنگ":"کرم","ضخامت":"8mm"}'>{{ old('attributes_json', $product->attributes ? json_encode($product->attributes, JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT) : '') }}</textarea><small class="text-secondary">برای ویژگی‌های متفاوت هر دسته استفاده می‌شود؛ ساختار نهایی فرم‌های اختصاصی را بعداً روی همین داده سوار می‌کنیم.</small></div>
        <div class="col-12"><label class="form-label">توضیحات</label><textarea name="description" rows="4" class="form-control">{{ old('description',$product->description) }}</textarea></div>
        <div class="col-md-6 form-check form-switch mx-2"><input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active',$product->exists ? $product->is_active : true) ? 'checked' : '' }}><label class="form-check-label">فعال در فروشگاه</label></div>
        <div class="col-md-5 form-check form-switch mx-2"><input class="form-check-input" type="checkbox" name="is_featured" value="1" {{ old('is_featured',$product->is_featured ?? false) ? 'checked' : '' }}><label class="form-check-label">محصول منتخب</label></div>
    </div>

    @if($product->exists)
    <div class="col-12 mt-4">
        <div class="border rounded-3 p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="mb-1">تصاویر محصول</h5>
                    <div class="small text-secondary">تصویر اصلی در فروشگاه نمایش داده می‌شود. می‌توانید عکس جدید اضافه، عکس اصلی را عوض یا عکس قبلی را حذف کنید.</div>
                </div>
            </div>

            @if($product->media->count())
                <div class="row g-3 mb-4">
                    @foreach($product->media->sortBy('sort_order') as $media)
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="border rounded-3 p-2 h-100">
                                <div class="ratio ratio-1x1 bg-light rounded overflow-hidden mb-2">
                                    <img src="{{ Storage::disk('public')->url($media->path) }}" alt="{{ $media->alt ?: $product->name }}" class="w-100 h-100 object-fit-cover">
                                </div>
                                <div class="d-flex align-items-center justify-content-between gap-2 mb-2">
                                    @if($media->is_cover)
                                        <span class="badge text-bg-dark">تصویر اصلی</span>
                                    @else
                                        <span class="badge text-bg-light">تصویر محصول</span>
                                    @endif
                                </div>
                                <div class="d-flex gap-2">
                                    @unless($media->is_cover)
                                        <form method="post" action="{{ route('admin.products.media.cover', [$product, $media]) }}" class="flex-grow-1">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-primary w-100">اصلی کردن</button>
                                        </form>
                                    @endunless
                                    <form method="post" action="{{ route('admin.products.media.destroy', [$product, $media]) }}" onsubmit="return confirm('این عکس حذف شود؟');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-light border">هنوز تصویری برای این محصول ثبت نشده است.</div>
            @endif

            <form method="post" action="{{ route('admin.products.media.store', $product) }}" enctype="multipart/form-data" class="row g-2 align-items-end">
                @csrf
                <div class="col-md-7">
                    <label class="form-label">افزودن تصویر جدید</label>
                    <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">عنوان تصویر</label>
                    <input type="text" name="alt" class="form-control" value="{{ $product->name }}" maxlength="180">
                </div>
                <div class="col-md-2">
                    <button class="btn btn-palaz w-100">افزودن عکس</button>
                </div>
            </form>
        </div>
    </div>
    @endif

    <div class="mt-4 d-flex gap-2"><button class="btn btn-palaz">ذخیره محصول و قیمت‌گذاری</button><a class="btn btn-light" href="{{ route('admin.products.index') }}">انصراف</a></div>
</form>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const type = document.querySelector('[name="calculation_type"]');
    const section = document.getElementById('rollInventorySection');
    const sync = () => { section.hidden = type.value !== 'roll'; };
    type.addEventListener('change', sync);
    sync();
});
</script>
@endsection
