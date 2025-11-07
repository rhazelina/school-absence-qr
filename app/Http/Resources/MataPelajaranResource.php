<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MataPelajaranResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'kode_mapel' => $this->kode_mapel,
            'nama_mapel' => $this->nama_mapel,
            'slug' => $this->slug,
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
