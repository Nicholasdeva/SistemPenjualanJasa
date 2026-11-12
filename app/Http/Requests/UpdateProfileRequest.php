<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama pengguna wajib diisi.',
            'name.max' => 'Nama pengguna maksimal :max karakter.',
            'password.min' => 'Password baru minimal :min karakter.',
            'password.confirmed' => 'Konfirmasi password baru tidak cocok.',
            'avatar.image' => 'Avatar harus berupa file gambar.',
            'avatar.mimes' => 'Avatar harus berformat JPG, JPEG, PNG, atau WEBP.',
            'avatar.max' => 'Ukuran avatar maksimal 2 MB.',
        ];
    }
}
