<?php

namespace App\Http\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;

class DeliverWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'delivery_file' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf,zip,docx', 'max:10240'],
            'delivery_note' => ['required', 'string', 'min:5', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'delivery_file.required' => 'File hasil kerja wajib diunggah.',
            'delivery_file.file'     => 'Berkas hasil kerja tidak valid.',
            'delivery_file.mimes'    => 'Format file hasil kerja harus JPG, PNG, WEBP, PDF, ZIP, atau DOCX.',
            'delivery_file.max'      => 'Ukuran file hasil kerja maksimal 10 MB.',
            'delivery_note.required' => 'Catatan pengiriman hasil kerja wajib diisi.',
            'delivery_note.min'      => 'Catatan pengiriman minimal 5 karakter.',
            'delivery_note.max'      => 'Catatan pengiriman maksimal 1000 karakter.',
        ];
    }
}
