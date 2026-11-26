<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\UpdateSellerProfileRequest;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SellerController extends Controller
{
    public function index()
    {
        $sellers = User::where('role', 'seller')
            ->with('sellerProfile')
            ->orderByDesc('created_at')
            ->paginate(15);

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

        if ($value) {
            $validator = Validator::make(
                $user->sellerProfile->only(['ktp_number', 'bank_name', 'bank_account']),
                UpdateSellerProfileRequest::profileRules(),
            );

            if ($validator->fails()) {
                return back()->with('warning', $validator->errors()->first());
            }
        }

        DB::transaction(function () use ($user, $value) {
            $profile = SellerProfile::where('user_id', $user->id)->lockForUpdate()->firstOrFail();
            $profile->is_verified = $value;
            $profile->verified_by = auth()->id();
            $profile->save();
        });

        return back()->with('success', $message);
    }
}