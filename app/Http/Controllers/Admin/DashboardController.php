<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ServiceRequest;
use App\Services\ManagementAgentService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', ['stats' => [
            'categories' => Category::count(),
            'products' => Product::count(),
            'orders' => Order::count(),
            'services' => ServiceRequest::count(),
        ]]);
    }

    public function management(Request $request, ManagementAgentService $agent)
    {
        $data = $agent->snapshot($request->input('from'), $request->input('to'));
        return view('admin.management', compact('data'));
    }

    public function sales()
    {
        return view('admin.dashboard', ['stats' => [], 'staffArea' => 'sales']);
    }

    public function installation()
    {
        return view('admin.dashboard', ['stats' => [], 'staffArea' => 'installation']);
    }
}
