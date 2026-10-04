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
        <div class="install-step active">۱. فضا و ابعاد</div><div class="install-step">۲. کف و چسب</div><div class="install-step">۳. کارهای جانبی</div><div class="install-step">۴. موارد خاص</div><div class="install-step">۵. محل نصب</div><div class="install-step">۶. بازبینی</div>
    </div>

    <form method="POST" action="{{ route('account.installation.complete', ['order' => $order->id]) }}" id="installationForm">
@csrf
<section class="install-card panel active" data-step="1">
<h2>۱. فضا و ابعاد</h2><div class="notice">طاقه‌های خریداری‌شده قفل هستند؛ فقط فضاهای محل نصب را مشخص کنید.</div>
<div id="spaces"></div>
<button type="button" class="btn btn-light" id="addSpace">+ افزودن فضا</button>
<div class="quote"><span>متراژ خریداری‌شده</span><strong>{{ number_format($purchasedArea,2) }} مترمربع</strong></div>
<div class="actions"><span></span><button type="button" class="btn btn-primary next">ادامه</button></div></section>
<section class="install-card panel" data-step="2"><h2>۲. کف و چسب</h2>
<div class="form-grid"><label class="field"><span>نوع کف فعلی</span><select name="floor_type"><option value="">انتخاب کنید</option><option>بتن</option><option>سرامیک</option><option>سنگ</option><option>کفپوش قدیمی</option><option>سایر</option></select></label>
<label class="field"><span>متراژ کف</span><input type="number" step="0.01" name="floor_area"></label></div>
<div class="field" style="margin-top:16px"><span>نیاز به چسب / زیرسازی</span><div class="choice-row"><label class="choice"><input type="radio" name="glue_needed" value="yes"> دارد</label><label class="choice"><input type="radio" name="glue_needed" value="no"> ندارد</label></div></div>
<div class="actions"><button type="button" class="btn btn-light prev">بازگشت</button><button type="button" class="btn btn-primary next">ادامه</button></div></section>
<section class="install-card panel" data-step="3"><h2>۳. کارهای جانبی</h2>
<div class="choice-row"><label class="choice"><input type="checkbox" name="side_work[]" value="جابجایی وسایل"> جابجایی وسایل</label><label class="choice"><input type="checkbox" name="side_work[]" value="جمع‌آوری کف قبلی"> جمع‌آوری کف قبلی</label><label class="choice"><input type="checkbox" name="side_work[]" value="زیرسازی"> زیرسازی</label></div>
<div class="actions"><button type="button" class="btn btn-light prev">بازگشت</button><button type="button" class="btn btn-primary next">ادامه</button></div></section>
<section class="install-card panel" data-step="4"><h2>۴. موارد خاص</h2>
<div class="field"><span>توضیحات یا شرایط خاص</span><textarea name="special_notes" placeholder="پله، قرنیز، دسترسی، وسایل خاص و..."></textarea></div>
<div class="actions"><button type="button" class="btn btn-light prev">بازگشت</button><button type="button" class="btn btn-primary next">ادامه</button></div></section>
<section class="install-card panel" data-step="5"><h2>۵. محل نصب</h2>
<div class="form-grid"><label class="field full"><span>آدرس نصب</span><textarea name="installation_address" required>{{ $order->address }}</textarea></label>
<label class="field"><span>طبقه</span><input name="floor"></label><div class="field"><span>آسانسور</span><div class="choice-row"><label class="choice"><input type="radio" name="elevator" value="yes" required> دارد</label><label class="choice"><input type="radio" name="elevator" value="no"> ندارد</label></div></div>
<label class="field"><span>نماینده محل</span><input name="site_contact"></label></div>
<div class="field" style="margin-top:16px"><span>اندازه‌گیری</span><div class="choice-row"><label class="choice"><input type="radio" name="measurement" value="not_needed" required> نیاز نیست</label><label class="choice"><input type="radio" name="measurement" value="needed"> نیاز به اندازه‌گیری</label></div></div>
<div class="form-grid" style="margin-top:16px"><label class="field"><span>تاریخ پیشنهادی</span><input name="preferred_date"></label><label class="field"><span>زمان پیشنهادی</span><select name="preferred_time"><option value="">انتخاب</option><option>صبح</option><option>ظهر</option><option>عصر</option></select></label></div>
<div class="actions"><button type="button" class="btn btn-light prev">بازگشت</button><button type="button" class="btn btn-primary next">بازبینی</button></div></section>
<section class="install-card panel" data-step="6"><h2>۶. بازبینی و تأیید</h2>
<div class="notice"><div>سفارش: <b>{{ $order->tracking_code }}</b></div><div>متراژ خرید: <b>{{ number_format($purchasedArea,2) }} مترمربع</b></div><div>هزینه نصب: <b>{{ number_format($installationAmount) }} ریال</b></div></div>
<div class="notice">اطلاعات انتخاب‌شده در پنج مرحله قبل همراه سفارش برای واحد نصب پالاز ارسال می‌شود.</div>
<div class="actions"><button type="button" class="btn btn-light prev">بازگشت</button><button type="submit" class="btn btn-primary">تأیید و ثبت درخواست</button></div></section>
</form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const panels = [...document.querySelectorAll('.panel')];
    const spaces = document.getElementById('spaces');
    document.getElementById('addSpace')?.addEventListener('click', () => { const n=prompt('نام فضا را وارد کنید'); if(!n) return; const row=document.createElement('div'); row.className='field'; row.style.marginBottom='12px'; row.innerHTML='<input type="hidden" name="spaces[][name]" value="'+n.replace(/"/g,'&quot;')+'"><input name="spaces[][area]" type="number" step="0.01" placeholder="متراژ '+n.replace(/"/g,'&quot;')+' (مترمربع)">'; spaces.appendChild(row); });
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
