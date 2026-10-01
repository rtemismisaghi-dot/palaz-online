<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
use App\Models\CustomerUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function submit(Request $request)
    {
        $data = $request->validate(['mobile' => ['required', 'string', 'max:30']]);
        $mobile = preg_replace('/\D+/', '', $data['mobile']);
        $admin = AdminUser::where('mobile', $mobile)->first();
        // Local test account until the real SMS provider is connected.
        if (!$admin && $mobile === '09209075332') {
            return view('auth.login', ['adminMobile' => $mobile]);
        }

        if ($admin) return view('auth.login', ['adminMobile' => $mobile]);

        $customer = CustomerUser::firstOrCreate(['mobile' => $mobile]);
        $request->session()->regenerate();
        $request->session()->put(['customer_user_id' => $customer->id, 'customer_mobile' => $customer->mobile]);
        return redirect()->intended(route('checkout'));

        $request->session()->put('login_otp_hash', Hash::make($otp));
        $request->session()->put('login_otp_mobile', $mobile);
        $request->session()->put('login_otp_expires', now()->addSeconds((int) env('ADMIN_OTP_EXPIRE', 120))->timestamp);

        $url = env('ADMIN_SMS_API_URL');
        if (!$url) {
            $request->session()->forget(['login_otp_hash', 'login_otp_mobile', 'login_otp_expires']);
            return back()->withErrors(['mobile' => 'سرویس پیامک هنوز تنظیم نشده است.'])->withInput();
        }

        $response = Http::timeout((int) env('ADMIN_SMS_TIMEOUT', 10))->post($url, [
            'to' => $mobile,
            'message' => "کد ورود پالاز آنلاین: {$otp}",
            'sender' => env('ADMIN_SMS_SENDER'),
            'api_key' => env('ADMIN_SMS_API_KEY'),
        ]);
        if ($response->failed()) {
            $request->session()->forget(['login_otp_hash', 'login_otp_mobile', 'login_otp_expires']);
            return back()->withErrors(['mobile' => 'ارسال پیامک انجام نشد.'])->withInput();
        }

        return view('auth.login', ['otpSent' => true, 'mobile' => $mobile]);
    }

    public function customerLogin(Request $request)
    {
        $data = $request->validate([
            'mobile' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string'],
        ]);

        $mobile = preg_replace('/\D+/', '', $data['mobile']);
        $customer = CustomerUser::where('mobile', $mobile)->first();

        if (!$customer) {
            $customer = CustomerUser::create([
                'mobile' => $mobile,
                'password' => Hash::make($data['password']),
            ]);
        } elseif (!$customer->password) {
            $customer->forceFill(['password' => Hash::make($data['password'])])->save();
        }

        if (!$customer || !Hash::check($data['password'], $customer->password)) {
            return back()->withErrors(['password' => 'شماره موبایل یا رمز عبور صحیح نیست.'])->withInput();
        }

        $request->session()->regenerate();
        $request->session()->put(['customer_user_id' => $customer->id, 'customer_mobile' => $customer->mobile]);

        return redirect()->intended(route('checkout'));
    }

    public function verify(Request $request)
    {
        $data = $request->validate(['mobile' => ['required', 'string'], 'otp' => ['required', 'digits:6']]);
        $mobile = preg_replace('/\D+/', '', $data['mobile']);

        if ($request->session()->get('login_otp_mobile') !== $mobile ||
            $request->session()->get('login_otp_expires', 0) < now()->timestamp ||
            !Hash::check($data['otp'], $request->session()->get('login_otp_hash', ''))) {
            return back()->withErrors(['otp' => 'کد ورود صحیح یا معتبر نیست.'])->withInput();
        }

        $request->session()->forget(['login_otp_hash', 'login_otp_mobile', 'login_otp_expires']);
        $request->session()->regenerate();
        $customer = CustomerUser::firstOrCreate(['mobile' => $mobile]);
        $request->session()->put(['customer_user_id' => $customer->id, 'customer_mobile' => $customer->mobile]);

        return redirect()->intended(route('home'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['customer_user_id', 'customer_mobile']);
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
