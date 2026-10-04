@extends('layouts.admin')
@section('title','ایجنت داده و مدیریت فروش')
@section('content')
<div class="d-flex justify-content-between align-items-end gap-3 flex-wrap mb-4">
<div><h1 class="h3 fw-bold mb-2">ایجنت داده و مدیریت فروش</h1><p class="text-secondary mb-0">تحلیل فروش، قیمت و موجودی از داده‌های واقعی فروشگاه.</p></div>
<form class="d-flex gap-2 flex-wrap" method="get">
<input class="form-control" type="date" name="from" value="{{ $data['from'] }}">
<input class="form-control" type="date" name="to" value="{{ $data['to'] }}">
<button class="btn btn-palaz px-4">تحلیل</button>
</form></div>
<div class="row g-3 mb-4">
@foreach([['فروش ثبت‌شده',number_format($data['orders'])],['تعداد اقلام فروخته‌شده',number_format($data['sales_quantity'])],['فروش ریالی',number_format($data['sales_revenue']).' تومان'],['موجودی طاقه',number_format($data['inventory_rolls'])],['موجودی مترمربع',number_format($data['inventory_area']).' m²'],['محصول فعال',number_format($data['active_products'])]] as $stat)
<div class="col-6 col-xl-2"><div class="card stat h-100"><small>{{ $stat[0] }}</small><strong style="font-size:22px">{{ $stat[1] }}</strong></div></div>@endforeach
</div>
<div class="row g-3">
<div class="col-xl-7"><div class="card p-4 h-100"><h2 class="h5 fw-bold mb-3">محصولات پرفروش</h2>
@if($data['top_products']->isEmpty())<p class="text-secondary mb-0">در این بازه هنوز داده فروش ثبت نشده است.</p>
@else<div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>محصول</th><th>تعداد</th><th>فروش</th></tr></thead><tbody>
@foreach($data['top_products'] as $item)<tr><td class="fw-semibold">{{ $item['name'] }}</td><td>{{ number_format($item['quantity']) }}</td><td>{{ number_format($item['revenue']) }} تومان</td></tr>@endforeach
</tbody></table></div>@endif</div></div>
<div class="col-xl-5"><div class="card p-4 h-100"><h2 class="h5 fw-bold mb-3">هشدار موجودی</h2>
@if($data['low_stock']->isEmpty())<p class="text-secondary mb-0">مورد کم‌موجودی ثبت نشده است.</p>
@else@foreach($data['low_stock'] as $item)<div class="d-flex justify-content-between border-bottom py-2"><span>{{ $item['name'] }}</span><strong>{{ number_format($item['roll_count']) }} طاقه</strong></div>@endforeach@endif
</div></div>
<div class="col-12"><div class="card p-4"><h2 class="h5 fw-bold mb-3">وضعیت موجودی محصولات</h2>
<div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>محصول</th><th>دسته</th><th>قیمت</th><th>طاقه</th><th>مساحت</th><th>وضعیت</th></tr></thead><tbody>
@foreach($data['inventory'] as $item)<tr><td class="fw-semibold">{{ $item['name'] }}</td><td>{{ $item['category'] }}</td><td>{{ number_format($item['price']) }}</td><td>{{ $item['is_roll'] ? number_format($item['roll_count']) : '—' }}</td><td>{{ $item['is_roll'] ? number_format($item['area']).' m²' : '—' }}</td><td>{!! $item['low'] ? '<span class="badge text-bg-warning">رو به اتمام</span>' : '<span class="badge text-bg-light">عادی</span>' !!}</td></tr>@endforeach
</tbody></table></div></div></div></div>
@endsection
