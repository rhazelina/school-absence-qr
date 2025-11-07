<?php

namespace App\Http\Requests\Siswa;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiswaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nisn' => 'required|digits:10|unique:siswa,nisn',
            'nama_siswa' => 'required|string|max:100',
            'kelas_id' => 'required|exists:kelas,id',
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
