<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Models\SellerProfile;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function edit()
    {
        $profile = SellerProfile::firstOrCreate(['user_id' => auth()->id()]);

        return view('seller.profile', compact('profile'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'ktp_number'   => ['nullable', 'string', 'max:30'],
            'bank_name'    => ['nullable', 'string', 'max:100'],
            'bank_account' => ['nullable', 'string', 'max:50'],
        ]);

        // is_verified TIDAK boleh diubah penjual; itu tugas admin.
        $profile = SellerProfile::firstOrCreate(['user_id' => auth()->id()]);
        $profile->update($data);

        return redirect()->route('seller.profile.edit')
            ->with('status', 'Profil berhasil disimpan.');
    }
}