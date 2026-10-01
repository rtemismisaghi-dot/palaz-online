<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login', ['hasAdmin' => AdminUser::query()->exists()]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'mobile' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
        ]);

        $mobile = preg_replace('/\D+/', '', $credentials['mobile']);
        $admin = AdminUser::where('mobile', $mobile)->first();

        if (!$admin && !AdminUser::exists() && app()->environment('local') && $request->routeIs('admin.login.submit')) {
            $admin = AdminUser::create([
                'name' => 'مدیر پالاز',
                'mobile' => $mobile,
                'password' => Hash::make($credentials['password']),
            ]);
        }

        if (!$admin || !Hash::check($credentials['password'], $admin->password)) {
            return back()->withErrors(['mobile' => 'شماره موبایل یا رمز عبور صحیح نیست.'])->withInput($request->only('mobile'));
        }

        $request->session()->regenerate();
        $request->session()->put(['admin_user_id' => $admin->id, 'admin_user_name' => $admin->name]);

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
