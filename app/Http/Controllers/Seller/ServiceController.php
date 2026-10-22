<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with(['category', 'packages'])
            ->where('seller_id', auth()->id())
            ->latest()
            ->get();

        return view('seller.services.index', compact('services'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('seller.services.form', compact('categories'));
    }

    // Tahap 1: dummy. Penyimpanan asli + StoreServiceRequest dikerjakan di Tahap 2.
    public function store(Request $request)
    {
        $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string'],
        ]);

        return redirect()->route('seller.services.index')
            ->with('status', 'Jasa berhasil ditambah (dummy, belum disimpan ke database).');
    }

    public function edit(Service $service)
    {
        $this->ensureOwner($service);

        $service->load('packages');
        $categories = Category::orderBy('name')->get();

        return view('seller.services.form', compact('service', 'categories'));
    }

    public function update(Request $request, Service $service)
    {
        $this->ensureOwner($service);

        $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string'],
        ]);

        return redirect()->route('seller.services.index')
            ->with('status', 'Jasa berhasil diupdate (dummy, belum disimpan ke database).');
    }

    public function destroy(Service $service)
    {
        $this->ensureOwner($service);

        return redirect()->route('seller.services.index')
            ->with('status', 'Jasa dihapus (dummy, belum dihapus dari database).');
    }

    // Digantikan ServicePolicy di Tahap 2
    private function ensureOwner(Service $service): void
    {
        abort_unless($service->seller_id === auth()->id(), 403, 'Ini bukan jasa Anda.');
    }
}