<?php

namespace App\Http\Controllers;
use App\Models\Siswa;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Exports\PinjamanExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Exception;

class PeminjamanController extends Controller
{
    public function index(Request $request)
{
    $siswa = Siswa::all();
    $buku  = Buku::where('status', 'tersedia')->get();

    $query = Peminjaman::with(['siswa', 'buku']);

    // 🔍 filter pencarian (q)
    if ($request->has('q') && !empty($request->q)) {
        $q = $request->q;
        $query->where(function ($sub) use ($q) {
            $sub->whereHas('siswa', function($s) use ($q) {
                    $s->where('nama', 'like', '%' . $q . '%')
                      ->orWhere('nisn', 'like', '%' . $q . '%');
                })
                ->orWhereHas('buku', function($b) use ($q) {
                    $b->where('judul', 'like', '%' . $q . '%')
                      ->orWhere('buku_id', 'like', '%' . $q . '%');
                })
                ->orWhere('tanggal_pinjam', 'like', '%' . $q . '%')
                ->orWhere('tanggal_kembali', 'like', '%' . $q . '%');
        });
    }

    // 📅 filter tanggal (created_at)
    if ($request->has('today')) {
        $query->whereDate('created_at', now()->toDateString());
    } elseif ($request->filled('start_date') && $request->filled('end_date')) {
        $query->whereBetween('created_at', [
            $request->start_date . ' 00:00:00',
            $request->end_date . ' 23:59:59'
        ]);
    }

    // 📌 filter status
    if ($request->filled('status') && $request->status !== 'Semua') {
        $query->where('status', $request->status);
    }

    $peminjaman = $query->orderBy('id', 'desc')->paginate(10);

    $total_peminjaman = $query->count();

    // agar query ikut terbawa di pagination
    $peminjaman->appends($request->only('q', 'today', 'start_date', 'end_date', 'status'));

    return view('admin.peminjaman.index', compact('peminjaman', 'siswa', 'buku', 'total_peminjaman'));
}



    public function simpan_peminjaman(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'siswa_id'   => 'required|exists:siswa,id',
            'barcode'    => 'required',
            'tanggal_kembali'=> 'required|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        $buku = Buku::where('no_barcode', $request->barcode)->first();

        if (!$buku) {
            return back()->with('toast_error', 'Buku tidak ditemukan!');
        }

        if ($buku->status != 'Tersedia') {
            return back()->with('toast_error', 'Buku sedang dipinjam!');
        }

        try {
            // Simpan peminjaman baru dengan placeholder barcode
            $peminjaman = Peminjaman::create([
                'siswa_id'   => $request->siswa_id,
                'buku_id'    => $buku->id,
                'tanggal_pinjam' => now(),
                'tanggal_kembali'=> $request->tanggal_kembali,
                'status'     => 'Dipinjam',
            ]);

            $buku->update(['status' => 'Dipinjam']);

            DB::commit();

            return redirect()->route('admin.peminjaman') // ✅ pakai route resource index
                         ->with('toast_success', 'Data berhasil ditambahkan!');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                         ->with('toast_error', 'Terjadi kesalahan: '.$e->getMessage());
        }
    }


    public function edit_peminjaman(Request $request)
    {
        $id_peminjaman = $request->id;

        $validator = Validator::make($request->all(), [
            'tanggal_kembali'=> 'required|date',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            $peminjaman = Peminjaman::findOrFail($id_peminjaman);

            $data = [
                'tanggal_kembali' => $request->tanggal_kembali
            ];

            $peminjaman->update($data);

            DB::commit();

            return redirect()->route('admin.peminjaman')
                         ->with('toast_success', 'Data peminjaman berhasil diperbarui!')
                         ->with('activeTab', 'akun');
        } catch (Exception $e) {
            DB::rollBack();
            return redirect()->back()
                         ->with('error', 'Terjadi kesalahan saat menyimpan data.')
                         ->with('activeTab', 'akun');
        }
    }

    public function hapus_peminjaman($id)
    {
        $peminjaman = Peminjaman::find($id);

        if (!$peminjaman) {
            return redirect()->back()->with('toast_error', 'Data tidak ditemukan!');
        }

        $buku = Buku::where('id', $peminjaman->buku_id);

        if (!$buku) {
            return redirect()->back()->with('toast_error', 'Data tidak ditemukan!');
        }

        $data = [
            'status' => 'Tersedia'
        ];

        $buku->update($data);

        $peminjaman->delete();
        return redirect()->route('peminjaman')
                         ->with('toast_success', 'Data Berhasil Dihapus!');
    }

    public function cariSiswa(Request $request)
    {
        $query = Siswa::query();

        if ($request->has('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->q . '%')
                ->orWhere('nisn', 'like', '%' . $request->q . '%');
            });
        }

        $perPage = 10;
        $page = $request->get('page', 1);

        $siswas = $query->paginate($perPage, ['*'], 'page', $page);

        $items = collect($siswas->items())->map(fn($s) => [
            'id' => $s->id,
            'text' => $s->nisn . ' - ' . $s->nama
        ]);

        return response()->json([
            'items' => $items,
            'more' => $siswas->hasMorePages()
        ]);
    }

    // SiswaController.php
    public function search(Request $request)
    {
        $query = $request->get('q');
        $siswa = Siswa::where('nama', 'like', "%$query%")
                  ->orWhere('nisn', 'like', "%$query%")
                  ->limit(10)
                  ->get(['id','nisn','nama']);

        return response()->json($siswa);
    }

    public function export_pinjaman(Request $request)
    {
        $query = Peminjaman::with(['buku', 'siswa']);

        // Filter Hari Ini
        if ($request->has('todayCheck')) {
            $query->whereDate('created_at', Carbon::today());
        } 
        // Filter Range Tanggal
        elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date, $request->end_date]);
        }

        // Filter Status
        if ($request->filled('status') && $request->status !== 'Semua') {
            $query->where('status', $request->status);
        }

        $data = $query->get();

        if ($data->isEmpty()) {
            return back()->with('warning', 'Tidak ada data untuk tanggal yang dipilih.');
        }
        $filename = 'data_pinjaman_' . now()->format('Ymd_His') . '.xlsx';
        return Excel::download(new PinjamanExport($data), $filename);
    }

}
