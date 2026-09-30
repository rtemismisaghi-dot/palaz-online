@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div><h1>دسته‌بندی‌ها</h1><p class="text-secondary mb-0">نوع محصولات و ترتیب نمایش فروشگاه.</p></div>
    <a class="btn btn-palaz" href="{{ route('admin.categories.create') }}">+ دسته‌بندی جدید</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead><tr><th>نام</th><th>Slug</th><th>محصولات</th><th>ترتیب</th><th>وضعیت</th><th></th></tr></thead>
            <tbody>
            @forelse($categories as $category)
                <tr>
                    <td>{{ $category->name }}</td><td>{{ $category->slug }}</td><td>{{ $category->products_count }}</td>
                    <td>{{ $category->sort_order }}</td><td>{{ $category->is_active ? 'فعال' : 'غیرفعال' }}</td>
                    <td class="text-nowrap">
                        <a class="me-2" href="{{ route('admin.categories.edit',$category) }}">ویرایش</a>
                        <form class="d-inline" method="post" action="{{ route('admin.categories.destroy',$category) }}" onsubmit="return confirm('این دسته‌بندی حذف شود؟')">
                            @csrf @method('DELETE')
                            <button class="btn btn-link text-danger p-0">حذف</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center py-5">هنوز دسته‌بندی ثبت نشده.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
