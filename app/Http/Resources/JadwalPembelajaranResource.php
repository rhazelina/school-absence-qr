<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class JadwalPembelajaranResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'hari' => $this->hari,
            'waktu_mulai' => (string) $this->waktu_mulai,
            'waktu_selesai' => (string) $this->waktu_selesai,
            'ruangan' => $this->ruangan,
            'semester' => $this->semester,
            'tahun_ajaran' => $this->tahun_ajaran,
            'kelas' => $this->whenLoaded('kelas', function () {
                return [
                    'id' => $this->kelas->id,
                    'nama_kelas' => $this->kelas->nama_kelas,
                    'slug' => $this->kelas->slug,
                ];
            }),
            'guru' => $this->whenLoaded('guru', function () {
                return [
                    'id' => $this->guru->id,
                    'nama_guru' => $this->guru->nama_guru,
                    'kode_guru' => $this->guru->kode_guru,
                ];
            }),
            'mata_pelajaran' => $this->whenLoaded('mataPelajaran', function () {
                return [
                    'id' => $this->mataPelajaran->id,
                    'nama_mapel' => $this->mataPelajaran->nama_mapel,
                    'kode_mapel' => $this->mataPelajaran->kode_mapel,
                ];
            }),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
