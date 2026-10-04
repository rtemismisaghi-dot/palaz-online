@extends('layouts.store')

@section('title','خدمات نصب پالاز')

@section('content')
<style>
.install-flow{max-width:1080px;margin:40px auto 80px;padding:0 18px}
.install-hero{background:linear-gradient(135deg,#181818,#303030);color:#fff;border-radius:24px;padding:30px}
.install-hero h1{margin:0 0 8px;font-size:30px}
.install-hero p{margin:0;color:#ddd}
.install-steps{display:flex;gap:8px;margin:18px 0;overflow:auto}
.install-step{flex:1;min-width:150px;padding:14px;border:1px solid #e5e5e5;border-radius:14px;background:#fff;color:#777;text-align:center}
.install-step.active{border-color:#b51f2a;color:#b51f2a;font-weight:700}
.install-card{background:#fff;border:1px solid #e9e9e9;border-radius:20px;padding:24px;margin-top:16px;box-shadow:0 10px 30px rgba(0,0,0,.05)}
.install-card h2{margin-top:0;font-size:21px}
.rolls{display:grid;grid-template-columns:repeat(auto-fit,minmax(190px,1fr));gap:12px}
.roll{border:1px solid #eee;border-radius:14px;padding:16px;background:#fafafa}
.roll b{display:block;font-size:17px;margin-bottom:6px}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.field{display:flex;flex-direction:column;gap:7px}
.field.full{grid-column:1/-1}
.field input,.field textarea,.field select{width:100%;box-sizing:border-box;border:1px solid #ddd;border-radius:12px;padding:13px;font:inherit;background:#fff}
.field textarea{min-height:110px;resize:vertical}
.choice-row{display:flex;gap:12px;flex-wrap:wrap}
.choice{border:1px solid #ddd;border-radius:13px;padding:13px 18px;cursor:pointer}
.choice:has(input:checked){border-color:#b51f2a;background:#fff7f7}
.choice input{margin-left:7px}
.quote{display:flex;justify-content:space-between;gap:20px;align-items:center;border-top:1px solid #eee;margin-top:20px;padding-top:20px}
.quote strong{font-size:24px;color:#b51f2a}
.actions{display:flex;justify-content:space-between;gap:12px;margin-top:22px}
.btn{border:0;border-radius:13px;padding:13px 25px;font:inherit;font-weight:700;cursor:pointer}
.btn-primary{background:#b51f2a;color:#fff}
.btn-light{background:#f3f3f3;color:#222}
.panel{display:none}.panel.active{display:block}
.notice{padding:14px;border-radius:12px;background:#f7f7f7;margin-bottom:14px}
.success{background:#eff9f1;border:1px solid #cfe8d2;padding:18px;border-radius:16px}
@media(max-width:700px){.form-grid{grid-template-columns:1fr}.field.full{grid-column:auto}.install-hero h1{font-size:24px}.quote{align-items:flex-start;flex-direction:column}}
</style>

<div class="install-flow">
    <div class="install-hero">
        <h1>مسیر نصب سفارش شما</h1>
        <p>این مسیر در پالاز آنلاین انجام می‌شود؛ اطلاعات خرید شما از قبل ثبت شده و دوباره نیازی به انتخاب طاقه‌ها نیست.</p>
    </div>

    @if(session('installation_completed'))
        <div class="install-card success">
            <h2>اطلاعات نصب ثبت شد ✓</h2>
            <p>درخواست شما برای هماهنگی اجرا به واحد نصب پالاز ارسال شد.</p>
            @if(session('installation_tracking'))
                <p><b>کد پیگیری نصب:</b> {{ session('installation_tracking') }}</p>
            @endif
            @if(session('installation_amount') !== null)
                <p><b>هزینه نصب:</b> {{ number_format((float) session('installation_amount')) }} ریال</p>
            @endif
        </div>
    @endif

    <div class="install-steps">
        <div class="install-step active" data-step-label="1">۱. سفارش خریداری‌شده</div>
        <div class="install-step" data-step-label="2">۲. محل نصب</div>
        <div class="install-step" data-step-label="3">۳. هماهنگی اجرا</div>
        <div class="install-step" data-step-label="4">۴. تأیید نهایی</div>
    </div>

    <form method="POST" action="{{ route('account.installation.complete', ['order' => $order->id]) }}" id="installationForm">
        @csrf
        <section class="install-card panel active" data-step="1">
            <h2>۱. اطلاعات خرید</h2>
            <div class="notice">متراژ نصب از طاقه‌های خریداری‌شده محاسبه شده و قابل تغییر نیست.</div>
            <div class="rolls">
                @forelse($rolls as $roll)
                    <div class="roll">
                        <b>{{ $roll['name'] ?? 'موکت پالاز' }}</b>
                        <div>کد: {{ $roll['code'] ?? '—' }}</div>
                        <div>طاقه: {{ $roll['width'] ?? 3 }} × {{ $roll['length'] ?? 0 }} متر</div>
                        <div>تعداد: {{ $roll['quantity'] ?? 1 }}</div>
                        <div>متراژ: {{ number_format((float)($roll['area'] ?? 0), 2) }} مترمربع</div>
                    </div>
                @empty
                    <div class="notice">اطلاعات طاقه‌های خریداری‌شده در دسترس نیست.</div>
                @endforelse
            </div>
            <div class="quote">
                <span>متراژ قطعی سفارش</span>
                <strong>{{ number_format($purchasedArea, 2) }} مترمربع</strong>
            </div>
            <div class="actions"><span></span><button type="button" class="btn btn-primary next">ادامه</button></div>
        </section>

        <section class="install-card panel" data-step="2">
            <h2>۲. محل نصب</h2>
            <div class="form-grid">
                <label class="field full"><span>آدرس نصب</span><textarea name="installation_address" required>{{ $order->address }}</textarea></label>
                <label class="field"><span>طبقه</span><input name="floor" placeholder="مثلاً ۳"></label>
                <div class="field"><span>آسانسور</span><div class="choice-row">
                    <label class="choice"><input type="radio" name="elevator" value="yes" required> دارد</label>
                    <label class="choice"><input type="radio" name="elevator" value="no"> ندارد</label>
                </div></div>
                <label class="field"><span>نماینده محل (اختیاری)</span><input name="site_contact" placeholder="نام و شماره تماس"></label>
            </div>
            <div class="actions"><button type="button" class="btn btn-light prev">بازگشت</button><button type="button" class="btn btn-primary next">ادامه</button></div>
        </section>

        <section class="install-card panel" data-step="3">
            <h2>۳. هماهنگی اجرا</h2>
            <div class="field">
                <span>وضعیت اندازه‌گیری</span>
                <div class="choice-row">
                    <label class="choice"><input type="radio" name="measurement" value="not_needed" required> متراژ خریداری‌شده مبنا باشد</label>
                    <label class="choice"><input type="radio" name="measurement" value="needed"> درخواست اندازه‌گیری</label>
                </div>
            </div>
            <div class="form-grid" style="margin-top:18px">
                <label class="field"><span>تاریخ پیشنهادی اجرا</span><input name="preferred_date" placeholder="مثلاً شنبه ۱۲ مهر"></label>
                <label class="field"><span>بازه زمانی پیشنهادی</span><select name="preferred_time"><option value="">انتخاب کنید</option><option>صبح</option><option>ظهر</option><option>عصر</option></select></label>
                <label class="field full"><span>توضیحات تکمیلی</span><textarea name="notes" placeholder="توضیحات دسترسی، شرایط محل یا نکته‌ای برای تیم اجرا..."></textarea></label>
            </div>
            <div class="actions"><button type="button" class="btn btn-light prev">بازگشت</button><button type="button" class="btn btn-primary next">ادامه</button></div>
        </section>

        <section class="install-card panel" data-step="4">
            <h2>۴. بررسی و تأیید</h2>
            <div class="notice">
                <div><b>کد سفارش:</b> {{ $order->tracking_code }}</div>
                <div><b>کد پیگیری نصب:</b> {{ $trackingCode }}</div>
                <div><b>متراژ نصب:</b> {{ number_format($purchasedArea, 2) }} مترمربع</div>
                <div><b>هزینه نصب:</b> {{ number_format($installationAmount) }} ریال</div>
            </div>
            <div class="notice">با تأیید، اطلاعات شما برای هماهنگی اجرا به سامانه نصب پالاز ارسال می‌شود.</div>
            <div class="actions"><button type="button" class="btn btn-light prev">بازگشت</button><button type="submit" class="btn btn-primary">تأیید و ثبت درخواست نصب</button></div>
        </section>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const panels = [...document.querySelectorAll('.panel')];
    const steps = [...document.querySelectorAll('.install-step')];
    let current = 0;

    function show(index) {
        current = Math.max(0, Math.min(panels.length - 1, index));
        panels.forEach((p,i)=>p.classList.toggle('active', i===current));
        steps.forEach((s,i)=>s.classList.toggle('active', i===current));
        window.scrollTo({top:0,behavior:'smooth'});
    }

    document.querySelectorAll('.next').forEach(btn => btn.addEventListener('click', () => {
        const panel = panels[current];
        const required = [...panel.querySelectorAll('[required]')];
        for (const el of required) {
            if (!el.checkValidity()) { el.reportValidity(); return; }
        }
        show(current + 1);
    }));
    document.querySelectorAll('.prev').forEach(btn => btn.addEventListener('click', () => show(current - 1)));
});
</script>
@endsection
