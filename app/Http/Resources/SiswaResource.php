<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SiswaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'nisn' => $this->nisn,
            'nama_siswa' => $this->nama_siswa,
            'slug' => $this->slug,
            'kelas_id' => $this->kelas_id,
            'kelas' => $this->whenLoaded('kelas', function () {
                return [
                    'id' => $this->kelas->id,
                    'nama_kelas' => $this->kelas->nama_kelas,
                    'slug' => $this->kelas->slug,
                ];
            }),
            'tempat_lahir' => $this->tempat_lahir,
            'tanggal_lahir' => optional($this->tanggal_lahir)?->toDateString(),
            'alamat' => $this->alamat,
            'nama_wali' => $this->nama_wali,
            'kontak_wali' => $this->kontak_wali,
            'status' => $this->status,
            'last_login_at' => optional($this->last_login_at)?->toIso8601String(),
            'created_at' => optional($this->created_at)?->toIso8601String(),
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
            'deleted_at' => optional($this->deleted_at)?->toIso8601String(),
        ];
    }
}
