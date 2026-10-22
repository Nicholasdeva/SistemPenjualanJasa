<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\SellerProfile;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $data['role'],
            ]);

            if ($data['role'] === 'seller') {
                SellerProfile::create([
                    'user_id' => $user->id,
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        if ($user->role === 'seller') {
            return redirect()
                ->route('seller.profile.edit')
                ->with(
                    'status',
                    'Akun berhasil dibuat. Lengkapi data KTP dan rekening kau.'
                );
        }

        return redirect()
            ->route('dashboard')
            ->with(
                'status',
                'Akun berhasil dibuat. Selamat bergabung!'
            );
    }
}
