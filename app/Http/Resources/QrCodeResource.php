<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QrCodeResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'token' => $this->token,
            'kelas_id' => $this->kelas_id,
            'kelas' => $this->whenLoaded('kelas', function () {
                return [
                    'id' => $this->kelas->id,
                    'nama_kelas' => $this->kelas->nama_kelas,
                    'slug' => $this->kelas->slug,
                ];
            }),
            'guru_id' => $this->guru_id,
            'guru' => $this->whenLoaded('guru', function () {
                return [
                    'id' => $this->guru->id,
                    'nama_guru' => $this->guru->nama_guru,
                    'kode_guru' => $this->guru->kode_guru,
                ];
            }),
            'jadwal_id' => $this->jadwal_id,
            'jadwal' => $this->whenLoaded('jadwalPembelajaran', function () {
                return [
                    'id' => $this->jadwalPembelajaran->id,
                    'mata_pelajaran_id' => $this->jadwalPembelajaran->mata_pelajaran_id,
                    'kelas_id' => $this->jadwalPembelajaran->kelas_id,
                    'hari' => $this->jadwalPembelajaran->hari,
                    'waktu_mulai' => optional($this->jadwalPembelajaran->waktu_mulai)?->format('H:i'),
                    'waktu_selesai' => optional($this->jadwalPembelajaran->waktu_selesai)?->format('H:i'),
                ];
            }),
            'waktu_pertemuan' => optional($this->waktu_pertemuan)?->toIso8601String(),
            'expires_at' => optional($this->expires_at)?->toIso8601String(),
            'is_active' => (bool) $this->is_active,
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
