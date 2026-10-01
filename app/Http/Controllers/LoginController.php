<?php

namespace App\Http\Controllers;

use App\Models\AdminUser;
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
        $data = $request->validate([
            'mobile' => ['required', 'string', 'max:30'],
        ]);

        $mobile = preg_replace('/\D+/', '', $data['mobile']);
        $admin = AdminUser::where('mobile', $mobile)->first();

        if ($admin) {
            return view('auth.login', ['adminMobile' => $mobile]);
        }

        $otp = (string) random_int(100000, 999999);
        $request->session()->put('login_otp_hash', Hash::make($otp));
        $request->session()->put('login_otp_mobile', $mobile);
        $request->session()->put('login_otp_expires', now()->addSeconds((int) env('ADMIN_OTP_EXPIRE', 120))->timestamp);

        $url = env('ADMIN_SMS_API_URL');
        if ($url) {
            $payload = [
                'to' => $mobile,
                'message' => "کد ورود پالاز آنلاین: {$otp}",
                'sender' => env('ADMIN_SMS_SENDER'),
                'api_key' => env('ADMIN_SMS_API_KEY'),
            ];
            $response = Http::timeout((int) env('ADMIN_SMS_TIMEOUT', 10))->post($url, $payload);
            if ($response->failed()) {
                $request->session()->forget(['login_otp_hash', 'login_otp_mobile', 'login_otp_expires']);
                return back()->withErrors(['mobile' => 'ارسال پیامک انجام نشد.'])->withInput();
            }
        } else {
            $request->session()->forget(['login_otp_hash', 'login_otp_mobile', 'login_otp_expires']);
            return back()->withErrors(['mobile' => 'سرویس پیامک هنوز تنظیم نشده است.'])->withInput();
        }

        return view('auth.login', ['otpSent' => true, 'mobile' => $mobile]);
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'mobile' => ['required', 'string'],
            'otp' => ['required', 'digits:6'],
        ]);

        $mobile = preg_replace('/\D+/', '', $data['mobile']);
        if ($request->session()->get('login_otp_mobile') !== $mobile ||
            $request->session()->get('login_otp_expires', 0) < now()->timestamp ||
            !Hash::check($data['otp'], $request->session()->get('login_otp_hash', ''))) {
            return back()->withErrors(['otp' => 'کد ورود صحیح یا معتبر نیست.'])->withInput();
        }

        $request->session()->forget(['login_otp_hash', 'login_otp_mobile', 'login_otp_expires']);
        $request->session()->regenerate();
        $request->session()->put('customer_mobile', $mobile);

        return redirect()->intended(route('home'));
    }
}
