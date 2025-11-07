<?php

namespace App\Http\Controllers\API;

use App\Events\QrCodeGenerated;
use App\Http\Controllers\Controller;
use App\Http\Requests\QrCode\GenerateQrCodeRequest;
use App\Http\Resources\QrCodeResource;
use App\Models\JadwalPembelajaran;
use App\Models\QrCode;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class QrCodeController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = QrCode::query()->with(['kelas', 'guru', 'jadwalPembelajaran']);
        $perPage = $request->integer('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->integer('kelas_id'));
        }

        if ($request->filled('jadwal_id')) {
            $query->where('jadwal_id', $request->integer('jadwal_id'));
        }

        if ($request->boolean('only_active')) {
            $query->where('is_active', true)->where('expires_at', '>=', now());
        }

        $codes = $query->latest('created_at')->paginate($perPage);

        return QrCodeResource::collection($codes)->additional([
            'status' => 'success',
            'message' => 'Daftar QR code berhasil diambil.',
        ]);
    }

    public function store(GenerateQrCodeRequest $request)
    {
        $data = $request->validated();
        $jadwal = JadwalPembelajaran::with(['kelas', 'guru'])->findOrFail($data['jadwal_id']);

        if ((int) $jadwal->kelas_id !== (int) $data['kelas_id']) {
            throw ValidationException::withMessages([
                'kelas_id' => ['Kelas tidak sesuai dengan jadwal yang dipilih.'],
            ]);
        }

        if ((int) $jadwal->guru_id !== (int) $data['guru_id']) {
            throw ValidationException::withMessages([
                'guru_id' => ['Guru tidak sesuai dengan jadwal yang dipilih.'],
            ]);
        }

        $expiresInMinutes = $data['expires_in_minutes'] ?? config('attendance.qr.default_expiration_minutes', 5);
        $expiresAt = now()->addMinutes($expiresInMinutes);
        $meetingTime = $data['waktu_pertemuan']
            ? Carbon::parse($data['waktu_pertemuan'])
            : now()->startOfMinute();

        // Nonaktifkan QR code aktif sebelumnya untuk jadwal/pertemuan ini
        QrCode::where('jadwal_id', $jadwal->id)
            ->where('kelas_id', $jadwal->kelas_id)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $qrCode = QrCode::create([
            'kelas_id' => $jadwal->kelas_id,
            'guru_id' => $jadwal->guru_id,
            'jadwal_id' => $jadwal->id,
            'token' => $this->generateUniqueToken(),
            'expires_at' => $expiresAt,
            'waktu_pertemuan' => $meetingTime,
            'is_active' => true,
        ])->load(['kelas', 'guru', 'jadwalPembelajaran']);

        $resource = new QrCodeResource($qrCode);
        event(new QrCodeGenerated($resource->resolve()));

        $response = $resource->additional([
            'status' => 'success',
            'message' => 'QR code absensi berhasil dibuat.',
        ])->response();

        return $response->setStatusCode(201);
    }

    public function show(QrCode $qrCode): QrCodeResource
    {
        $qrCode->load(['kelas', 'guru', 'jadwalPembelajaran']);

        return (new QrCodeResource($qrCode))->additional([
            'status' => 'success',
            'message' => 'Detail QR code berhasil diambil.',
        ]);
    }

    public function active(Request $request): AnonymousResourceCollection
    {
        $query = QrCode::query()
            ->with(['kelas', 'guru', 'jadwalPembelajaran'])
            ->where('is_active', true)
            ->where('expires_at', '>=', now());

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->integer('kelas_id'));
        }

        if ($request->filled('jadwal_id')) {
            $query->where('jadwal_id', $request->integer('jadwal_id'));
        }

        $codes = $query->orderBy('created_at', 'desc')->get();

        return QrCodeResource::collection($codes)->additional([
            'status' => 'success',
            'message' => 'Daftar QR aktif berhasil diambil.',
        ]);
    }

    public function destroy(QrCode $qrCode): JsonResponse
    {
        $qrCode->update([
            'is_active' => false,
            'expires_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'QR code berhasil dinonaktifkan.',
        ]);
    }

    protected function generateUniqueToken(): string
    {
        do {
            $token = Str::random(64);
        } while (QrCode::where('token', $token)->exists());

        return $token;
    }
}
