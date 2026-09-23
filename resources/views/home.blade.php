<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="PALAZ ONLINE — فروش آنلاین محصولات و خدمات پالاز">
    <title>PALAZ ONLINE</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<header class="site-header">
    <div class="announcement"><span class="announcement-pulse"></span> ارسال و مشاوره تخصصی پالاز در سراسر ایران</div>

    <div class="container header-main">
        <button class="mobile-menu" type="button" aria-label="باز کردن منو" aria-expanded="false">☰</button>

        <a href="{{ route('home') }}" class="brand brand-real" aria-label="پالاز آنلاین">
            <img class="brand-logo" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAARQAAAA8CAYAAACn+EWWAAAO5klEQVR4nO3dP28UaZ7A8d/TgDfYsaat3sQ9jK5GAt8Fa9HIl3DJVSdEW8LI0W0ykDgFXgHNK8Ckm2ASNjkLI4gmmSKB5FoYeYJbz0j0amZNMi0bmTtpG+Pa7+OqWrfLT9HVXdV2236+0meqDE27bPfzm+o/tpXYbDZbQdmBMtzGMAVHRKooupaINNGGzZbahlMry/aHS+zudfaLNxOtlU32CkvBNpwcEXElHCrDrIPH0FubbbeNr//12o7suEEQ1FSweztMLVDiK6VWSlLyJ37+y1P+aOAUbMU3BVcOL19E1mA7xW04/+bsfPx0V1QwK4GU+aP+U7LJhFkunTtzb6L1vy3pMztQiq+CORxmr7AK2ylMD5Jge/t+EMgsbxaWUrKszp69089gsQOl+P6AKg6zP2MLtlOUflxkZ/vDLc5GGjLMlDQqv/x0j72eKdiKq4I5HFYd+BI+OGs7RW04F2s7H+Uhj4DUePMQ4jGWc2eu9zpbsQOl2KZxBaaaeIei+jvasJ2ydofJdvA9ZyZl3jy8eHyldFbVJ1o/rvCWMTtQim0mkqwZsdly9evXF2+oneAhu0eTHiqqdHPi57Vl3jqQHSjFpoeJluw51mGzDdyRnZkk00Ml5UzlJA2UMfwGWyi6cei7GPoxi881E0mWdaBU0MYwKkO/sElvX2ATpsr4T2ziDfS2qGrQx6CvdwVp6ffvSHicLbExTHhKeHv79ZEPk7jdoXL2cvIxFYWT0BVMQ7eF79BG3iq4inHoVvEKac1EkvUaKNPQH4OuA1+KXUiOiHwPR8I2cR2+7M8VkScoQ9cSkTpakr/7uI24RRG5ie7KeAJX9tKXWZRTXvuri68P7wHYrKmVyt9+vMzOP1M47ukFrHW3hT8jb/+FcXTXjJiaiST73ECpQj/VnEwf/xaKaBnX0N0mJtDdBsro7ilmkSdXwoGW7CYWZa+GiNxFsm/QklNa+/yFBmcmps/L0afkHk8pNyRK4bjnYRLJPreIs5S20NtYgqlpXEGyRQnPPEz9B36PZK+wiiIKYOoyVqCr4TVM5b2dNMQ8KB7hhuzlS3h3J9lNLMopbOTu6iRL3PVROO55mESyYQ0U3Z9gagz6eCqI+wEvkZa+vOn4m5EiWsElJOv++rtiPovQdV9ukBpiHigv4MpevpgHyj005BTGXZ1F/n/wreRJyftAf26VWj4TBC05qzZlOyjzN7ITSC1Q4qrgwBlsH6lH3PW5IaRw3PNgWpDPsY5Bq6LfgaIbwzTGsY41fC4PpuNvRopoFk/Q3T00ZC9X7EAZqXbPTj5uv2V3sBgk/HehdPaLhV7fVRy+r0+NQYdX6dwXE/p9KPaPex4mkeyoBkq/pR1/M1JUroSDpYzlSHeu2IEyUrW/unCbzX0MkHpTOvdbVy9y3shc9NS0z12sL3mzn+5U/vbTgmLnuOdhEsnsQOkvV+xAGam4u/NWJHCk7/buggzS7vcIffw/n/d9STIXPuOj2DvueTAtSDtQ9irjLm5D50v4tPEm4lyxA2VkCu+CDHJ3Z7Azk2SDnKmUzp39RrE97nmYRLJRGShTGMcW1pDMg+n4m5EiWsAtdOdL+BqTOFfsQBmZBn2JvV7U8TMueev3GIKSuqnYHvc8mBbkKAyUOVQQ18YSuvNgOv5mpIg2UEayCWxC54odKCNT+/zFBQmCW+xmL/GakCL69fwFXwXGr8nBlHqg2Bz3PJgW5FEPlCm4cjBf9p+ppB1/M1JEAUzV4UuYK3agjEx9LeSoIs9O4vo5S+Hp5xeK7XHPg2lBHvVAmYkka0biPJiOvxkpogCm6vAlzBU7UEam9vkLG3zVyuxmTL3hQdEaO4UWPkD7YYPd3inZVGyOex5MC/KoB8oUXDmYL/YMJe4FXNnLFztQduMp44BN9oZwdyeun7MlhX5zJHyp+Dje4SU6OKo8mBbkUQ8U3RwqiGtjCd15MB1/M1JEAUzV4UuYK3agjEynZaBUkVxkbSzhqPJgWpCjMFB00xhDB6tI5sF0/M1IEQUwVYcvYa7YgTIyjdhAWWagXGO3Zwr9dBWOHCzv4s2TB9OCzHtMVRQxUHqVdvzNSBGl3Tjr8CXMFTtQiq4M/fHo7Qu0JGMjNlCGdobiwXTjz7t48zSsY6rCDpQwhTw15PQNFEfCz6cje93EomRolAZKO+PPYhnkWZ6ZSHcdPIbeHkUeTAvSDpS90m6cdfgS5kq4AEwp5Kkhp2+gLOMautuEfu1Pz0ZloPTzil0GylPFtt+uwpGwDr7DOo4qD6YFaQfKXgFM1eFLmCt2oBTZWzhysDp86dGoDBSO4zab++gdx6DYDNJ4ZB1HnQfTgrQDZa+0G2cdvoS5YgdKkQUwVYcvPWIhp/17cyzmogfK7mtQtj+85SMp82bPSkrqiq2uAkfCZyP+inUU1RT09XewCr3N2him8BtsYQ3JPJgW5KgMlCmM4+9YQwfdeZhEsmakiNJunHX4EuaKHShFFsBUHb70aBQGSrufHz2p5D3vv6zYrSK5cHwJb/x5cyVcUHEdPIbe9moMHiqIa0l4F6s7fRnTghyFgXIVjuzVxjN0EOfBdPzNSBEFMFWHL2Gu2IFSZAFM1eFLj456oPTzkvswtfsjExR7c6igO32DX5R86eucQ7If8BK9msYVJPsOLdnLwySSPcc6Bq2KPAPFkXCgJHuFVcSlHX8zUkQBTNXhS5grdqAUWQBTdfjSo6McKBtfT83uBDsP+Qgy3dXRlbi7M/HLT75ifx6mltDGoE3jCpK9wzP06iocOVgzEudhEsmOeqDMRJK1JByKcWnH34wUUdqNsw5fwlyxA6XIApiqw5ceHdVA4W7OXenzl6/z7M6L3/3ykyukMA9TeRekXkxasnd4hl55yLLQPJgul/f4qxjGQEl+/GnH34wUUdqNsw5fwlyxA6XIApiqw5ceHeZA0Q++ftr5/2vqU9DgsB3ps1J0diKkMA9TeRfkTCRZckGl5SHLQvNgulze46/CDpQwhTw1xA6UuDp86VFRA0UPC56pucXuwZQqB0FQU8G+r0FfcXbylLOTWXZ3U5iHqbwLciaSLLmg0vKQZaF5MF0u7/FXYQdKmEKeGmIHSlwdvvSoqIHS/uriIofyrQwjntkpnT1b6/4ZLArzMJV3Qc5EkiUXVFoesiw0D6bL5T3+KuxACVPIU0PsQImrw5ceFTFQNs5fcHeC1K9p7kql0vWJn9eW2f1nCvMwlXdBzkSSJRdUWh6yLDQPpsvlPf4q7EAJU8hTQ+xAiavDlx4VMVA4O3nNYdTYLT6lHlR++fE2e/tSmIepvAtyJpIsuaDS8pBloXkwXe47tGTwHAmfaTL1J/RqJpLsHZ4hLu34m5G8uZI+KL5BS8IcCV8ubqoOXwbve7hysAfovlH6Yh4oycuNeo7k/FzmHSj8e/35uo8hpHZfcyKGFOZhalgDpYPH0Nu0xjGHMSR7hVXEeTAtyJaEQ2XQrsIRc0toI60xzGEcyZqROA+m429G8uRKeKOqwZRCd2k34hXcgS/9VcYtNMScvs4FxC2KyLcwdRuPsIlRroaH0FtTE9jEZ2MgBGyy1zVQogdi3/LVLPNmwanUYaJTmIepYQ0U3RbWkNY0xmBqCW3EubL/1bjdtWT/ZbOmF3gVaXXwF+itKUfCF/aZ8mX/x+5hEsmakSzVcB+uZO8pZtHdMq7hsPoGLdlrFk9wUnuDGnqWZ6AM9BPzs9T1PtJSmIepYQ6UQfuAx+iuirTHOkaxRdk/iDzkGSiOiLxGGf1Uhy/7cyW8e3IYvYArB2uJyL/gJHYdy+gZj3+scMJ4id1sRYt99xd0fQxe8ydF9teSkhvxa00+l8I8TD3HOgZNDxOtyHzZ/3/3OA+mRTlqNSPdeTAdezPSqxsSnmL30yPcEHOLkn63o6jeo4aWHMyVwxtqh9lTzCJT7X6+MU8XDZRfz1/4XuV4XcmBuN4sv2w9TmEepkZtoOhB4ou5ccxhDKNaG8/QQXeumO+yNSO9msUTZC3LDXsZ1zCM3sOV8HGZtG5I/0NylHsDVzI8dhK3+zhIP79fmIUfKNXq7xv6UlJ8jQK1XDp3ptH9GpMsHZeBsoaX6CCtCjyMoej0+81zvW08g76eZNO4gmT9fP5X0OuG9x4LaEi2GhI+EPoliuoF9HWuoFeuhMd7Cce1fj/n+9JD5dP2h2WV5ee58jQuZzTXGECODBJDJBDxuZ7lM2d+u5z1jCSZwjxM9XODNjUTydMH/A/WkKUx/Dt+jyLqoCXhMPsd9Mcziazpf7+KJj6Xh+7rXYMv2SvjhpjPPFoSXtcyNtFPZczCFRFHBs+X8P2voN9mUYMrx6eWhB+zL+F+rqLv/q2x4F1JbxNl9EwFqsV/WkIlVVqRM0FrovXjCm/mTmEepp5jHYM2E0nWhl4wY0hrC/py2qBV0b1I++0dfkUH3Y1DX6/epqX/TRvryJoj4VmWfr/9/DubbWRSmIepY0UvWCewWaznbDsQLHZbIVlB4rNZissPVBuiPnxjGZk0P6AKpK1JN9L4m22YVfGt9DbF/DlYPrvHQlvz4+QzJHwMrpHaMkpSOEqHDG3hpaEP7E9a+OYQhWmXmEVcRVM4wu0JPyZs92NYQYVtNFEBzbbMNJD5DUc2WtRwt/6F/cEs4hbwWXEzeIhyoi7jmWc6BSmcQWH1RLa0OkhMYfuWrJ3BqOHyR+ht3FbWEIHNlvRLeAWkl3GCmbxBMnuYAG6t3Bkf5uYwIlOQfdH6DOEYbcGX/a6CkcOpgdGGzORZL6E12WzFZ0v5h+hcBOLEr5I7S6SPcBt6AKYUjjRxR9gBXMYZh/w3+ggzsMkkj3HOmYiyZoRm63oFnALyS5jBbN4gmR3sABdSw5+g+N7lHGiiweKzpHwlXhjKLo2fAm33elhoXWnB84StjAFVw4WDxybrej0ol9B90B4gNuIW8Y1xL1BDXGuhJf5EnHXof/sRKfQ3TiuwJHiamIVelAkG4OHCuJ82X93xpVwsMT9gJew2YZVGbNwJLw9+nIw/fc1tCS8K5TMkfAZVN2ihJc78SmYGsck9HbQ2lhHB72q4gu8wxaSVSL6OjWbzTaC/QPIlGTDjc2UigAAAABJRU5ErkJgg" alt="پالاز آنلاین">
        </a>

        <div class="search search-real">
            <span aria-hidden="true">⌕</span>
            <input type="search" placeholder="جستجوی محصول، مدل یا رنگ..." aria-label="جستجوی محصول">
            <button type="button" aria-label="جستجو">جستجو</button>
        </div>

        <nav class="header-actions" aria-label="دسترسی سریع">
            <a href="#" class="header-action"><span class="action-icon">♙</span><span>حساب من</span></a>
            <a href="#" class="header-action"><span class="action-icon">♡</span><span>علاقه‌مندی</span></a>
            <a href="{{ route('cart') }}" class="header-action cart"><span class="action-icon">🛒</span><span>سبد خرید</span><b>0</b></a>
        </nav>
    </div>

    <nav class="main-nav" aria-label="منوی اصلی">
        <div class="container nav-inner">
            <a class="active" href="{{ route('home') }}">خانه</a>
            <a href="{{ route('shop') }}">فروشگاه</a>
            <div class="nav-dropdown">
                <span>محصولات <small>⌄</small></span>
                <div class="mega" aria-label="دسته‌بندی محصولات">
                    <a href="{{ route('shop') }}"><b>موکت</b><small>کالکشن‌های موکت پالاز</small></a>
                    <a href="{{ route('shop') }}"><b>لمینیت</b><small>کفپوش‌های لمینیت</small></a>
                    <a href="{{ route('shop') }}"><b>SPC</b><small>کفپوش مدرن و مقاوم</small></a>
                    <a href="{{ route('shop') }}"><b>کاغذدیواری</b><small>طرح‌ها و رنگ‌های متنوع</small></a>
                    <a href="{{ route('shop') }}"><b>موکت تایل</b><small>راهکارهای مدولار</small></a>
                    <a href="{{ route('shop') }}"><b>چمن مصنوعی</b><small>برای فضای سبز</small></a>
                </div>
            </div>
            <a href="{{ route('services') }}">خدمات</a>
            <a href="#">طراحی فضای من</a>
            <a href="#">مجله پالاز</a>
            <a href="#">درباره پالاز</a>
        </div>
    </nav>
</header>

<main>
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <span class="eyebrow">PALAZ ONLINE</span>
            <h1>خانه‌ای که<br><em>با انتخاب شما</em><br>ساخته می‌شود.</h1>
            <p>محصول، مشاوره، اندازه‌گیری و نصب؛ همه در یک مسیر ساده و حرفه‌ای.</p>
            <div class="hero-buttons"><a class="btn btn-primary" href="#">مشاهده محصولات</a><a class="btn btn-ghost" href="#">مشاوره خرید ←</a></div>
        </div>
        <div class="hero-art" aria-hidden="true"><div class="art-card art-one"></div><div class="art-card art-two"></div><div class="art-card art-three"></div><div class="art-caption">DESIGNED<br>FOR LIVING</div></div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow">EXPLORE</span><h2>انتخاب شما، شروع مسیر است</h2></div><a href="#">مشاهده همه ←</a></div>
        <div class="category-grid">
            @foreach(['موکت','لمینیت','SPC','کاغذدیواری','موکت تایل','چمن مصنوعی','پادری','خانه پالاز'] as $i => $category)
                <a class="category-card c{{ $i+1 }}" href="#"><span>{{ str_pad($i+1,2,'0',STR_PAD_LEFT) }}</span><strong>{{ $category }}</strong><b>مشاهده ←</b></a>
            @endforeach
        </div>
    </div>
</section>

<section class="service-section">
    <div class="container">
        <div class="section-head light"><div><span class="eyebrow">CONNECTED SERVICES</span><h2>از انتخاب تا اجرا، کنار شما هستیم</h2></div></div>
        <div class="service-grid">
            <a href="#" class="service-card"><span class="service-icon">⌖</span><div><h3>اندازه‌گیری دقیق</h3><p>درخواست اندازه‌گیری مستقیماً به DTZ ارسال می‌شود.</p><small>ارسال به DTZ ←</small></div></a>
            <a href="#" class="service-card"><span class="service-icon">⌂</span><div><h3>نصب حرفه‌ای</h3><p>هماهنگی نصب و پیگیری در مسیر خدمات پالاز.</p><small>ارسال به DTZ ←</small></div></a>
            <a href="#" class="service-card"><span class="service-icon">◇</span><div><h3>طراحی و محاسبه</h3><p>پلان و محاسبات پروژه در DTZ Tablet انجام می‌شود.</p><small>ورود به DTZ Tablet ←</small></div></a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-head"><div><span class="eyebrow">SELECTED</span><h2>محصولات منتخب پالاز</h2></div><a href="#">فروشگاه ←</a></div>
        <div class="product-grid">
            @foreach(['موکت پالاز مدل آرتا','لمینیت پالاز مدل کلاسیک','کاغذدیواری پالاز','موکت تایل پالاز'] as $i => $product)
            <article class="product-card"><div class="product-visual pv{{ $i+1 }}"><span>PALAZ</span></div><div class="product-info"><small>PALAZ COLLECTION</small><h3>{{ $product }}</h3><p>انتخاب رنگ و مشخصات</p><strong>مشاهده محصول ←</strong></div></article>
            @endforeach
        </div>
    </div>
</section>
</main>

<footer class="footer"><div class="container footer-grid"><div><div class="footer-brand">PALAZ ONLINE</div><p>فروش آنلاین محصولات و خدمات پالاز.</p></div><div><h4>فروشگاه</h4><a href="#">موکت</a><a href="#">لمینیت</a><a href="#">SPC</a></div><div><h4>خدمات</h4><a href="#">مشاوره</a><a href="#">اندازه‌گیری</a><a href="#">نصب</a></div><div><h4>پشتیبانی</h4><a href="#">پیگیری سفارش</a><a href="#">تماس با ما</a><a href="#">سوالات متداول</a></div></div></footer>
</body>
</html>