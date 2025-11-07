<?php

namespace App\Http\Controllers\API;

use App\Events\MataPelajaranChanged;
use App\Http\Controllers\Controller;
use App\Http\Requests\MataPelajaran\StoreMataPelajaranRequest;
use App\Http\Requests\MataPelajaran\UpdateMataPelajaranRequest;
use App\Http\Resources\MataPelajaranResource;
use App\Models\MataPelajaran;
use App\Support\SlugService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class MataPelajaranController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = (int) $request->integer('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;

        $query = MataPelajaran::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            if ($search !== '') {
                $query->where(function ($builder) use ($search) {
                    $builder
                        ->where('nama_mapel', 'like', "%{$search}%")
                        ->orWhere('kode_mapel', 'like', "%{$search}%");
                });
            }
        }

        $lessons = $query
            ->orderBy('nama_mapel')
            ->paginate($perPage)
            ->appends($request->query());

        return MataPelajaranResource::collection($lessons)->additional([
            'status' => 'success',
            'message' => 'Daftar mata pelajaran berhasil diambil.',
        ]);
    }

    public function store(StoreMataPelajaranRequest $request): MataPelajaranResource
    {
        $data = $request->validated();
        $data['slug'] = SlugService::generate($data['nama_mapel'], 'mata_pelajaran');

        $lesson = MataPelajaran::create($data);

        $resource = new MataPelajaranResource($lesson);
        event(new MataPelajaranChanged('created', $resource->resolve()));

        return $resource->additional([
            'status' => 'success',
            'message' => 'Mata pelajaran berhasil ditambahkan.',
        ]);
    }

    public function show(MataPelajaran $mataPelajaran): MataPelajaranResource
    {
        return (new MataPelajaranResource($mataPelajaran))->additional([
            'status' => 'success',
            'message' => 'Detail mata pelajaran berhasil diambil.',
        ]);
    }

    public function update(UpdateMataPelajaranRequest $request, MataPelajaran $mataPelajaran): MataPelajaranResource
    {
        $data = $request->validated();

        if (isset($data['nama_mapel']) && $data['nama_mapel'] !== $mataPelajaran->nama_mapel) {
            $data['slug'] = SlugService::generate($data['nama_mapel'], 'mata_pelajaran', 'slug', $mataPelajaran->id);
        }

        $mataPelajaran->fill($data);
        $mataPelajaran->save();

        $resource = new MataPelajaranResource($mataPelajaran);
        event(new MataPelajaranChanged('updated', $resource->resolve()));

        return $resource->additional([
            'status' => 'success',
            'message' => 'Data mata pelajaran berhasil diperbarui.',
        ]);
    }

    public function destroy(MataPelajaran $mataPelajaran): JsonResponse
    {
        $mataPelajaran->delete();
        $payload = (new MataPelajaranResource($mataPelajaran))->resolve();

        event(new MataPelajaranChanged('deleted', $payload));

        return response()->json([
            'status' => 'success',
            'message' => 'Mata pelajaran berhasil dihapus.',
            'data' => $payload,
        ]);
    }
}
