<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
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

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $service = new Service();
        $service->seller_id   = auth()->id();
        $service->category_id = $data['category_id'];
        $service->title       = $data['title'];
        $service->description = $data['description'];
        $service->status      = 'published';
        $service->save();

        $this->savePackages($service, $request);

        return redirect()->route('seller.services.index')
            ->with('status', 'Jasa berhasil ditambahkan.');
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
        $data = $this->validated($request);

        $service->category_id = $data['category_id'];
        $service->title       = $data['title'];
        $service->description = $data['description'];
        $service->save();

        $this->savePackages($service, $request);

        return redirect()->route('seller.services.index')
            ->with('status', 'Jasa berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        $this->ensureOwner($service);

        // Jasa yang sudah pernah dipesan tidak boleh dihapus (migrasi memakai cascade)
        $packageIds = $service->packages()->pluck('id');
        if (Order::whereIn('service_package_id', $packageIds)->exists()) {
            return redirect()->route('seller.services.index')
                ->with('error', 'Jasa sudah pernah dipesan, tidak bisa dihapus.');
        }

        $service->delete();

        return redirect()->route('seller.services.index')
            ->with('status', 'Jasa berhasil dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title'       => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:categories,id'],
            'description' => ['required', 'string'],
        ]);
    }

    // Form mengirim packages[i][tier_name|price|delivery_days|revision_limit]
    private function savePackages(Service $service, Request $request): void
    {
        $allowed = ['Basic', 'Standard', 'Premium'];

        foreach ((array) $request->input('packages', []) as $p) {
            $tier = $p['tier_name'] ?? null;

            if (! in_array($tier, $allowed, true) || empty($p['price'])) {
                continue; // lewati paket yang harganya kosong
            }

            $values = [
                'price'          => (float) $p['price'],
                'delivery_days'  => max(1, (int) ($p['delivery_days'] ?? 1)),
                'revision_limit' => max(0, (int) ($p['revision_limit'] ?? 0)),
            ];

            $existing = $service->packages()->where('tier_name', $tier)->first();
            if ($existing) {
                $existing->forceFill($values)->save();
            } else {
                $service->packages()->forceCreate($values + ['tier_name' => $tier]);
            }
        }
    }

    // Digantikan ServicePolicy di Tahap 2
    private function ensureOwner(Service $service): void
    {
        abort_unless($service->seller_id === auth()->id(), 403, 'Ini bukan jasa Anda.');
    }
}