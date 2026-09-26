@extends('layouts.store')

@section('content')
<main class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card p-4 p-lg-5">
                <span class="text-danger fw-bold">CALCULATOR</span>
                <h1 class="mt-2">{{ $product->name }}</h1>
                <p class="text-secondary">{{ $product->description }}</p>

                @if($product->pricingRule?->calculation_type === 'roll')
                    <div class="alert alert-light border mb-4">
                        <strong>فروش طاقه عرض ۳ متر</strong>
                        <div class="small text-secondary mt-1">فعلاً طول هر طاقه از ۱ تا ۱۵ متر قابل انتخاب است. عرض ۴ متر بعداً اضافه می‌شود.</div>
                    </div>

                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">طول طاقه</label>
                            <select id="length" class="form-select form-select-lg">
                                @for($length = 1; $length <= 15; $length++)
                                    <option value="{{ $length }}">{{ $length }} متر</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">تعداد طاقه</label>
                            <input id="quantity" type="number" min="1" max="1000" step="1" value="1" class="form-control form-control-lg">
                        </div>
                        <div class="col-md-3">
                            <button id="calculate" class="btn btn-dark btn-lg w-100">محاسبه</button>
                        </div>
                    </div>
                @else
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">مقدار مورد نیاز</label>
                            <input id="quantity" type="number" min=".01" step=".01" value="1" class="form-control form-control-lg">
                        </div>
                        <div class="col-md-6">
                            <button id="calculate" class="btn btn-dark btn-lg w-100">محاسبه قیمت</button>
                        </div>
                    </div>
                @endif

                <div id="error" class="alert alert-danger mt-4 d-none"></div>

                <div id="result" class="mt-4 p-4 rounded-4 bg-light d-none">
                    @if($product->pricingRule?->calculation_type === 'roll')
                        <div>ابعاد طاقه: <strong id="dimensions"></strong></div>
                        <div>متراژ هر طاقه: <strong id="areaPerRoll"></strong> مترمربع</div>
                        <div>تعداد طاقه: <strong id="rollCount"></strong></div>
                    @endif
                    <div>مقدار قابل محاسبه: <strong id="bq"></strong></div>
                    <div>پرت: <strong id="w"></strong>%</div>
                    <div class="fs-3 mt-2">قیمت نهایی: <strong id="total"></strong></div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.getElementById('calculate').addEventListener('click', async () => {
    const quantity = document.getElementById('quantity').value;
    const payload = { quantity };

    @if($product->pricingRule?->calculation_type === 'roll')
        payload.length = document.getElementById('length').value;
    @endif

    const errorBox = document.getElementById('error');
    const result = document.getElementById('result');
    errorBox.classList.add('d-none');
    result.classList.add('d-none');

    const r = await fetch('{{ route('calculator.calculate', $product->slug) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    });

    const d = await r.json();

    if (!r.ok) {
        errorBox.textContent = Object.values(d.errors ?? {}).flat()[0] ?? 'اطلاعات واردشده صحیح نیست.';
        errorBox.classList.remove('d-none');
        return;
    }

    result.classList.remove('d-none');

    @if($product->pricingRule?->calculation_type === 'roll')
        document.getElementById('dimensions').textContent = d.width + ' × ' + d.length + ' متر';
        document.getElementById('areaPerRoll').textContent = d.area_per_roll;
        document.getElementById('rollCount').textContent = d.roll_count;
    @endif

    document.getElementById('bq').textContent = d.billable_quantity + ' {{ $product->pricingRule?->unit ?? '' }}';
    document.getElementById('w').textContent = d.waste_percent;
    document.getElementById('total').textContent = new Intl.NumberFormat('fa-IR').format(d.total) + ' تومان';
});
</script>
@endsection
