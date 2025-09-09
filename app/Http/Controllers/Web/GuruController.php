<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use Illuminate\Http\Request;

class GuruController extends Controller
{
    public function index()
    {
        $guru = Guru::paginate(10);
        return view('guru.index', compact('guru'));
    }

    public function create()
    {
        return view('guru.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip' => 'required|string|unique:guru,nip',
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string',
            'no_hp' => 'required|string',
            'email' => 'required|email|unique:users,email'
        ]);

        $guru = Guru::create($validated);

        // Create user account for guru
        $user = $guru->user()->create([
            'name' => $validated['nama'],
            'email' => $validated['email'],
            'password' => bcrypt('password') // Default password
        ]);

        // Attach guru role
        $user->roles()->attach(3); // Assuming 3 is guru role ID

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil ditambahkan');
    }

    public function edit(Guru $guru)
    {
        return view('guru.edit', compact('guru'));
    }

    public function update(Request $request, Guru $guru)
    {
        $validated = $request->validate([
            'nip' => 'required|string|unique:guru,nip,' . $guru->id,
            'nama' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:L,P',
            'alamat' => 'required|string',
            'no_hp' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $guru->user->id
        ]);

        $guru->update($validated);

        // Update user account
        $guru->user->update([
            'name' => $validated['nama'],
            'email' => $validated['email']
        ]);

        return redirect()->route('guru.index')->with('success', 'Data guru berhasil diperbarui');
    }

    public function destroy(Guru $guru)
    {
        // Delete user account
        if ($guru->user) {
            $guru->user->delete();
        }

        $guru->delete();
        return redirect()->route('guru.index')->with('success', 'Data guru berhasil dihapus');
    }
}
