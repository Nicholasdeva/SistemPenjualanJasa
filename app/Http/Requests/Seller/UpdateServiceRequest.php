<?php

namespace App\Http\Requests\Seller;

/**
 * Validasi ubah jasa. Aturannya sama dengan tambah jasa,
 * ditambah otorisasi: hanya pemilik jasa yang boleh mengubah (ServicePolicy@update).
 */
class UpdateServiceRequest extends StoreServiceRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('service'));
    }

    public function rules(): array
    {
        return parent::rules() + [
            'remove_thumbnail' => ['nullable', 'boolean'],
        ];
    }
}
