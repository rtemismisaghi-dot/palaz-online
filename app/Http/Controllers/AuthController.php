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
        $passwords = [
            'admin' => env('PALAZ_ADMIN_PASSWORD'),
            'sales' => env('PALAZ_SALES_PASSWORD'),
            'installation' => env('PALAZ_INSTALLATION_PASSWORD'),
        ];

        $role = null;
        foreach ($passwords as $candidateRole => $candidatePassword) {
            if ($candidatePassword && hash_equals((string) $candidatePassword, (string) $data['password'])) {
                $role = $candidateRole;
                break;
            }
        }

        if (!$phone || !$role || !hash_equals((string) $phone, (string) $data['phone'])) {
            return back()->withErrors(['phone' => 'شماره موبایل یا رمز عبور صحیح نیست.'])->withInput($request->only('phone'));
        }

        $request->session()->regenerate();
        $request->session()->put([
            'palaz_staff_authenticated' => true,
            'palaz_staff_role' => $role,
        ]);

        return redirect()->route(match ($role) {
            'admin' => 'admin.dashboard',
            'sales' => 'sales.dashboard',
            'installation' => 'installation.dashboard',
        });
    }

    public function logout(Request $request)
    {
        $request->session()->forget(['palaz_staff_authenticated', 'palaz_staff_role']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
