<!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>@yield('title','مدیریت پالاز آنلاین')</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
<style>
:root{--palaz:#9f1820;--ink:#241f1d;--muted:#756d68;--bg:#f7f4f1;--line:#ebe5e0}
*{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font-family:Tahoma,Arial,sans-serif}
.admin-shell{min-height:100vh;display:flex}.admin-sidebar{width:250px;background:#fff;border-left:1px solid var(--line);padding:22px 16px;position:sticky;top:0;height:100vh}
.admin-brand{display:flex;align-items:center;gap:10px;text-decoration:none;color:var(--ink);font-weight:800;font-size:17px;margin-bottom:28px}
.admin-brand-mark{width:40px;height:40px;border-radius:12px;background:var(--palaz);color:#fff;display:grid;place-items:center;font-weight:900}
.admin-nav a{display:flex;align-items:center;gap:10px;color:#554e49;text-decoration:none;padding:11px 12px;border-radius:12px;margin:3px 0}
.admin-nav a:hover,.admin-nav a.active{background:#f8eeee;color:var(--palaz)}
.admin-main{flex:1;min-width:0}.admin-top{height:72px;background:#fff;border-bottom:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;padding:0 30px}
.admin-content{max-width:1400px;margin:auto;padding:28px 30px}
.card{border:1px solid var(--line);border-radius:18px;box-shadow:0 8px 28px rgba(35,25,20,.045);background:#fff}
.btn-palaz{background:var(--palaz);border-color:var(--palaz);color:#fff;border-radius:11px}.btn-palaz:hover{background:#7f1118;border-color:#7f1118;color:#fff}
.form-control,.form-select{border-radius:10px;border-color:#ddd4ce}.form-control:focus,.form-select:focus{border-color:#c47a82;box-shadow:0 0 0 .2rem rgba(159,24,32,.08)}
.table>:not(caption)>*>*{padding:14px 12px;border-color:var(--line)}.table thead th{font-size:12px;color:var(--muted);font-weight:700}
.stat{padding:20px}.stat small{color:var(--muted);display:block;margin-bottom:8px}.stat strong{font-size:30px}
.mobile-toggle{display:none}
@media(max-width:900px){.admin-sidebar{width:210px}.admin-content{padding:20px}.admin-top{padding:0 20px}}
@media(max-width:700px){.admin-shell{display:block}.admin-sidebar{position:relative;width:100%;height:auto;border-left:0;border-bottom:1px solid var(--line);padding:12px}.admin-brand{margin-bottom:8px}.admin-nav{display:flex;gap:5px;overflow:auto}.admin-nav a{white-space:nowrap}.admin-top{display:none}.admin-content{padding:14px}.table{min-width:760px}}
</style>
</head>
<body>
<div class="admin-shell">
<aside class="admin-sidebar">
<a class="admin-brand" href="{{ route('admin.dashboard') }}"><span class="admin-brand-mark">P</span><span>PALAZ ONLINE<br><small style="color:#9b918b;font-weight:500">کنسول مدیریت</small></span></a>
<nav class="admin-nav">
<a class="{{ request()->routeIs('admin.dashboard')?'active':'' }}" href="{{ route('admin.dashboard') }}">▦ داشبورد</a>
<a class="{{ request()->routeIs('admin.products.*')?'active':'' }}" href="{{ route('admin.products.index') }}">▣ محصولات</a>
<a class="{{ request()->routeIs('admin.categories.*')?'active':'' }}" href="{{ route('admin.categories.index') }}">◈ دسته‌بندی‌ها</a>
<a href="{{ route('home') }}">↗ مشاهده فروشگاه</a>
</nav>
</aside>
<section class="admin-main">
<header class="admin-top"><strong>@yield('title','داشبورد مدیریت')</strong><span style="color:#756d68;font-size:13px">مدیریت کاتالوگ و فروشگاه پالاز</span></header>
<main class="admin-content">
@if(session('success'))<div class="alert alert-success rounded-4">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger rounded-4">{{ $errors->first() }}</div>@endif
@yield('content')
</main>
</section>
</div>
</body>
</html>
