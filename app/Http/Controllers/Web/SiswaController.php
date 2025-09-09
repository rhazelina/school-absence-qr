<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use Illuminate\Http\Request;

class SiswaController extends Controller
{
    public function index()
    {
        $siswa = Siswa::paginate(10);
        return view('siswa.index', compact('siswa'));
    }

    public function create()
    {
        return view('siswa.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis' => 'required|string|unique:siswa,nis',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas' => 'required|string',
            'alamat' => 'required|string',
            'nama_wali' => 'required|string',
            'no_hp_wali' => 'required|string',
            'email' => 'required|email|unique:users,email'
        ]);

        $siswa = Siswa::create($validated);

        // Create user account for siswa
        $user = $siswa->user()->create([
            'name' => $validated['nama'],
            'email' => $validated['email'],
            'password' => bcrypt('password') // Default password
        ]);

        // Attach siswa role
        $user->roles()->attach(4); // Assuming 4 is siswa role ID

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil ditambahkan');
    }

    public function edit(Siswa $siswa)
    {
        return view('siswa.edit', compact('siswa'));
    }

    public function update(Request $request, Siswa $siswa)
    {
        $validated = $request->validate([
            'nis' => 'required|string|unique:siswa,nis,' . $siswa->id,
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'kelas' => 'required|string',
            'alamat' => 'required|string',
            'nama_wali' => 'required|string',
            'no_hp_wali' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $siswa->user->id
        ]);

        $siswa->update($validated);

        // Update user account
        $siswa->user->update([
            'name' => $validated['nama'],
            'email' => $validated['email']
        ]);

        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil diperbarui');
    }

    public function destroy(Siswa $siswa)
    {
        // Delete user account
        if ($siswa->user) {
            $siswa->user->delete();
        }

        $siswa->delete();
        return redirect()->route('siswa.index')->with('success', 'Data siswa berhasil dihapus');
    }
}
