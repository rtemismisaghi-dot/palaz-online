<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود | پالاز آنلاین</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body{min-height:100vh;display:grid;place-items:center;background:#f7f7f8}
        .card{width:min(420px,92vw);background:#fff;border-radius:22px;padding:32px;box-shadow:0 18px 55px rgba(0,0,0,.09)}
        h1{font-size:28px;margin:0 0 8px}.muted{color:#777;margin-bottom:24px}
        label{display:block;margin:14px 0 7px;font-weight:700}input{width:100%;box-sizing:border-box;border:1px solid #ddd;border-radius:12px;padding:12px 14px}
        button{width:100%;margin-top:22px;border:0;border-radius:12px;padding:13px;background:#b5121b;color:#fff;font-weight:800;cursor:pointer}
        .error{color:#b5121b;margin-top:10px;font-size:14px}
    </style>
</head>
<body>
<div class="card">
    <h1>ورود</h1>
    <div class="muted">برای ورود شماره موبایل خود را وارد کنید.</div>
    @if($errors->any())<div class="error">{{ $errors->first() }}</div>@endif
    @if(!empty($adminMobile))
        <form method="POST" action="{{ route('login.admin') }}">
            @csrf
            <input type="hidden" name="mobile" value="{{ $adminMobile }}">
            <label>رمز عبور</label>
            <input type="password" name="password" minlength="8" autocomplete="current-password" required autofocus>
            <button type="submit">ورود</button>
        </form>
    @elseif(!empty($otpSent))
        <form method="POST" action="{{ route('login.verify') }}">
            @csrf
            <input type="hidden" name="mobile" value="{{ $mobile }}">
            <label>کد پیامک‌شده</label>
            <input type="text" name="otp" inputmode="numeric" maxlength="6" autocomplete="one-time-code" required autofocus>
            <button type="submit">ورود</button>
        </form>
    @else
        <form method="POST" action="{{ route('login.submit') }}">
            @csrf
            <label>شماره موبایل</label>
            <input type="tel" name="mobile" inputmode="tel" autocomplete="tel" required autofocus>
            <button type="submit">ادامه</button>
        </form>
    @endif
</div>
</body>
</html>
