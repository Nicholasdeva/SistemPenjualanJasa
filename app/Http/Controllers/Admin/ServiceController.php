<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Service;
use App\Models\User;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderByDesc('created_at')->get();

        $sellers    = User::whereIn('id', $services->pluck('seller_id'))->pluck('name', 'id');
        $categories = Category::whereIn('id', $services->pluck('category_id'))->pluck('name', 'id');

        return view('admin.services.index', compact('services', 'sellers', 'categories'));
    }

    public function activate(Service $service)
    {
        $service->status = 'published';
        $service->save();

        return back()->with('success', 'Jasa ditayangkan.');
    }

    public function deactivate(Service $service)
    {
        $service->status = 'draft';
        $service->save();

        return back()->with('success', 'Jasa diturunkan ke draft.');
    }
}