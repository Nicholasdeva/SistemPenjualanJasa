<?php

namespace App\Http\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest
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
            'message'    => ['required_without:attachment', 'nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf,zip,docx', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'message.required_without' => 'Tulis pesan atau lampirkan file terlebih dahulu.',
            'message.max'              => 'Pesan maksimal 2000 karakter.',
            'attachment.file'          => 'Lampiran harus berupa berkas yang valid.',
            'attachment.mimes'         => 'Format lampiran harus JPG, PNG, WEBP, PDF, ZIP, atau DOCX.',
            'attachment.max'           => 'Ukuran lampiran maksimal 10 MB.',
        ];
    }
}
