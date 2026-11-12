<?php

namespace App\Http\Requests\Seller;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validasi data KTP & rekening penjual (dipakai admin untuk verifikasi dan pencairan saldo).
 */
class UpdateSellerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Akses /seller sudah dibatasi middleware role:seller
        return true;
    }

    protected function prepareForValidation(): void
    {
        // Buang spasi / tanda hubung yang sering ikut diketik
        $this->merge([
            'ktp_number'   => preg_replace('/[\s\-.]/', '', (string) $this->input('ktp_number')),
            'bank_account' => preg_replace('/[\s\-.]/', '', (string) $this->input('bank_account')),
            'bank_name'    => trim((string) $this->input('bank_name')),
        ]);
    }

    public function rules(): array
    {
        return [
            'ktp_number'   => ['required', 'digits:16'],
            'bank_name'    => ['required', 'string', 'max:50'],
            'bank_account' => ['required', 'digits_between:6,20'],
        ];
    }

    public function messages(): array
    {
        return [
            'ktp_number.required'         => 'Nomor KTP wajib diisi.',
            'ktp_number.digits'           => 'Nomor KTP (NIK) harus 16 digit angka.',
            'bank_name.required'          => 'Nama bank wajib diisi.',
            'bank_name.max'               => 'Nama bank maksimal :max karakter.',
            'bank_account.required'       => 'Nomor rekening wajib diisi.',
            'bank_account.digits_between' => 'Nomor rekening harus berupa angka, 6 sampai 20 digit.',
        ];
    }
}
