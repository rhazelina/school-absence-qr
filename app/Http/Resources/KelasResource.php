<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class KelasResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nama_kelas' => $this->nama_kelas,
            'slug' => $this->slug,
            'tahun_ajaran' => $this->tahun_ajaran,
            'kapasitas' => $this->kapasitas,
            'students_count' => $this->when(isset($this->students_count), $this->students_count),
            'jurusan' => $this->whenLoaded('jurusan', function () {
                return [
                    'id' => $this->jurusan->id,
                    'nama_jurusan' => $this->jurusan->nama_jurusan,
                    'kode_jurusan' => $this->jurusan->kode_jurusan,
                ];
            }),
            'wali_kelas' => $this->whenLoaded('waliKelas', function () {
                return [
                    'id' => $this->waliKelas->id,
                    'nama_guru' => $this->waliKelas->nama_guru,
                    'kode_guru' => $this->waliKelas->kode_guru,
                ];
            }),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
