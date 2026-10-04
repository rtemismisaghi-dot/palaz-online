<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ورود مدیریت پالاز</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<style>
:root{--palaz:#9f1820;--ink:#241f1d;--muted:#756d68;--bg:#f7f4f1;--line:#ebe5e0}
*{box-sizing:border-box}
body{margin:0;min-height:100vh;background:var(--bg);color:var(--ink);font-family:Tahoma,Arial,sans-serif}
.login-page{min-height:100vh;display:grid;place-items:center;padding:24px}
.login-card{width:min(430px,100%);background:#fff;border:1px solid var(--line);border-radius:24px;box-shadow:0 18px 55px rgba(35,25,20,.08);padding:34px}
.brand{display:flex;align-items:center;gap:12px;margin-bottom:28px}
.brand-mark{width:48px;height:48px;border-radius:14px;background:var(--palaz);color:#fff;display:grid;place-items:center;font-size:22px;font-weight:900}
.brand-title{font-size:19px;font-weight:800;line-height:1.4}
.brand-sub{display:block;color:#9b918b;font-size:12px;font-weight:500}
h1{font-size:23px;font-weight:800;margin:0 0 8px}
.lead{color:var(--muted);font-size:13px;line-height:1.9;margin-bottom:25px}
.form-label{font-size:13px;font-weight:700;margin-bottom:8px}
.form-control{height:48px;border-radius:12px;border-color:#ddd4ce}
.form-control:focus{border-color:#c47a82;box-shadow:0 0 0 .2rem rgba(159,24,32,.08)}
.btn-palaz{height:48px;background:var(--palaz);border-color:var(--palaz);color:#fff;border-radius:12px;font-weight:700}
.btn-palaz:hover{background:#7f1118;border-color:#7f1118;color:#fff}
.hint{font-size:11px;color:#9b918b;line-height:1.8;margin-top:18px}
.back{display:block;text-align:center;color:var(--muted);text-decoration:none;font-size:12px;margin-top:18px}
.back:hover{color:var(--palaz)}
</style>
</head>
<body>
<div class="login-page">
<div class="login-card">
<div class="brand">
<span class="brand-mark">P</span>
<div class="brand-title">PALAZ ONLINE<span class="brand-sub">کنسول مدیریت</span></div>
</div>
<h1>ورود به مدیریت</h1>
<p class="lead">برای ورود به پنل مدیریت، شماره موبایل و رمز عبور مدیر را وارد کنید.</p>
<form method="POST" action="{{ route('admin.login.submit') }}">
@csrf
<div class="mb-3">
<label class="form-label" for="mobile">شماره موبایل</label>
<input id="mobile" name="mobile" type="tel" class="form-control" value="{{ old('mobile') }}" autocomplete="username" inputmode="tel" required autofocus>
</div>
<div class="mb-3">
<label class="form-label" for="password">رمز عبور</label>
<input id="password" name="password" type="password" class="form-control" autocomplete="current-password" required>
</div>
@if($errors->any())
<div class="alert alert-danger rounded-3 py-2 small">{{ $errors->first() }}</div>
@endif
<button type="submit" class="btn btn-palaz w-100">ورود به پنل</button>
</form>
@if(!$hasAdmin && app()->environment('local'))
<div class="hint">در محیط محلی، اگر هنوز مدیر ثبت نشده باشد، اولین ورود با شماره موبایل و رمز واردشده حساب مدیر را ایجاد می‌کند.</div>
@endif
<a class="back" href="{{ route('home') }}">بازگشت به فروشگاه</a>
</div>
</div>
</body>
</html>
