<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\UpdateSellerProfileRequest;
use App\Models\SellerProfile;

class ProfileController extends Controller
{
    public function edit()
    {
        $profile = SellerProfile::firstOrCreate(['user_id' => auth()->id()]);

        return view('seller.profile', compact('profile'));
    }

    public function update(UpdateSellerProfileRequest $request)
    {
        $profile = SellerProfile::firstOrCreate(['user_id' => $request->user()->id]);
        $profile->fill($request->validated());

        // is_verified hanya diubah admin. Jika data yang sudah diverifikasi diganti,
        // status kembali "menunggu" supaya admin memeriksa ulang.
        $needsReverify = $profile->is_verified && $profile->isDirty(['ktp_number', 'bank_account']);
        if ($needsReverify) {
            $profile->is_verified = false;
        }

        $profile->save();

        return redirect()->route('seller.profile.edit')->with(
            $needsReverify ? 'warning' : 'status',
            $needsReverify
                ? 'Profil disimpan. Karena KTP atau nomor rekening berubah, akunmu perlu diverifikasi ulang oleh admin.'
                : 'Profil berhasil disimpan.'
        );
    }
}
