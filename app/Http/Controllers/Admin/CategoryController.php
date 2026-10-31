<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('name')->get();
        $counts = Service::selectRaw('category_id, count(*) as total')
            ->groupBy('category_id')
            ->pluck('total', 'category_id');

        return view('admin.categories.index', compact('categories', 'counts'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(
            ['name' => ['required', 'string', 'max:100', 'unique:categories,name']],
            ['name.required' => 'Nama kategori wajib diisi.', 'name.unique' => 'Kategori sudah ada.']
        );

        Category::create(['name' => $data['name'], 'slug' => Str::slug($data['name'])]);

        return back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate(
            ['name' => ['required', 'string', 'max:100', Rule::unique('categories', 'name')->ignore($category->id)]],
            ['name.required' => 'Nama kategori wajib diisi.', 'name.unique' => 'Kategori sudah ada.']
        );

        $category->update(['name' => $data['name'], 'slug' => Str::slug($data['name'])]);

        return back()->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(Category $category)
    {
        if (Service::where('category_id', $category->id)->exists()) {
            return back()->with('error', 'Kategori masih dipakai oleh jasa, tidak bisa dihapus.');
        }

        $category->delete();

        return back()->with('success', 'Kategori berhasil dihapus.');
    }
}