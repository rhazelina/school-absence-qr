<?php

namespace App\Http\Requests\QrCode;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerateQrCodeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kelas_id' => ['required', 'exists:kelas,id'],
            'guru_id' => ['required', 'exists:guru,id'],
            'jadwal_id' => [
                'required',
                Rule::exists('jadwal_pembelajaran', 'id')->where(function ($query) {
                    if ($this->filled('kelas_id')) {
                        $query->where('kelas_id', $this->input('kelas_id'));
                    }

                    if ($this->filled('guru_id')) {
                        $query->where('guru_id', $this->input('guru_id'));
                    }
                }),
            ],
            'waktu_pertemuan' => ['nullable', 'date'],
            'expires_in_minutes' => ['nullable', 'integer', 'min:1', 'max:180'],
        ];
    }
}
