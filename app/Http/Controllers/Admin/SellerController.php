<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class SellerController extends Controller
{
    public function index()
    {
        $sellers = User::where('role', 'seller')
            ->with('sellerProfile')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.sellers.index', compact('sellers'));
    }

    public function verify(User $user)
    {
        return $this->setVerified($user, true, 'Penjual berhasil diverifikasi.');
    }

    public function unverify(User $user)
    {
        return $this->setVerified($user, false, 'Verifikasi penjual dicabut.');
    }

    private function setVerified(User $user, bool $value, string $message)
    {
        abort_unless($user->role === 'seller' && $user->sellerProfile, 404);

        $profile = $user->sellerProfile;
        $profile->is_verified = $value;
        $profile->save();

        return back()->with('success', $message);
    }
}