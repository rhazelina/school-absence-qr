<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KehadiranSiswaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'siswa_id' => $this->siswa_id,
            'siswa' => $this->whenLoaded('siswa', function () {
                return [
                    'id' => $this->siswa->id,
                    'nama_siswa' => $this->siswa->nama_siswa,
                    'nisn' => $this->siswa->nisn,
                    'kelas_id' => $this->siswa->kelas_id,
                ];
            }),
            'jadwal_id' => $this->jadwal_id,
            'jadwal' => $this->whenLoaded('jadwalPembelajaran', function () {
                return [
                    'id' => $this->jadwalPembelajaran->id,
                    'kelas_id' => $this->jadwalPembelajaran->kelas_id,
                    'guru_id' => $this->jadwalPembelajaran->guru_id,
                    'mata_pelajaran_id' => $this->jadwalPembelajaran->mata_pelajaran_id,
                ];
            }),
            'waktu_pertemuan' => optional($this->waktu_pertemuan)?->toIso8601String(),
            'status_kehadiran' => $this->status_kehadiran,
            'keterangan' => $this->keterangan,
            'metode_catat' => $this->metode_catat,
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
