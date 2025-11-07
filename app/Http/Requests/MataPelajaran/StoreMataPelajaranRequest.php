<?php

namespace App\Http\Requests\MataPelajaran;

use Illuminate\Foundation\Http\FormRequest;

class StoreMataPelajaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode_mapel' => 'required|string|max:10|unique:mata_pelajaran,kode_mapel',
            'nama_mapel' => 'required|string|max:100',
        ];
    }
}
