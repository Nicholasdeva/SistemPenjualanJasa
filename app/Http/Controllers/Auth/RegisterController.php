<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\SellerProfile;
use App\Models\User;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $request)
    {
        $user = User::create($request->validated()); // password otomatis di-hash oleh cast di model User

        // Sengaja tidak auto-login: user diarahkan ke halaman login setelah registrasi
        if ($user->role === 'seller') {
            SellerProfile::create([
                'user_id'      => $user->id,
                'ktp_number'   => '',
                'bank_name'    => '',
                'bank_account' => '',
            ]);

            return redirect()->route('login')
                ->with('info', 'Akun penjual berhasil dibuat. Silakan login, lalu lengkapi data KTP dan rekening agar bisa diverifikasi admin.');
        }

        return redirect()->route('login')
            ->with('success', 'Registrasi berhasil. Silakan login dengan akun Anda.');
    }
}
