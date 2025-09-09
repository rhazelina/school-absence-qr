<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Admin login endpoint
     */
    public function adminLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string|max:255',
            'password' => 'required|string|min:6',
        ], [
            'username.required' => 'Username harus diisi.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 6 karakter.'
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $user = User::where('username', $request->username)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Administrator');
            })
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'username' => ['Kredensial yang diberikan tidak valid.'],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'token' => $user->createToken('auth-token')->plainTextToken,
            ]
        ]);
    }

    /**
     * Staff/Management login endpoint (Waka, Staff, Kesiswaan)
     */
    public function staffLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_guru' => 'required|string|max:255',
            'kode_guru' => 'required|string|max:10',
            'password' => 'required|string|min:6',
        ], [
            'nama_guru.required' => 'Nama guru harus diisi.',
            'kode_guru.required' => 'Kode guru harus diisi.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 6 karakter.'
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $guru = Guru::where('nama_guru', $request->nama_guru)
            ->where('kode_guru', $request->kode_guru)
            ->whereIn('role', ['Waka', 'Staff Kurikulum', 'Kesiswaan'])
            ->first();

        if (!$guru || !Hash::check($request->password, $guru->password_hash)) {
            throw ValidationException::withMessages([
                'kode_guru' => ['Kredensial yang diberikan tidak valid.'],
            ]);
        }

        $user = User::where('username', $guru->kode_guru)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'guru' => $guru,
                'token' => $user->createToken('auth-token')->plainTextToken,
            ]
        ]);
    }

    /**
     * Guru/Wali Kelas login endpoint
     */
    public function guruLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_guru' => 'required|string|max:255',
            'kode_guru' => 'required|string|max:10',
            'password' => 'required|string|min:6',
        ], [
            'nama_guru.required' => 'Nama guru harus diisi.',
            'kode_guru.required' => 'Kode guru harus diisi.',
            'password.required' => 'Password harus diisi.',
            'password.min' => 'Password minimal 6 karakter.'
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $guru = Guru::where('nama_guru', $request->nama_guru)
            ->where('kode_guru', $request->kode_guru)
            ->whereIn('role', ['Guru', 'Wali Kelas'])
            ->first();

        if (!$guru || !Hash::check($request->password, $guru->password_hash)) {
            throw ValidationException::withMessages([
                'kode_guru' => ['Kredensial yang diberikan tidak valid.'],
            ]);
        }

        $user = User::where('username', $guru->kode_guru)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'guru' => $guru,
                'token' => $user->createToken('auth-token')->plainTextToken,
            ]
        ]);
    }

    /**
     * Siswa login endpoint
     */
    public function siswaLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'nisn' => 'required|string|size:10',
        ], [
            'nama_lengkap.required' => 'Nama lengkap harus diisi.',
            'nisn.required' => 'NISN harus diisi.',
            'nisn.size' => 'NISN harus 10 digit.',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $siswa = Siswa::where('nama_siswa', $request->nama_lengkap)
            ->where('nisn', $request->nisn)
            ->where('status', 'Aktif')
            ->first();

        if (!$siswa) {
            throw ValidationException::withMessages([
                'nisn' => ['Kredensial yang diberikan tidak valid.'],
            ]);
        }

        $user = User::where('username', $siswa->nisn)->first();

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'siswa' => $siswa,
                'token' => $user->createToken('auth-token')->plainTextToken,
            ]
        ]);
    }

    /**
     * Pengurus Kelas login endpoint
     */
    public function pengurusKelasLogin(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'nisn' => 'required|string|size:10',
        ], [
            'nama_lengkap.required' => 'Nama lengkap harus diisi.',
            'nisn.required' => 'NISN harus diisi.',
            'nisn.size' => 'NISN harus 10 digit.',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        $siswa = Siswa::where('nama_siswa', $request->nama_lengkap)
            ->where('nisn', $request->nisn)
            ->where('status', 'Aktif')
            ->first();

        if (!$siswa) {
            throw ValidationException::withMessages([
                'nisn' => ['Kredensial yang diberikan tidak valid.'],
            ]);
        }

        $user = User::where('username', $siswa->nisn)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Pengurus Kelas');
            })
            ->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'nisn' => ['Akun ini tidak memiliki akses sebagai Pengurus Kelas.'],
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'user' => $user,
                'siswa' => $siswa,
                'token' => $user->createToken('auth-token')->plainTextToken,
            ]
        ]);
    }

    /**
     * Logout endpoint
     */
    public function logout(): JsonResponse
    {
        Auth::user()->tokens()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil logout'
        ]);
    }
}
