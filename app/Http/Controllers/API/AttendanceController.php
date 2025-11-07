<?php

namespace App\Http\Controllers\API;

use App\Events\AttendanceRecorded;
use App\Http\Controllers\Controller;
use App\Http\Requests\Attendance\ScanAttendanceRequest;
use App\Http\Resources\KehadiranSiswaResource;
use App\Models\KehadiranSiswa;
use App\Models\JadwalPembelajaran;
use App\Models\QrCode;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function scan(ScanAttendanceRequest $request): JsonResponse
    {
        $data = $request->validated();

        $qrCode = QrCode::with('kelas')->where('token', $data['token'])->firstOrFail();

        if (!$qrCode->is_active || $qrCode->expires_at?->isPast()) {
            $qrCode->update(['is_active' => false]);

            throw ValidationException::withMessages([
                'token' => ['QR code sudah tidak aktif atau kedaluwarsa.'],
            ]);
        }

        if (!$qrCode->jadwal_id) {
            throw ValidationException::withMessages([
                'token' => ['QR code tidak terkait dengan jadwal yang valid.'],
            ]);
        }

        $siswa = $this->resolveSiswa($data);

        if ((int) $siswa->kelas_id !== (int) $qrCode->kelas_id) {
            throw ValidationException::withMessages([
                'siswa_id' => ['Siswa tidak terdaftar pada kelas QR code ini.'],
            ]);
        }

        if ($siswa->status !== 'Aktif') {
            throw ValidationException::withMessages([
                'siswa_id' => ['Status siswa tidak aktif.'],
            ]);
        }

        $meetingTime = $qrCode->waktu_pertemuan
            ? $qrCode->waktu_pertemuan->copy()
            : now()->startOfMinute();

        $existing = KehadiranSiswa::where('siswa_id', $siswa->id)
            ->where('jadwal_id', $qrCode->jadwal_id)
            ->where('waktu_pertemuan', $meetingTime)
            ->first();

        if ($existing) {
            return (new KehadiranSiswaResource($existing->load('siswa')))
                ->additional([
                    'status' => 'info',
                    'message' => 'Kehadiran sudah tercatat untuk pertemuan ini.',
                ])->response();
        }

        $status = $data['status_kehadiran'] ?? $this->determineStatus($meetingTime);

        $attendance = KehadiranSiswa::create([
            'siswa_id' => $siswa->id,
            'jadwal_id' => $qrCode->jadwal_id,
            'waktu_pertemuan' => $meetingTime,
            'status_kehadiran' => $status,
            'keterangan' => $this->composeNotes($data),
            'metode_catat' => 'QR Scan',
        ])->load(['siswa', 'jadwalPembelajaran']);

        $resource = new KehadiranSiswaResource($attendance);
        event(new AttendanceRecorded($resource->resolve()));

        $response = $resource->additional([
            'status' => 'success',
            'message' => 'Kehadiran siswa berhasil tercatat.',
        ])->response();

        return $response->setStatusCode(201);
    }

    public function records(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->integer('per_page', 15);
        $perPage = $perPage > 0 ? min($perPage, 100) : 15;

        $query = KehadiranSiswa::query()->with([
            'siswa',
            'jadwalPembelajaran.kelas',
            'jadwalPembelajaran.mataPelajaran',
        ]);

        if ($request->filled('kelas_id')) {
            $query->whereHas('jadwalPembelajaran', function ($builder) use ($request) {
                $builder->where('kelas_id', $request->integer('kelas_id'));
            });
        }

        if ($request->filled('status')) {
            $statuses = (array) $request->input('status');
            $query->whereIn('status_kehadiran', $statuses);
        }

        if ($request->filled('jadwal_id')) {
            $query->where('jadwal_id', $request->integer('jadwal_id'));
        }

        if ($request->filled('date')) {
            $date = Carbon::parse($request->input('date'))->startOfDay();
            $query->whereBetween('waktu_pertemuan', [$date, (clone $date)->endOfDay()]);
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            if ($search !== '') {
                $query->whereHas('siswa', function ($builder) use ($search) {
                    $builder
                        ->where('nama_siswa', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%");
                });
            }
        }

        $records = $query
            ->latest('waktu_pertemuan')
            ->paginate($perPage)
            ->appends($request->query());

        return KehadiranSiswaResource::collection($records)->additional([
            'status' => 'success',
            'message' => 'Riwayat kehadiran berhasil diambil.',
        ]);
    }

    public function summary(Request $request): JsonResponse
    {
        $request->validate([
            'kelas_id' => ['nullable', 'exists:kelas,id'],
            'jadwal_id' => ['nullable', 'exists:jadwal_pembelajaran,id'],
            'date' => ['nullable', 'date'],
        ]);

        if (!$request->filled('kelas_id') && !$request->filled('jadwal_id')) {
            throw ValidationException::withMessages([
                'kelas_id' => ['Kelas atau jadwal wajib dipilih.'],
            ]);
        }

        $date = $request->filled('date')
            ? Carbon::parse($request->input('date'))->startOfDay()
            : now()->startOfDay();

        $endDate = (clone $date)->endOfDay();

        $query = KehadiranSiswa::query()->whereBetween('waktu_pertemuan', [$date, $endDate]);

        $classId = $request->integer('kelas_id');

        if ($request->filled('jadwal_id')) {
            $jadwal = JadwalPembelajaran::findOrFail($request->integer('jadwal_id'));
            $query->where('jadwal_id', $jadwal->id);

            if (!$classId) {
                $classId = $jadwal->kelas_id;
            }
        }

        if ($classId) {
            $query->whereHas('siswa', function ($builder) use ($classId) {
                $builder->where('kelas_id', $classId);
            });
        }

        $baseQuery = clone $query;

        $statusOrder = ['Hadir', 'Terlambat', 'Izin', 'Sakit', 'Alpha'];
        $perStatus = [];

        foreach ($statusOrder as $status) {
            $perStatus[$status] = (clone $baseQuery)->where('status_kehadiran', $status)->count();
        }

        $totalAttendance = array_sum($perStatus);

        $studentsCount = $classId
            ? Siswa::where('kelas_id', $classId)->count()
            : $totalAttendance;

        $attendanceRate = $studentsCount > 0
            ? round(($perStatus['Hadir'] / $studentsCount) * 100, 2)
            : 0;

        $latestAttendance = (clone $baseQuery)
            ->latest('waktu_pertemuan')
            ->limit(5)
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Ringkasan kehadiran berhasil diambil.',
            'data' => [
                'date' => $date->toDateString(),
                'total_students' => $studentsCount,
                'total_attendance' => $totalAttendance,
                'attendance_rate' => $attendanceRate,
                'per_status' => $perStatus,
                'latest_attendance' => KehadiranSiswaResource::collection($latestAttendance),
            ],
        ]);
    }

    protected function resolveSiswa(array $data): Siswa
    {
        $query = Siswa::query();

        if (!empty($data['siswa_id'])) {
            return $query->where('id', $data['siswa_id'])->firstOrFail();
        }

        return $query->where('nisn', $data['nisn'])->firstOrFail();
    }

    protected function determineStatus(Carbon $meetingTime): string
    {
        $threshold = (int) config('attendance.qr.late_threshold_minutes', 10);
        $lateAfter = $meetingTime->copy()->addMinutes($threshold);

        return now()->greaterThan($lateAfter) ? 'Terlambat' : 'Hadir';
    }

    protected function composeNotes(array $data): ?string
    {
        $notes = [];

        if (!empty($data['catatan'])) {
            $notes[] = $data['catatan'];
        }

        if (!empty($data['device_info'])) {
            $notes[] = 'Device: ' . $data['device_info'];
        }

        return empty($notes) ? null : implode(' | ', $notes);
    }
}
