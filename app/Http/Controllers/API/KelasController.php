<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\KelasResource;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class KelasController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->integer('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;

        $query = Kelas::query()
            ->with(['jurusan', 'waliKelas'])
            ->withCount(['siswa as students_count']);

        if ($request->filled('jurusan_id')) {
            $query->where('jurusan_id', $request->integer('jurusan_id'));
        }

        if ($request->filled('tahun_ajaran')) {
            $query->where('tahun_ajaran', $request->input('tahun_ajaran'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            if ($search !== '') {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('nama_kelas', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            }
        }

        $classes = $query
            ->orderBy('nama_kelas')
            ->paginate($perPage)
            ->appends($request->query());

        return KelasResource::collection($classes)->additional([
            'status' => 'success',
            'message' => 'Daftar kelas berhasil diambil.',
        ]);
    }

    public function show(Kelas $kelas): KelasResource
    {
        $kelas->load(['jurusan', 'waliKelas'])->loadCount(['siswa as students_count']);

        return (new KelasResource($kelas))->additional([
            'status' => 'success',
            'message' => 'Detail kelas berhasil diambil.',
        ]);
    }
}
