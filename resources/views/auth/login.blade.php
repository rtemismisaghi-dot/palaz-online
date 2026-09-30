<!doctype html>
<html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>ورود | PALAZ ONLINE</title>
<style>
body{margin:0;background:#f7f4f1;font-family:Tahoma,Arial,sans-serif;color:#292321;min-height:100vh;display:grid;place-items:center}
.box{width:min(460px,calc(100% - 32px));background:#fff;border-radius:26px;box-shadow:0 20px 70px #0001;padding:30px}
.logo{display:flex;align-items:center;gap:10px;font-weight:900;font-size:20px;margin-bottom:28px}.mark{width:46px;height:46px;border-radius:14px;background:#9f1820;color:#fff;display:grid;place-items:center}
label{display:block;font-size:13px;margin:16px 0 7px}input{width:100%;padding:13px;border:1px solid #ddd4ce;border-radius:12px;box-sizing:border-box;font-family:inherit}
button.submit{width:100%;border:0;background:#9f1820;color:#fff;padding:14px;border-radius:12px;font-family:inherit;margin-top:22px;cursor:pointer}.hint{font-size:12px;color:#877d77;line-height:1.9;margin-top:12px}.back{display:block;text-align:center;margin-top:18px;color:#756d68;text-decoration:none;font-size:13px}
.error{background:#fff1f1;color:#9f1820;border-radius:12px;padding:10px 12px;font-size:12px;margin-top:14px}
</style></head><body><main class="box">
<div class="logo"><span class="mark">P</span><span>PALAZ ONLINE<br><small style="font-size:11px;color:#877d77;font-weight:500">ورود به حساب</small></span></div>
<h2>ورود</h2><p class="hint">با شماره موبایل و رمز عبور وارد شوید.</p>
@if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
<form method="post" action="{{ route('login.staff') }}">@csrf
<label>شماره موبایل</label><input name="phone" type="tel" inputmode="numeric" autocomplete="username" value="{{ old('phone') }}" placeholder="09xxxxxxxxx" required>
<label>رمز عبور</label><input name="password" type="password" autocomplete="current-password" required>
<button class="submit">ورود</button>
</form><a class="back" href="{{ route('home') }}">بازگشت به سایت</a>
</main></body></html>