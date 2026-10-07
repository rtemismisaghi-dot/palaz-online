@extends('layouts.store')

@section('title','محاسبه نصب | PALAZ ONLINE')

@section('content')
<section class="page-head">
    <div class="container">
        <span class="eyebrow">PALAZ ONLINE / INSTALLATION</span>
        <h1>آماده‌سازی نصب</h1>
        <p>منطق محاسبه نصب پالاز در خود فروشگاه اجرا می‌شود و اطلاعات طاقه‌های خریداری‌شده قابل تغییر نیست.</p>
    </div>
</section>

<section class="section">
<div class="container" style="max-width:1080px">
    <div class="install-local-note">
        <strong>محصول خریداری‌شده</strong>
        <span>عرض طاقه و طول و تعداد از سفارش شما خوانده شده است.</span>
    </div>

    <div class="install-grid">
        <div>
            <div class="install-card">
                <div class="install-card-head"><span>۱</span><h2>فضا و ابعاد</h2></div>
                <div id="rolls">
                    @foreach($rolls as $index => $roll)
                    <div class="product-install-card">
                        <div class="product-install-main">
                            <div class="product-install-icon">P</div>
                            <div class="product-install-title">
                                <span>محصول {{ $index + 1 }}</span>
                                <strong>{{ $roll['name'] ?? 'موکت پالاز' }}</strong>
                                <small>{{ $roll['model'] ?? '—' }} · کد {{ $roll['code'] ?? '—' }}</small>
                            </div>
                        </div>
                        <div class="product-install-details">
                            <div><span>عرض</span><b>{{ number_format((float)($roll['width'] ?? 3), 2) }} متر</b></div>
                            <div><span>طول هر طاقه</span><b>{{ number_format((float)($roll['length'] ?? 1), 2) }} متر</b></div>
                            <div><span>تعداد طاقه</span><b>{{ (int)($roll['quantity'] ?? 1) }}</b></div>
                            <div><span>متراژ این محصول</span><b>{{ number_format((float)($roll['area'] ?? 0), 2) }} مترمربع</b></div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="area-total"><span>متراژ کل نصب</span><b id="areaValue">{{ number_format((float)data_get($quote->payload,'purchased_area',0),2) }} مترمربع</b></div>
            </div>

            <div class="install-card">
                <div class="install-card-head"><span>۲</span><h2>کف و چسب</h2></div>
                <div id="floors">
                    <div class="option-row">
                        <label>نوع کف
                            <select class="floor-type">
                                <option value="normal">عادی (سنگ، سرامیک، لمینت، پارکت، موزاییک)</option>
                                <option value="cement">سیمان</option>
                                <option value="moquette">موکت</option>
                            </select>
                        </label>
                        <label class="check"><input type="checkbox" class="normal-floor" checked> کف کاملاً عادی است</label>
                        <label>متراژ دقیق کف (در صورت نیاز)<input type="number" class="floor-meter" min="0" step="0.01" value="0"></label>
                        <button type="button" class="small-btn remove-floor">حذف</button>
                    </div>
                </div>
                <button type="button" class="outline-btn" id="addFloor">+ افزودن کف</button>

                <div id="glues" class="sub-block">
                    <div class="option-row glue-row">
                        <label>نوع چسب
                            <select class="glue-type">
                                <option value="none">بدون چسب</option>
                                <option value="edge">دور چسب</option>
                                <option value="full">تمام چسب</option>
                                <option value="double_25">چسب دوطرف 25 متری</option>
                                <option value="water_soluble">چسب حلال آب</option>
                            </select>
                        </label>
                        <label>تعداد<input type="number" class="glue-count" min="1" value="1"></label>
                        <button type="button" class="small-btn remove-glue">حذف</button>
                    </div>
                </div>
                <button type="button" class="outline-btn" id="addGlue">+ افزودن چسب</button>

                <div class="zero-note">هزینه چسب و کف در DTZ فعلی فرمول فعال ندارد و فعلاً ۰ ریال است.</div>

                <div class="option-row stairs">
                    <label class="check"><input type="radio" name="has_stairs" value="0" checked> پله ندارد</label>
                    <label class="check"><input type="radio" name="has_stairs" value="1"> پله دارد</label>
                    <label>پله صاف<input id="straightStairs" type="number" min="0" value="0"></label>
                    <label>پله لبه‌دار<input id="edgedStairs" type="number" min="0" value="0"></label>
                    <label>پله گردان<input id="turnStairs" type="number" min="0" value="0"></label>
                </div>
            </div>

            <div class="install-card">
                <div class="install-card-head"><span>۳</span><h2>کارهای جانبی</h2></div>
                <div class="option-row">
                    <label>نیاز به گرده ماهی
                        <select id="fishNeed"><option value="yes">بله</option><option value="no">خیر</option></select>
                    </label>
                    <label>نوع<input id="fishType" value="طلایی"></label>
                    <label>طول شاخه<input id="fishLength" type="number" min="0" value="0"></label>
                    <label>تعداد شاخه<input id="fishCount" type="number" min="0" value="0"></label>
                    <label>متراژ کل<input id="fishTotal" value="0 متر طول" readonly></label>
                </div>
                <div class="option-row">
                    <label>پریز کف خواب
                        <select id="cutFloor"><option value="no">خیر</option><option value="yes">بله</option></select>
                    </label>
                    <label>تعداد پریز<input id="cutCount" type="number" min="0" value="0"></label>
                </div>
                <div class="zero-note">گرده ماهی و پریز کف خواب در DTZ فعلی مبلغ محاسباتی ندارند و ۰ ریال ثبت می‌شوند.</div>
            </div>

            <div class="install-card">
                <div class="install-card-head"><span>۴</span><h2>موارد خاص نصب</h2></div>
                <div class="checks">
                    <label><input type="checkbox" name="special_cut"> برشکاری خاص</label>
                    <label><input type="checkbox" name="special_billiard"> میز بیلیارد</label>
                    <label><input type="checkbox" name="special_island"> میز جزیره</label>
                    <label><input type="checkbox" name="special_wall"> نصب دیواری</label>
                </div>
                <label>توضیحات<textarea id="specialDescription" rows="4" placeholder="توضیحات موارد خاص..."></textarea></label>
                <label>مبلغ پیشنهادی<input id="specialAmount" type="number" min="0" value="0"></label>
                <div class="zero-note">مبلغ پیشنهادی در منطق فعلی DTZ وارد محاسبه نهایی نمی‌شود.</div>
            </div>

            <div class="install-card">
                <div class="install-card-head"><span>۵</span><h2>محل نصب</h2></div>
                <div class="option-row">
                    <label class="check"><input id="hasFloorCarry" type="checkbox"> حمل به طبقات</label>
                    <label>تعداد طبقات<input id="floorCount" type="number" min="0" value="0"></label>
                    <label class="check"><input id="hasElevator" type="checkbox"> آسانسور دارد</label>
                    <label>نوع آسانسور<select id="elevatorType"><option value="person">نفربر</option><option value="cargo">باربر</option></select></label>
                </div>
                <div class="option-row">
                    <label>تمیزی محل<select id="cleanPlace"><option value="yes">بله</option><option value="no">خیر</option></select></label>
                    <label>تعداد کارگر<input id="workerCount" type="number" min="0" value="1"></label>
                    <label>نیاز به حمل<select id="needCarry"><option value="no">خیر</option><option value="yes">بله</option></select></label>
                </div>
                <div class="option-row">
                    <label>نوع لوکیشن<select id="locationType"><option value="staff">توسط پرسنل</option><option value="sms">ارسال مجدد SMS</option></select></label>
                    <label>شهر<input id="locationCity"></label>
                    <label>محله<input id="locationDistrict"></label>
                    <label>آدرس تکمیلی<input id="locationAddress"></label>
                </div>
                <div class="zero-note">حمل طبقات، آسانسور، کارگر و ایاب‌وذهاب در کد فعلی DTZ نرخ فعال ندارند و ۰ ریال هستند.</div>
            </div>

            <div class="install-card">
                <div class="install-card-head"><span>۶</span><h2>بازبینی</h2></div>
                <div class="summary-row"><span>نصب</span><b id="installationAmount">۰ ریال</b></div>
                <div class="summary-row"><span>کف و چسب</span><b>۰ ریال</b></div>
                <div class="summary-row"><span>کارهای جانبی</span><b>۰ ریال</b></div>
                <div class="summary-row total"><span>هزینه نهایی نصب</span><b id="totalAmount">۰ ریال</b></div>
                <p class="small-muted">اجرت نصب: ۵۰۰٬۰۰۰ ریال برای هر مترمربع؛ حداقل اجرت تا ۳۰ مترمربع، ۱۵٬۰۰۰٬۰۰۰ ریال. مبلغ نهایی در سرور دوباره محاسبه می‌شود.</p>
                <button type="button" id="confirmInstallation" class="btn btn-primary wide">تأیید نهایی و ادامه سفارش</button>
                <div id="installMessage" class="message"></div>
            </div>
        </div>

        <aside class="install-summary">
            <span class="eyebrow">INSTALLATION</span>
            <h2>خلاصه</h2>
            <div><span>کد نصب</span><b>{{ $quote->tracking_code }}</b></div>
            <div><span>مشتری</span><b>{{ $order->name }}</b></div>
            <div><span>تعداد طاقه</span><b>{{ data_get($quote->payload,'purchased_roll_quantity',0) }}</b></div>
            <div><span>متراژ خریداری‌شده</span><b>{{ number_format((float)data_get($quote->payload,'purchased_area',0),2) }} m²</b></div>
            <hr>
            <div><span>اجرت نصب</span><b>۵۰۰٬۰۰۰ ریال / m²</b></div>
            <div><span>حداقل اجرت</span><b>۱۵٬۰۰۰٬۰۰۰ ریال</b></div>
            <div><span>مبلغ فعلی</span><b id="sideAmount">۰ ریال</b></div>
        </aside>
    </div>
</div>
</section>

<style>
.install-local-note{background:#fff8f8;border:1px solid #f2d5d7;border-radius:16px;padding:16px 18px;margin-bottom:18px;display:flex;gap:12px;flex-wrap:wrap}
.install-local-note strong{color:#d91f26}.install-grid{display:grid;grid-template-columns:minmax(0,1fr) 300px;gap:20px}.install-card,.install-summary{background:#fff;border:1px solid #eee;border-radius:18px;padding:22px;margin-bottom:18px;box-shadow:0 8px 28px rgba(0,0,0,.045)}.install-summary{position:sticky;top:18px;height:max-content}.install-summary>div,.summary-row{display:flex;justify-content:space-between;gap:12px;padding:11px 0;border-bottom:1px solid #eee}.install-summary>div:last-child,.summary-row.total{border-bottom:0}.install-card-head{display:flex;align-items:center;gap:10px;margin-bottom:18px}.install-card-head span{width:34px;height:34px;border-radius:50%;display:grid;place-items:center;background:#d91f26;color:#fff;font-weight:800}.install-card h2,.install-summary h2{margin:0;font-size:20px}.product-install-card{border:1px solid #eee;border-radius:15px;padding:16px;margin-bottom:12px;background:#fff}
.product-install-main{display:flex;align-items:center;gap:12px;margin-bottom:15px}
.product-install-icon{width:46px;height:46px;border-radius:12px;background:#d91f26;color:#fff;display:grid;place-items:center;font-weight:900;font-size:20px}
.product-install-title{display:flex;flex-direction:column;gap:3px;min-width:0}
.product-install-title span,.product-install-title small{font-size:12px;color:#777}
.product-install-title strong{font-size:16px;line-height:1.7}
.product-install-details{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
.product-install-details div{background:#fafafa;border-radius:10px;padding:10px}
.product-install-details span{display:block;font-size:11px;color:#777;margin-bottom:4px}
.product-install-details b{font-size:14px}
.option-row{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-bottom:15px}.option-row label,.install-card>label{display:flex;flex-direction:column;gap:6px;font-size:13px;color:#555}.install-card input,.install-card select,.install-card textarea{border:1px solid #ddd;border-radius:10px;padding:10px;background:#fff}.install-card input[readonly]{background:#fafafa}.check,.checks label{display:flex!important;flex-direction:row!important;align-items:center;gap:7px}.checks{display:flex;gap:20px;flex-wrap:wrap;margin-bottom:18px}.outline-btn,.small-btn{border:1px solid #d91f26;background:#fff;color:#d91f26;border-radius:9px;padding:9px 14px}.small-btn{border-color:#ddd;color:#777}.sub-block{margin-top:18px}.glue-row{grid-template-columns:1fr 180px 80px}.stairs{margin-top:20px}.area-total{display:flex;justify-content:space-between;background:#fff8f8;border-radius:12px;padding:14px;margin-top:16px}.area-total b{color:#d91f26}.summary-row.total{font-size:18px;font-weight:800;color:#d91f26}.small-muted,.zero-note{font-size:12px;color:#777;margin-top:12px}.zero-note{background:#fafafa;border-radius:10px;padding:10px}.message{margin-top:12px;color:#555}@media(max-width:900px){.install-grid{grid-template-columns:1fr}.install-summary{position:static}.product-install-details{grid-template-columns:1fr 1fr}.option-row{grid-template-columns:1fr 1fr}}@media(max-width:600px){.option-row,.product-install-details{grid-template-columns:1fr}.install-card{padding:16px}}
</style>

<script>
(function(){
    const rate = 500000;
    const minimumAmount = 15000000;
    const purchasedArea = {{ json_encode((float)data_get($quote->payload,'purchased_area',0)) }};
    const completeUrl = @json($completeUrl);

    const money = value => Number(value || 0).toLocaleString('fa-IR') + ' ریال';
    const value = id => document.getElementById(id)?.value ?? '';

    function updatePreview(){
        const amount = Math.max(purchasedArea * rate, minimumAmount);
        document.getElementById('installationAmount').textContent = money(amount);
        document.getElementById('totalAmount').textContent = money(amount);
        document.getElementById('sideAmount').textContent = money(amount);
        const length = Number(value('fishLength')) || 0;
        const count = Number(value('fishCount')) || 0;
        document.getElementById('fishTotal').value = (length * count) + ' متر طول';
    }

    function collect(){
        const floors = [...document.querySelectorAll('#floors .option-row')].map(row => ({
            type: row.querySelector('.floor-type')?.value || 'normal',
            normal: !!row.querySelector('.normal-floor')?.checked,
            meter: Number(row.querySelector('.floor-meter')?.value || 0)
        }));

        const glue = [...document.querySelectorAll('#glues .glue-row')].map(row => ({
            type: row.querySelector('.glue-type')?.value || 'none',
            count: Number(row.querySelector('.glue-count')?.value || 1)
        }));

        const special = {
            cut: !!document.querySelector('[name="special_cut"]')?.checked,
            billiard: !!document.querySelector('[name="special_billiard"]')?.checked,
            island: !!document.querySelector('[name="special_island"]')?.checked,
            wall: !!document.querySelector('[name="special_wall"]')?.checked,
            description: value('specialDescription'),
            amount: Number(value('specialAmount')) || 0
        };

        return {
            rolls: @json($rolls),
            floors,
            glue,
            stairs: {
                has: document.querySelector('[name="has_stairs"]:checked')?.value === '1',
                straight: Number(value('straightStairs')) || 0,
                edged: Number(value('edgedStairs')) || 0,
                turn: Number(value('turnStairs')) || 0
            },
            fish: {
                need: value('fishNeed'),
                type: value('fishType'),
                length: Number(value('fishLength')) || 0,
                count: Number(value('fishCount')) || 0,
                total_length: (Number(value('fishLength')) || 0) * (Number(value('fishCount')) || 0)
            },
            cut: {
                need: value('cutFloor'),
                count: Number(value('cutCount')) || 0
            },
            special,
            location: {
                floor_carry: !!document.getElementById('hasFloorCarry')?.checked,
                floor_count: Number(value('floorCount')) || 0,
                elevator: !!document.getElementById('hasElevator')?.checked,
                elevator_type: value('elevatorType'),
                clean_place: value('cleanPlace'),
                worker_count: Number(value('workerCount')) || 0,
                need_carry: value('needCarry'),
                location_type: value('locationType'),
                city: value('locationCity'),
                district: value('locationDistrict'),
                address: value('locationAddress')
            },
            options: {
                note: @json(data_get($quote->payload,'customer.description',''))
            }
        };
    }

    document.querySelectorAll('input,select,textarea').forEach(el => {
        el.addEventListener('input', updatePreview);
        el.addEventListener('change', updatePreview);
    });

    document.getElementById('addFloor').addEventListener('click', function(){
        const row=document.createElement('div');
        row.className='option-row';
        row.innerHTML='<label>نوع کف<select class="floor-type"><option value="normal">عادی (سنگ، سرامیک، لمینت، پارکت، موزاییک)</option><option value="cement">سیمان</option><option value="moquette">موکت</option></select></label><label class="check"><input type="checkbox" class="normal-floor" checked> کف کاملاً عادی است</label><label>متراژ دقیق کف<input type="number" class="floor-meter" min="0" step="0.01" value="0"></label><button type="button" class="small-btn remove-floor">حذف</button>';
        document.getElementById('floors').appendChild(row);
    });

    document.addEventListener('click', e => {
        if(e.target.closest('.remove-floor')){
            const rows=document.querySelectorAll('#floors .option-row');
            if(rows.length>1) e.target.closest('.option-row').remove();
        }
        if(e.target.closest('.remove-glue')){
            const rows=document.querySelectorAll('#glues .glue-row');
            if(rows.length>1) e.target.closest('.glue-row').remove();
        }
    });

    document.getElementById('addGlue').addEventListener('click', function(){
        const row=document.createElement('div');
        row.className='option-row glue-row';
        row.innerHTML='<label>نوع چسب<select class="glue-type"><option value="none">بدون چسب</option><option value="edge">دور چسب</option><option value="full">تمام چسب</option><option value="double_25">چسب دوطرف 25 متری</option><option value="water_soluble">چسب حلال آب</option></select></label><label>تعداد<input type="number" class="glue-count" min="1" value="1"></label><button type="button" class="small-btn remove-glue">حذف</button>';
        document.getElementById('glues').appendChild(row);
    });

    document.getElementById('confirmInstallation').addEventListener('click', async function(){
        const button=this;
        const message=document.getElementById('installMessage');
        button.disabled=true;
        button.textContent='در حال ثبت محاسبه...';
        message.textContent='در حال محاسبه نهایی در سرور...';
        try{
            const response=await fetch(completeUrl,{
                method:'POST',
                headers:{'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':@json(csrf_token())},
                body:JSON.stringify({payload:collect()})
            });
            const data=await response.json();
            if(!response.ok || !data.success) throw new Error(data.message || 'محاسبه انجام نشد');
            window.location.href=data.callback_url;
        }catch(error){
            button.disabled=false;
            button.textContent='تأیید نهایی و ادامه سفارش';
            message.textContent='محاسبه ثبت نشد. دوباره تلاش کنید.';
            console.error(error);
        }
    });

    updatePreview();
})();
</script>
@endsection
