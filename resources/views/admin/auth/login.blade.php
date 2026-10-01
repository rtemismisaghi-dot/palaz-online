<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود | پالاز آنلاین</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { min-height:100vh; display:grid; place-items:center; background:#f7f7f8; }
        .login-card { width:min(420px,92vw); background:#fff; border-radius:22px; padding:32px; box-shadow:0 18px 55px rgba(0,0,0,.09); }
        .logo { font-weight:800; font-size:28px; margin-bottom:8px; }
        .muted { color:#777; margin-bottom:24px; }
        label { display:block; margin:14px 0 7px; font-weight:700; }
        input { width:100%; border:1px solid #ddd; border-radius:12px; padding:12px 14px; box-sizing:border-box; }
        button { width:100%; margin-top:22px; border:0; border-radius:12px; padding:13px; background:#b5121b; color:#fff; font-weight:800; cursor:pointer; }
        .error { color:#b5121b; margin-top:10px; font-size:14px; }
        .setup { background:#fff7e6; border-radius:12px; padding:11px 13px; margin-bottom:14px; font-size:13px; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="logo">PALAZ ONLINE</div>
    <div class="muted">ورود به پالاز آنلاین</div>
    @if (!$hasAdmin)
        <div class="setup">اولین ورود در محیط Local حساب مدیر را با شماره موبایل و رمز عبور ایجاد می‌کند.</div>
    @endif
    @if ($errors->any())
        <div class="error">{{ $errors->first() }}</div>
    @endif
    <form method="POST" action="{{ route('admin.login.submit') }}">
        @csrf
        <label for="mobile">شماره موبایل</label>
        <input id="mobile" type="tel" name="mobile" value="{{ old('mobile') }}" inputmode="tel" autocomplete="username" required autofocus>
        <label for="password">رمز عبور</label>
        <input id="password" type="password" name="password" minlength="8" autocomplete="current-password" required>
        <button type="submit">ورود</button>
    </form>
</div>
</body>
</html>
