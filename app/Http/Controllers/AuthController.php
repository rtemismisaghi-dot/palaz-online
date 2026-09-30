<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function staffLogin(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string|max:30',
            'password' => 'required|string',
        ]);

        $phone = env('PALAZ_STAFF_PHONE');
        $password = env('PALAZ_STAFF_PASSWORD');

        if (!$phone || !$password || !hash_equals((string) $phone, (string) $data['phone']) || !hash_equals((string) $password, (string) $data['password'])) {
            return back()->withErrors(['phone' => 'شماره پرسنلی یا رمز عبور صحیح نیست.'])->withInput($request->only('phone'));
        }

        $request->session()->regenerate();
        $request->session()->put('palaz_staff_authenticated', true);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget('palaz_staff_authenticated');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
