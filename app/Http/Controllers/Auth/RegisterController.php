<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create($request->validated()); // password otomatis di-hash oleh cast di model User

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === 'seller') {
            SellerProfile::create([
                'user_id'      => $user->id,
                'ktp_number'   => '',
                'bank_name'    => '',
                'bank_account' => '',
            ]);

            // Penjual baru diarahkan untuk melengkapi KTP dan rekening
            return redirect()->route('seller.profile.edit')
                ->with('info', 'Akun penjual berhasil dibuat. Lengkapi data KTP dan rekening agar bisa diverifikasi admin.');
        }

        return redirect()->route('dashboard');
    }
}
