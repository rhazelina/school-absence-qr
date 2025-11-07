<?php

namespace App\Http\Requests\Attendance;

use Illuminate\Foundation\Http\FormRequest;

class ScanAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string', 'exists:qr_codes,token'],
            'siswa_id' => ['required_without:nisn', 'nullable', 'exists:siswa,id'],
            'nisn' => ['required_without:siswa_id', 'nullable', 'digits:10', 'exists:siswa,nisn'],
            'status_kehadiran' => ['nullable', 'in:Hadir,Terlambat,Izin,Sakit,Alpha'],
            'device_info' => ['nullable', 'string', 'max:255'],
            'catatan' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'siswa_id.required_without' => 'Siswa wajib diisi ketika NISN tidak diberikan.',
            'nisn.required_without' => 'NISN wajib diisi ketika ID siswa tidak diberikan.',
        ];
    }
}
