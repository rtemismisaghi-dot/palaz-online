<?php

namespace App\\Http\\Controllers\\Admin;

use App\\Http\\Controllers\\Controller;
use App\\Models\\AdminUser;
use Illuminate\\Http\\Request;
use Illuminate\\Support\\Facades\\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        $hasAdmin = AdminUser::query()->exists();

        return view('admin.auth.login', compact('hasAdmin'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $admin = AdminUser::where('email', $credentials['email'])->first();

        if (!$admin && !AdminUser::exists() && app()->environment('local')) {
            $admin = AdminUser::create([
                'name' => 'مدیر پالاز',
                'email' => $credentials['email'],
                'password' => Hash::make($credentials['password']),
            ]);
        }

        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            return back()->withErrors(['email' => 'ایمیل یا رمز عبور صحیح نیست.'])->withInput($request->only('email'));
        }

        $request->session()->regenerate();
        $request->session()->put('admin_user_id', $admin->id);
        $request->session()->put('admin_user_name', $admin->name);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['admin_user_id', 'admin_user_name']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
