<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if (!Auth::attempt(['phone' => $data['phone'], 'password' => $data['password'], 'role' => 'staff'], $request->boolean('remember'))) {
            return back()->withErrors(['phone' => 'شماره پرسنلی یا رمز عبور صحیح نیست.'])->withInput($request->only('phone'));
        }

        $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
