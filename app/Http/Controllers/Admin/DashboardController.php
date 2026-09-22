<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ServiceRequest;

class DashboardController extends Controller
{
    public function index() {
        return view('admin.dashboard',['stats'=>[
            'categories'=>Category::count(),'products'=>Product::count(),'orders'=>Order::count(),'services'=>ServiceRequest::count()
        ]]);
    }
}