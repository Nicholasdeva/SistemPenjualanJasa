<?php

namespace App\Http\Requests\Seller;

use App\Models\Service;
use Illuminate\Validation\Validator;

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

    public function after(): array
    {
        return array_merge(parent::after(), [
            function (Validator $validator) {
                $service = $this->route('service');

                if (
                    $service instanceof Service
                    && $service->status === Service::STATUS_DRAFT
                    && $service->moderation_locked
                    && $this->input('status') === Service::STATUS_PUBLISHED
                ) {
                    $validator->errors()->add('status', 'Jasa yang diturunkan admin hanya dapat ditayangkan kembali oleh admin.');
                }
            },
        ]);
    }
}
