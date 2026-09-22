<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
    public function index() { return view('admin.categories.index',['categories'=>Category::orderBy('sort_order')->orderBy('name')->get()]); }
    public function create() { return view('admin.categories.form',['category'=>new Category]); }
    public function store(Request $request) {
        $data=$request->validate(['name'=>'required|string|max:120','slug'=>'required|string|max:120|alpha_dash|unique:categories,slug','eyebrow'=>'nullable|string|max:120','tone'=>'nullable|string|max:40','sort_order'=>'nullable|integer|min:0']);
        $data['is_active']=$request->boolean('is_active'); Category::create($data);
        return redirect()->route('admin.categories.index')->with('success','دسته‌بندی ذخیره شد.');
    }
    public function edit(Category $category) { return view('admin.categories.form',compact('category')); }
    public function update(Request $request, Category $category) {
        $data=$request->validate(['name'=>'required|string|max:120','slug'=>'required|string|max:120|alpha_dash|unique:categories,slug,'.$category->id,'eyebrow'=>'nullable|string|max:120','tone'=>'nullable|string|max:40','sort_order'=>'nullable|integer|min:0']);
        $data['is_active']=$request->boolean('is_active'); $category->update($data);
        return redirect()->route('admin.categories.index')->with('success','دسته‌بندی به‌روزرسانی شد.');
    }
}