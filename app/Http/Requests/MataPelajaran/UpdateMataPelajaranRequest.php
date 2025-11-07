<?php

namespace App\Http\Requests\MataPelajaran;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMataPelajaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $mapelId = $this->route('mataPelajaran')?->id;

        return [
            'kode_mapel' => [
                'sometimes',
                'required',
                'string',
                'max:10',
                Rule::unique('mata_pelajaran', 'kode_mapel')->ignore($mapelId),
            ],
            'nama_mapel' => 'sometimes|required|string|max:100',
        ];
    }
}
