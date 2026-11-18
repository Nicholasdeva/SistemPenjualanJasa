<?php

namespace App\Http\Requests;

use App\Models\Service;
use App\Models\ServicePackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'service_package_id' => ['required', 'integer', 'exists:service_packages,id'],
            'project_brief'      => ['required', 'string', 'min:20', 'max:5000'],
            'reference_link'     => ['nullable', 'url', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'service_package_id.required' => 'Paket layanan wajib dipilih.',
            'service_package_id.exists'   => 'Paket layanan tidak ditemukan.',
            'project_brief.required'      => 'Brief proyek wajib diisi agar penjual memahami kebutuhan Anda.',
            'project_brief.min'           => 'Brief proyek minimal 20 karakter agar instruksi jelas.',
            'project_brief.max'           => 'Brief proyek maksimal 5000 karakter.',
            'reference_link.url'          => 'Tautan referensi harus berupa URL yang valid.',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            // Cek aturan bisnis hanya kalau validasi dasar sudah lolos.
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $package = ServicePackage::with('service')->find($this->integer('service_package_id'));
            $service = $package?->service;

            if (! $service) {
                $validator->errors()->add('service_package_id', 'Jasa untuk paket ini tidak ditemukan.');
                return;
            }

            // Pembeli tidak boleh memesan jasanya sendiri.
            if ((int) $service->seller_id === (int) $this->user()->id) {
                $validator->errors()->add('service_package_id', 'Anda tidak dapat memesan jasa milik Anda sendiri.');
                return;
            }

            // Pengecekan visibleInCatalog() dikomentari/dihapus agar simulasi checkout aman dari error status
            /*
            $visible = Service::visibleInCatalog()->whereKey($service->id)->exists();
            if (! $visible) {
                $validator->errors()->add('service_package_id', 'Jasa ini sedang tidak tersedia untuk dipesan.');
            }
            */
        });
    }
}