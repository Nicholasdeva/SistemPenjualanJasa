<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\StoreServiceRequest;
use App\Http\Requests\Seller\UpdateServiceRequest;
use App\Models\Category;
use App\Models\Order;
use App\Models\Service;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with(['category', 'packages'])
            ->withCount('orders')
            ->where('seller_id', auth()->id())
            ->latest()
            ->get();

        $isVerified = (bool) optional(auth()->user()->sellerProfile)->is_verified;

        return view('seller.services.index', compact('services', 'isVerified'));
    }

    public function create()
    {
        Gate::authorize('create', Service::class);

        $categories = Category::orderBy('name')->get();

        return view('seller.services.form', compact('categories'));
    }

    public function store(StoreServiceRequest $request)
    {
        $data = $request->validated();

        DB::transaction(function () use ($request, $data) {
            $service = Service::create([
                'seller_id'     => $request->user()->id,
                'category_id'   => $data['category_id'],
                'title'         => $data['title'],
                'description'   => $data['description'],
                'status'        => $data['status'],
                'thumbnail_url' => $this->storeThumbnail($request->file('thumbnail')),
            ]);

            $this->syncPackages($service, $request->filledPackages());
        });

        return redirect()->route('seller.services.index')
            ->with('status', 'Jasa berhasil ditambahkan.');
    }

    public function edit(Service $service)
    {
        Gate::authorize('update', $service);

        $service->load('packages');
        $categories = Category::orderBy('name')->get();

        return view('seller.services.form', compact('service', 'categories'));
    }

    public function update(UpdateServiceRequest $request, Service $service)
    {
        // Otorisasi pemilik sudah dicek di UpdateServiceRequest::authorize()
        $data = $request->validated();
        $oldThumbnail = $service->thumbnail_url;

        DB::transaction(function () use ($request, $service, $data) {
            $service->fill([
                'category_id' => $data['category_id'],
                'title'       => $data['title'],
                'description' => $data['description'],
                'status'      => $data['status'],
            ]);

            if ($request->hasFile('thumbnail')) {
                $service->thumbnail_url = $this->storeThumbnail($request->file('thumbnail'));
            } elseif ($request->boolean('remove_thumbnail')) {
                $service->thumbnail_url = null;
            }

            $service->save();

            $this->syncPackages($service, $request->filledPackages());
        });

        // Hapus file lama setelah data tersimpan
        if ($oldThumbnail && $oldThumbnail !== $service->thumbnail_url) {
            Storage::disk('public')->delete($oldThumbnail);
        }

        return redirect()->route('seller.services.index')
            ->with('status', 'Jasa berhasil diperbarui.');
    }

    public function destroy(Service $service)
    {
        Gate::authorize('delete', $service);

        // Jasa yang sudah pernah dipesan tidak boleh dihapus (riwayat pesanan harus tetap ada)
        $packageIds = $service->packages()->pluck('id');
        if (Order::whereIn('service_package_id', $packageIds)->exists()) {
            return redirect()->route('seller.services.index')
                ->with('error', 'Jasa sudah pernah dipesan sehingga tidak bisa dihapus. Ubah statusnya menjadi draft untuk menyembunyikannya.');
        }

        $thumbnail = $service->thumbnail_url;
        $service->delete();

        if ($thumbnail) {
            Storage::disk('public')->delete($thumbnail);
        }

        return redirect()->route('seller.services.index')
            ->with('status', 'Jasa berhasil dihapus.');
    }

    private function storeThumbnail(?UploadedFile $file): ?string
    {
        return $file ? $file->store('services', 'public') : null;
    }

    /**
     * Simpan paket yang diisi. Paket yang dikosongkan dihapus,
     * kecuali sudah pernah dipesan (agar riwayat pesanan tidak rusak).
     *
     * @param  array<string, array{price: float, delivery_days: int, revision_limit: int}>  $packages
     */
    private function syncPackages(Service $service, array $packages): void
    {
        foreach ($packages as $tier => $values) {
            $service->packages()->updateOrCreate(['tier_name' => $tier], $values);
        }

        $service->packages()
            ->whereNotIn('tier_name', array_keys($packages))
            ->whereDoesntHave('orders')
            ->delete();
    }
}
