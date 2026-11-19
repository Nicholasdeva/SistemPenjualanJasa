<?php

namespace App\Http\Requests\Workspace;

use Illuminate\Foundation\Http\FormRequest;

class RequestRevisionRequest extends FormRequest
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
            'revision_note' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'revision_note.required' => 'Catatan revisi wajib diisi.',
            'revision_note.min'      => 'Catatan revisi minimal 10 karakter agar penjual memahami bagian yang perlu diperbaiki.',
            'revision_note.max'      => 'Catatan revisi maksimal 1000 karakter.',
        ];
    }
}
