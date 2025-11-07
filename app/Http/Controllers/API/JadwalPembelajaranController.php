<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\JadwalPembelajaranResource;
use App\Models\JadwalPembelajaran;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class JadwalPembelajaranController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->integer('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;

        $query = JadwalPembelajaran::query()->with(['kelas', 'guru', 'mataPelajaran']);

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->integer('kelas_id'));
        }

        if ($request->filled('guru_id')) {
            $query->where('guru_id', $request->integer('guru_id'));
        }

        if ($request->filled('mata_pelajaran_id')) {
            $query->where('mata_pelajaran_id', $request->integer('mata_pelajaran_id'));
        }

        if ($request->filled('hari')) {
            $query->where('hari', $request->input('hari'));
        }

        if ($request->filled('semester')) {
            $query->where('semester', $request->input('semester'));
        }

        if ($request->filled('tahun_ajaran')) {
            $query->where('tahun_ajaran', $request->input('tahun_ajaran'));
        }

        $schedules = $query
            ->orderBy('hari')
            ->orderBy('waktu_mulai')
            ->paginate($perPage)
            ->appends($request->query());

        return JadwalPembelajaranResource::collection($schedules)->additional([
            'status' => 'success',
            'message' => 'Daftar jadwal berhasil diambil.',
        ]);
    }

    public function show(JadwalPembelajaran $jadwalPembelajaran): JadwalPembelajaranResource
    {
        $jadwalPembelajaran->load(['kelas', 'guru', 'mataPelajaran']);

        return (new JadwalPembelajaranResource($jadwalPembelajaran))->additional([
            'status' => 'success',
            'message' => 'Detail jadwal berhasil diambil.',
        ]);
    }
}
