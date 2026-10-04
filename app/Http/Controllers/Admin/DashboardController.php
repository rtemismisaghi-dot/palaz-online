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
        $products = Product::with('media')->latest('id')->limit(8)->get();

        return view('admin.dashboard', ['products' => $products, 'stats' => [
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

    public function sales(Request $request)
    {
        $query = Product::query()
            ->with(['category', 'media', 'pricingRule', 'inventoryRolls'])
            ->where('is_active', true);

        if ($request->filled('q')) {
            $q = trim((string) $request->input('q'));
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'ilike', "%{$q}%")
                    ->orWhere('slug', 'ilike', "%{$q}%");
            });
        }

        $products = $query->orderBy('name')->orderBy('slug')->get();
        $categories = Category::orderBy('sort_order')->orderBy('name')->get();

        return view('sales.index', compact('products', 'categories'));
    }

    public function installation()
    {
        return view('admin.dashboard', ['stats' => [], 'staffArea' => 'installation']);
    }
}
