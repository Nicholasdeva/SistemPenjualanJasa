<?php

namespace App\Http\Requests\Seller;

use App\Models\Service;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Validasi tambah jasa.
 * - Paket Basic wajib, Standard & Premium boleh dikosongkan.
 * - Harga harus angka positif, hari pengerjaan minimal 1, kuota revisi minimal 0.
 * - Gambar sampul opsional: jpg/jpeg/png/webp, maksimal 2 MB.
 *
 * Form mengirim: packages[i][tier_name|price|delivery_days|revision_limit]
 */
class StoreServiceRequest extends FormRequest
{
    public const TIERS = ['Basic', 'Standard', 'Premium'];
    public const MAX_THUMBNAIL_KB = 2048;

    public function authorize(): bool
    {
        // Akses /seller sudah dibatasi middleware role:seller
        return true;
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'min:10', 'max:120'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['required', 'string', 'min:30', 'max:5000'],
            'status'      => ['required', 'in:' . Service::STATUS_PUBLISHED . ',' . Service::STATUS_DRAFT],
            'thumbnail'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:' . self::MAX_THUMBNAIL_KB],

            'packages'                  => ['required', 'array', 'max:3'],
            'packages.*.tier_name'      => ['required', 'distinct', 'in:' . implode(',', self::TIERS)],
            'packages.*.price'          => ['nullable', 'numeric', 'gt:0', 'max:999999999'],
            'packages.*.delivery_days'  => ['nullable', 'required_with:packages.*.price', 'integer', 'min:1', 'max:365'],
            'packages.*.revision_limit' => ['nullable', 'required_with:packages.*.price', 'integer', 'min:0', 'max:50'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $basic = collect($this->input('packages', []))->firstWhere('tier_name', 'Basic');

                if (! $basic || blank($basic['price'] ?? null)) {
                    $validator->errors()->add('packages', 'Paket Basic wajib diisi (harga, lama pengerjaan, dan kuota revisi).');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'title.required'       => 'Judul jasa wajib diisi.',
            'title.min'            => 'Judul jasa minimal :min karakter.',
            'title.max'            => 'Judul jasa maksimal :max karakter.',
            'category_id.required' => 'Pilih kategori jasa.',
            'category_id.exists'   => 'Kategori tidak ditemukan.',
            'description.required' => 'Deskripsi jasa wajib diisi.',
            'description.min'      => 'Deskripsi minimal :min karakter agar pembeli paham apa yang didapat.',
            'status.in'            => 'Status jasa tidak valid.',
            'thumbnail.image'      => 'File sampul harus berupa gambar.',
            'thumbnail.mimes'      => 'Format gambar sampul harus JPG, PNG, atau WEBP.',
            'thumbnail.max'        => 'Ukuran gambar sampul maksimal 2 MB.',

            'packages.*.tier_name.in'               => 'Nama paket tidak valid.',
            'packages.*.tier_name.distinct'         => 'Nama paket tidak boleh dobel.',
            'packages.*.price.numeric'              => 'Harga paket harus berupa angka.',
            'packages.*.price.gt'                   => 'Harga paket harus lebih dari 0.',
            'packages.*.delivery_days.required_with' => 'Lama pengerjaan wajib diisi jika harga paket diisi.',
            'packages.*.delivery_days.min'          => 'Lama pengerjaan minimal 1 hari.',
            'packages.*.delivery_days.integer'      => 'Lama pengerjaan harus berupa angka bulat.',
            'packages.*.revision_limit.required_with' => 'Kuota revisi wajib diisi jika harga paket diisi.',
            'packages.*.revision_limit.min'         => 'Kuota revisi minimal 0.',
            'packages.*.revision_limit.integer'     => 'Kuota revisi harus berupa angka bulat.',
        ];
    }

    /**
     * Paket yang diisi (harga tidak kosong), siap disimpan.
     *
     * @return array<string, array{price: float, delivery_days: int, revision_limit: int}>
     */
    public function filledPackages(): array
    {
        $result = [];

        foreach ($this->validated('packages') as $p) {
            if (blank($p['price'] ?? null)) {
                continue;
            }

            $result[$p['tier_name']] = [
                'price'          => (float) $p['price'],
                'delivery_days'  => (int) $p['delivery_days'],
                'revision_limit' => (int) $p['revision_limit'],
            ];
        }

        return $result;
    }
}
