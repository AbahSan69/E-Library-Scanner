<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Peminjaman;
use App\Models\Buku;
use App\Models\Siswa;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        //Total Peminjaman per tanggal hari ini
        $today = Carbon::today();
        $total_hari_ini = Peminjaman::whereDate('created_at', $today)->count();
        //Total Peminjaman
        $total_peminjaman = Peminjaman::query()->count();
        //Total Buku
        $total_buku = Buku::query()->count();
        //Total siswa
        $total_siswa = Siswa::query()->count();

        $top_buku = Buku::withCount('peminjaman')
                    ->orderBy('peminjaman_count', 'desc')
                    ->take(10)
                    ->get();

        $top_siswa = Siswa::withCount('peminjaman')
                    ->orderBy('peminjaman_count', 'desc')
                    ->take(10)
                    ->get();
        return view('petugas.dashboard.index', compact('total_hari_ini','total_peminjaman','total_buku','total_siswa','top_buku','top_siswa'));
    }
}
