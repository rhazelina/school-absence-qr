<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    public function index()
    {
        $absensi = Absensi::with(['siswa', 'guru'])->latest()->paginate(10);
        return view('absensi.index', compact('absensi'));
    }

    public function show(Absensi $absensi)
    {
        return view('absensi.show', compact('absensi'));
    }

    public function export()
    {
        // TODO: Implement export to Excel/PDF functionality
        return redirect()->route('absensi.index')->with('info', 'Fitur export sedang dalam pengembangan');
    }
}
