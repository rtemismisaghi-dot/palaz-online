@extends('layouts.admin') @section('content')<div class="mb-4"><h1>کنسول مدیریت</h1><p class="text-secondary">کنترل محصولات، دسته‌بندی‌ها و منطق قیمت‌گذاری.</p></div><div class="row g-3">@foreach($stats as $key=>$value)<div class="col-6 col-lg-3"><div class="card p-4"><small class="text-secondary">{{ ['categories'=>'دسته‌بندی','products'=>'محصول','orders'=>'سفارش','services'=>'درخواست خدمات'][$key] }}</small><strong class="fs-2">{{ $value }}</strong></div></div>@endforeach</div><div class="row g-3 mt-3"><div class="col-md-6"><a class="btn btn-palaz w-100 py-3" href="{{ route('admin.products.index') }}">مدیریت محصولات و قیمت‌گذاری</a></div><div class="col-md-6"><a class="btn btn-outline-dark w-100 py-3" href="{{ route('admin.categories.index') }}">مدیریت دسته‌بندی‌ها</a></div></div>
<div class="card p-4 mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h2 class="h5 mb-1">🤖 Agent مدیریت پالاز</h2><small class="text-secondary">برای جستجو، قیمت و وضعیت محصول دستور فارسی بده.</small></div>
    </div>
    @if(session('agent_result'))
        <div class="alert alert-info mb-3">{{ session('agent_result.reply') }}</div>
        @if(is_array(session('agent_result.action')) && (session('agent_result.action.confirm_required') ?? false))
            <div class="border rounded-4 p-3 mb-3">
                <strong>این تغییر نیاز به تأیید دارد.</strong>
                <div class="mt-2 d-flex gap-2">
                    <form method="post" action="{{ route('admin.agent.confirm') }}">@csrf<button class="btn btn-palaz">تأیید و اجرا</button></form>
                    <form method="post" action="{{ route('admin.agent.cancel') }}">@csrf<button class="btn btn-outline-secondary">لغو</button></form>
                </div>
            </div>
        @endif
    @endif
    <form method="post" action="{{ route('admin.agent.chat') }}" class="d-flex gap-2">
        @csrf
        <input name="message" class="form-control" placeholder="مثلاً: قیمت کد 5223 را 10 درصد زیاد کن">
        <button class="btn btn-palaz px-4">ارسال</button>
    </form>
</div>
@endsection