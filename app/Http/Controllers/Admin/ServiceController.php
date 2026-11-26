<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Support\Facades\DB;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with(['seller:id,name', 'category:id,name'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.services.index', compact('services'));
    }

    public function activate(Service $service)
    {
        return $this->setStatus($service, Service::STATUS_DRAFT, Service::STATUS_PUBLISHED, 'Jasa ditayangkan.');
    }

    public function deactivate(Service $service)
    {
        return $this->setStatus($service, Service::STATUS_PUBLISHED, Service::STATUS_DRAFT, 'Jasa diturunkan ke draft.');
    }

    private function setStatus(Service $service, string $expected, string $status, string $message)
    {
        $updated = DB::transaction(function () use ($service, $expected, $status) {
            $locked = Service::whereKey($service->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== $expected) {
                return false;
            }

            $locked->status = $status;
            $locked->moderated_by = auth()->id();
            $locked->moderation_locked = $status === Service::STATUS_DRAFT;
            $locked->save();

            return true;
        });

        if (! $updated) {
            return back()->with('warning', 'Status jasa telah berubah atau tidak dapat diproses dengan aksi ini.');
        }

        return back()->with('success', $message);
    }
}