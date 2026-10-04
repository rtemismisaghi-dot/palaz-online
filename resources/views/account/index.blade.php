@extends('layouts.store')

@section('title', 'حساب کاربری | پالاز آنلاین')

@section('content')
<style>
.account-page{padding:42px 0 70px;background:linear-gradient(180deg,#faf8f5 0,#fff 45%)}
.account-shell{max-width:1180px;margin:auto;padding:0 20px}
.account-head{display:flex;justify-content:space-between;align-items:end;gap:20px;margin-bottom:24px}
.account-head h1{margin:0;font-size:30px}.account-head p{margin:8px 0 0;color:#777}
.account-grid{display:grid;grid-template-columns:230px 1fr;gap:22px}
.account-menu,.account-card{background:#fff;border:1px solid #eee5df;border-radius:22px;box-shadow:0 10px 30px rgba(30,20,15,.05)}
.account-menu{padding:12px;height:max-content}.account-menu a{display:block;padding:13px 15px;border-radius:14px;color:#444;text-decoration:none}.account-menu a.active,.account-menu a:hover{background:#f7efeb;color:#971f32}
.account-card{padding:22px}.account-card h2{margin:0 0 18px;font-size:19px}
.order-list,.service-list{display:grid;gap:12px}.order-item,.service-item{border:1px solid #eee8e3;border-radius:16px;padding:15px;background:#fff}
.item-top{display:flex;justify-content:space-between;gap:12px;align-items:center}.muted{color:#777;font-size:13px}.status{border-radius:999px;padding:5px 10px;background:#f6efeb;color:#8e2637;font-size:12px}
.empty{padding:28px;text-align:center;color:#777;background:#faf9f7;border-radius:16px}
.service-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}.service-actions a{padding:11px 16px;border-radius:13px;text-decoration:none;background:#971f32;color:#fff}.service-actions a.secondary{background:#f5f1ee;color:#555}
@media(max-width:800px){.account-grid{grid-template-columns:1fr}.account-menu{display:flex;overflow:auto;gap:5px}.account-menu a{white-space:nowrap}.account-head{align-items:start;flex-direction:column}}
</style>

<section class="account-page">
<div class="account-shell">
    <div class="account-head">
        <div>
            <div class="eyebrow">حساب من</div>
            <h1>حساب کاربری پالاز</h1>
            <p>سفارش‌ها و خدمات شما، در همان تجربه‌ای که از پالاز می‌شناسید.</p>
        </div>
        <div class="muted">شماره همراه: {{ $mobile }}</div>
    </div>

    <div class="account-grid">
        <aside class="account-menu">
            <a href="#orders" class="active">سفارش‌های من</a>
            <a href="#services">خدمات و نصب</a>
            <a href="{{ route('shop') }}">ادامه خرید</a>
            <a href="{{ route('services') }}">درخواست خدمت جدید</a>
        </aside>

        <main>
            <section class="account-card" id="orders">
                <h2>سفارش‌های من</h2>
                @if($orders->isEmpty())
                    <div class="empty">هنوز سفارشی با این حساب ثبت نشده است.</div>
                @else
                    <div class="order-list">
                        @foreach($orders as $order)
                            <article class="order-item">
                                <div class="item-top">
                                    <strong>سفارش {{ $order->tracking_code }}</strong>
                                    <span class="status">{{ $order->status ?: 'ثبت شده' }}</span>
                                </div>
                                <div class="muted" style="margin-top:8px">
                                    {{ $order->created_at?->format('Y/m/d') }} ·
                                    {{ number_format((float)$order->total) }} تومان
                                </div>
                                @if($order->items->isNotEmpty())
                                    <div class="muted" style="margin-top:9px">
                                        {{ $order->items->pluck('product_name')->take(3)->implode('، ') }}
                                        @if($order->items->count() > 3) و ... @endif
                                    </div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="account-card" id="services" style="margin-top:20px">
                <h2>خدمات و نصب</h2>
                @if($services->isEmpty())
                    <div class="empty">هنوز درخواست خدماتی برای این حساب ثبت نشده است.</div>
                @else
                    <div class="service-list">
                        @foreach($services as $service)
                @if($service->type === 'installation' && $service->order_id)
                    <a class="account-service-action" href="{{ route('account.installation', ['order' => $service->order_id]) }}">ادامه مسیر نصب</a>
                @endif
                            <article class="service-item">
                                <div class="item-top">
                                    <strong>{{ match($service->type){'installation'=>'نصب','measurement'=>'اندازه‌گیری','design'=>'طراحی',default=>'خدمت'} }}</strong>
                                    <span class="status">{{ $service->status ?: 'در حال بررسی' }}</span>
                                </div>
                                <div class="muted" style="margin-top:8px">
                                    کد پیگیری: {{ $service->tracking_code }}
                                    · {{ $service->created_at?->format('Y/m/d') }}
                                </div>
                                @if($service->description)
                                    <div class="muted" style="margin-top:9px;white-space:pre-line">{{ $service->description }}</div>
                                @endif
                            </article>
                        @endforeach
                    </div>
                @endif
                <div class="service-actions">
                    <a href="{{ route('services') }}">درخواست خدمت</a>
                    <a href="{{ route('shop') }}" class="secondary">بازگشت به فروشگاه</a>
                </div>
            </section>
        </main>
    </div>
</div>
</section>
@endsection
