<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $siswaId = $this->route('siswa')?->id;

        return [
            'nisn' => [
                'sometimes',
                'required',
                'digits:10',
                Rule::unique('siswa', 'nisn')->ignore($siswaId),
            ],
            'nama_siswa' => 'sometimes|required|string|max:100',
            'kelas_id' => 'sometimes|required|exists:kelas,id',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'alamat' => 'nullable|string',
            'nama_wali' => 'nullable|string|max:100',
            'kontak_wali' => 'nullable|string|max:20',
            'status' => 'nullable|in:Aktif,Lulus,Pindah,Drop Out',
        ];
    }

    public function attributes(): array
    {
        return [
            'nama_siswa' => 'nama siswa',
            'kelas_id' => 'kelas',
            'nama_wali' => 'nama wali',
            'kontak_wali' => 'kontak wali',
        ];
    }
}
