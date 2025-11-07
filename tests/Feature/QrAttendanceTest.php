<?php

namespace Tests\Feature;

use App\Models\Guru;
use App\Models\KehadiranSiswa;
use App\Models\JadwalPembelajaran;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class QrAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('broadcasting.default', 'null');
    }

    public function test_student_can_scan_active_qr_code(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $jurusan = Jurusan::create([
            'kode_jurusan' => 'TKJ',
            'nama_jurusan' => 'Teknik Komputer Jaringan',
            'slug' => 'tkj',
        ]);

        $guru = Guru::create([
            'kode_guru' => 'GR001',
            'nama_guru' => 'Pak Guru',
            'slug' => 'pak-guru',
            'password_hash' => bcrypt('secret'),
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => 'XII TKJ 1',
            'slug' => 'xii-tkj-1',
            'jurusan_id' => $jurusan->id,
            'tahun_ajaran' => '2024/2025',
            'kapasitas' => 36,
            'wali_kelas_id' => $guru->id,
        ]);

        $mapel = MataPelajaran::create([
            'kode_mapel' => 'MP001',
            'nama_mapel' => 'Jaringan',
            'slug' => 'jaringan',
        ]);

        $jadwal = JadwalPembelajaran::create([
            'mata_pelajaran_id' => $mapel->id,
            'guru_id' => $guru->id,
            'kelas_id' => $kelas->id,
            'hari' => 'Senin',
            'waktu_mulai' => '07:00:00',
            'waktu_selesai' => '08:00:00',
            'ruangan' => 'Lab 1',
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
        ]);

        $siswa = Siswa::create([
            'nisn' => '1234567890',
            'nama_siswa' => 'Budi',
            'slug' => 'budi-' . Str::random(5),
            'kelas_id' => $kelas->id,
            'status' => 'Aktif',
        ]);

        $qrPayload = [
            'kelas_id' => $kelas->id,
            'guru_id' => $guru->id,
            'jadwal_id' => $jadwal->id,
            'waktu_pertemuan' => now()->toDateTimeString(),
            'expires_in_minutes' => 10,
        ];

        $qrResponse = $this->postJson('/api/qr-codes', $qrPayload);
        $qrResponse->assertCreated();

        $token = $qrResponse->json('data.token');
        $this->assertNotEmpty($token);

        $scanResponse = $this->postJson('/api/attendance/scan', [
            'token' => $token,
            'siswa_id' => $siswa->id,
        ]);

        $scanResponse->assertStatus(201)
            ->assertJsonPath('data.siswa_id', $siswa->id)
            ->assertJsonPath('message', 'Kehadiran siswa berhasil tercatat.');

        $this->assertDatabaseHas('kehadiran_siswa', [
            'siswa_id' => $siswa->id,
            'jadwal_id' => $jadwal->id,
            'status_kehadiran' => 'Hadir',
        ]);
    }

    public function test_attendance_summary_returns_status_breakdown(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $jurusan = Jurusan::create([
            'kode_jurusan' => 'RPL',
            'nama_jurusan' => 'Rekayasa Perangkat Lunak',
            'slug' => 'rpl',
        ]);

        $guru = Guru::create([
            'kode_guru' => 'GR010',
            'nama_guru' => 'Bu Guru',
            'slug' => 'bu-guru',
            'password_hash' => bcrypt('secret'),
        ]);

        $kelas = Kelas::create([
            'nama_kelas' => 'XI RPL 2',
            'slug' => 'xi-rpl-2',
            'jurusan_id' => $jurusan->id,
            'tahun_ajaran' => '2024/2025',
            'kapasitas' => 32,
            'wali_kelas_id' => $guru->id,
        ]);

        $mapel = MataPelajaran::create([
            'kode_mapel' => 'MP045',
            'nama_mapel' => 'Pemrograman',
            'slug' => 'pemrograman',
        ]);

        $jadwal = JadwalPembelajaran::create([
            'mata_pelajaran_id' => $mapel->id,
            'guru_id' => $guru->id,
            'kelas_id' => $kelas->id,
            'hari' => 'Selasa',
            'waktu_mulai' => '09:00:00',
            'waktu_selesai' => '10:30:00',
            'ruangan' => 'Lab 2',
            'semester' => 'Ganjil',
            'tahun_ajaran' => '2024/2025',
        ]);

        $students = [];

        for ($i = 0; $i < 3; $i++) {
            $students[$i] = Siswa::create([
                'nisn' => '99887766' . $i,
                'nama_siswa' => 'Siswa ' . ($i + 1),
                'slug' => 'siswa-' . $i . '-' . Str::random(4),
                'kelas_id' => $kelas->id,
                'status' => 'Aktif',
            ]);
        }

        foreach ($students as $index => $student) {
            KehadiranSiswa::create([
                'siswa_id' => $student->id,
                'jadwal_id' => $jadwal->id,
                'waktu_pertemuan' => now()->startOfDay()->addMinutes($index),
                'status_kehadiran' => $index === 0 ? 'Hadir' : 'Terlambat',
                'metode_catat' => 'QR Scan',
            ]);
        }

        $response = $this->getJson('/api/attendance/summary?kelas_id=' . $kelas->id);

        $response->assertOk()
            ->assertJsonPath('data.total_students', 3)
            ->assertJsonPath('data.per_status.Hadir', 1)
            ->assertJsonPath('data.per_status.Terlambat', 2);
    }
}
