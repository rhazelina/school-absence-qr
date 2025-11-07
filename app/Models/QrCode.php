<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrCode extends Model
{
    protected $table = 'qr_codes';
    public $timestamps = false;

    protected $fillable = [
        'kelas_id',
        'guru_id',
        'jadwal_id',
        'token',
        'expires_at',
        'waktu_pertemuan',
        'is_active',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
        'waktu_pertemuan' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function jadwalPembelajaran(): BelongsTo
    {
        return $this->belongsTo(JadwalPembelajaran::class, 'jadwal_id');
    }
}
