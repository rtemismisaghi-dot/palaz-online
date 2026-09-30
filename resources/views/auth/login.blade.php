<!doctype html>
<html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ورود | PALAZ ONLINE</title>
<style>
body{margin:0;background:#f7f4f1;font-family:Tahoma,Arial,sans-serif;color:#292321;min-height:100vh;display:grid;place-items:center}
.box{width:min(460px,calc(100% - 32px));background:#fff;border-radius:26px;box-shadow:0 20px 70px #0001;padding:30px}
.logo{display:flex;align-items:center;gap:10px;font-weight:900;font-size:20px;margin-bottom:28px}.mark{width:46px;height:46px;border-radius:14px;background:#9f1820;color:#fff;display:grid;place-items:center}
.tabs{display:flex;background:#f6f3f0;border-radius:14px;padding:4px;margin-bottom:22px}.tab{flex:1;border:0;background:transparent;padding:11px;border-radius:11px;font-family:inherit;cursor:pointer}.tab.active{background:#fff;box-shadow:0 3px 12px #0001;color:#9f1820;font-weight:700}
.panel{display:none}.panel.active{display:block}label{display:block;font-size:13px;margin:13px 0 7px}input{width:100%;padding:13px;border:1px solid #ddd4ce;border-radius:12px;box-sizing:border-box;font-family:inherit}button.submit{width:100%;border:0;background:#9f1820;color:#fff;padding:14px;border-radius:12px;font-family:inherit;margin-top:18px;cursor:pointer}.hint{font-size:12px;color:#877d77;line-height:1.9;margin-top:12px}.back{display:block;text-align:center;margin-top:18px;color:#756d68;text-decoration:none;font-size:13px}
</style></head><body><main class="box">
<div class="logo"><span class="mark">P</span><span>PALAZ ONLINE<br><small style="font-size:11px;color:#877d77;font-weight:500">ورود به حساب</small></span></div>
<div class="tabs"><button class="tab active" data-tab="customer">مشتری</button><button class="tab" data-tab="staff">پرسنل پالاز</button></div>
<section id="customer" class="panel active"><h2>ورود مشتری</h2><p class="hint">با شماره موبایل وارد شوید و کد تأیید برایتان ارسال می‌شود.</p><label>شماره موبایل</label><input type="tel" placeholder="09xxxxxxxxx"><button class="submit" type="button" onclick="alert('اتصال پیامک در مرحله بعد فعال می‌شود.')">ارسال کد ورود</button></section>
<section id="staff" class="panel"><h2>ورود پرسنل</h2><p class="hint">این بخش فقط برای مدیر و پرسنل مجاز پالاز است.</p><form method="post" action="{{ route('login.staff') }}">@csrf<label>شماره پرسنلی / موبایل</label><input name="phone" type="tel" value="{{ old('phone') }}" required><label>رمز عبور</label><input name="password" type="password" required><button class="submit">ورود به پنل مدیریت</button></form></section>
<a class="back" href="{{ route('home') }}">بازگشت به سایت</a>
</main><script>
document.querySelectorAll('.tab').forEach(b=>b.onclick=()=>{document.querySelectorAll('.tab').forEach(x=>x.classList.remove('active'));document.querySelectorAll('.panel').forEach(x=>x.classList.remove('active'));b.classList.add('active');document.getElementById(b.dataset.tab).classList.add('active')});
</script></body></html>