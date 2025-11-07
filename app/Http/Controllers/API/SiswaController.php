<?php

namespace App\Http\Controllers\API;

use App\Events\SiswaChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\Siswa\StoreSiswaRequest;
use App\Http\Requests\Siswa\UpdateSiswaRequest;
use App\Http\Resources\SiswaResource;
use App\Models\Siswa;
use App\Support\SlugService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SiswaController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->integer('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;

        $query = Siswa::query()->with('kelas');

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            if ($search !== '') {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('nama_siswa', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            }
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->integer('kelas_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $students = $query
            ->orderBy('nama_siswa')
            ->paginate($perPage)
            ->appends($request->query());

        return SiswaResource::collection($students)->additional([
            'status' => 'success',
            'message' => 'Daftar siswa berhasil diambil.',
        ]);
    }

    public function store(StoreSiswaRequest $request): SiswaResource
    {
        $data = $request->validated();
        $data['slug'] = SlugService::generate($data['nama_siswa'], 'siswa');
        $data['status'] = $data['status'] ?? 'Aktif';

        $siswa = Siswa::create($data)->load('kelas');

        $resource = new SiswaResource($siswa);
        event(new SiswaChanged('created', $resource->resolve()));

        return $resource->additional([
            'status' => 'success',
            'message' => 'Siswa baru berhasil ditambahkan.',
        ]);
    }

    public function show(Siswa $siswa): SiswaResource
    {
        $siswa->load('kelas');

        return (new SiswaResource($siswa))->additional([
            'status' => 'success',
            'message' => 'Detail siswa berhasil diambil.',
        ]);
    }

    public function update(UpdateSiswaRequest $request, Siswa $siswa): SiswaResource
    {
        $data = $request->validated();

        if (isset($data['nama_siswa']) && $data['nama_siswa'] !== $siswa->nama_siswa) {
            $data['slug'] = SlugService::generate($data['nama_siswa'], 'siswa', 'slug', $siswa->id);
        }

        $siswa->fill($data);
        $siswa->save();
        $siswa->load('kelas');

        $resource = new SiswaResource($siswa);
        event(new SiswaChanged('updated', $resource->resolve()));

        return $resource->additional([
            'status' => 'success',
            'message' => 'Data siswa berhasil diperbarui.',
        ]);
    }

    public function destroy(Siswa $siswa): JsonResponse
    {
        $siswa->load('kelas');
        $siswa->delete();

        $payload = (new SiswaResource($siswa))->resolve();
        event(new SiswaChanged('deleted', $payload));

        return response()->json([
            'status' => 'success',
            'message' => 'Siswa berhasil dihapus.',
            'data' => $payload,
        ]);
    }
}
