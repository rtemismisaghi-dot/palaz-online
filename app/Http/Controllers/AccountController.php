<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ServiceRequest;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index(Request $request)
    {
        $mobile = (string) $request->session()->get('customer_mobile', '');

        abort_unless($request->session()->has('customer_user_id') && $mobile !== '', 403);

        $orders = Order::query()
            ->with('items')
            ->where('phone', $mobile)
            ->latest()
            ->take(20)
            ->get();

        $services = ServiceRequest::query()
            ->where('phone', $mobile)
            ->latest()
            ->take(20)
            ->get();

        return view('account.index', compact('orders', 'services', 'mobile'));
    }
}
