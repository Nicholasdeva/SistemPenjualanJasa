<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfileRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', ['user' => request()->user()]);
    }

    public function update(UpdateProfileRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        $oldAvatar = $user->avatar_url;

        $user->name = $data['name'];

        if ($request->filled('password')) {
            $user->password = Hash::make($data['password']);
        }

        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('avatars', 'public');
            if (! $avatarPath) {
                throw new RuntimeException('Avatar gagal disimpan ke storage.');
            }

            $user->avatar_url = $avatarPath;
        }

        $user->save();

        if ($oldAvatar && $oldAvatar !== $user->avatar_url) {
            Storage::disk('public')->delete($oldAvatar);
        }

        return redirect()->route('profile.edit')->with('status', 'Profil berhasil diperbarui.');
    }
}
