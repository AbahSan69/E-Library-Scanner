<?php

namespace App\Http\Controllers;
use App\Models\Siswa;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\DetailPeminjaman;
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

        // ✅ Sesuaikan eager load relasi
        $query = Peminjaman::with(['siswa', 'detail.buku']);

        // 🔍 Filter pencarian (q)
        if ($request->has('q') && !empty($request->q)) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                // cari di siswa
                $sub->whereHas('siswa', function($s) use ($q) {
                    $s->where('nama', 'like', '%' . $q . '%')
                      ->orWhere('nisn', 'like', '%' . $q . '%');
                })
                // cari di detail -> buku
                ->orWhereHas('detail.buku', function($b) use ($q) {
                    $b->where('judul', 'like', '%' . $q . '%')
                      ->orWhere('id', 'like', '%' . $q . '%'); // sesuaikan kolom id buku
                })
                // cari di tanggal pinjam / kembali
                ->orWhere('tanggal_pinjam', 'like', '%' . $q . '%')
                ->orWhere('tanggal_kembali', 'like', '%' . $q . '%');
            });
        }

        // 📅 Filter tanggal created_at
        if ($request->has('today')) {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // 📌 Filter status
        if ($request->filled('status') && $request->status !== 'Semua') {
            $query->where('status', $request->status);
        }

        // 📄 Pagination & total
        $peminjaman = $query->orderBy('id', 'desc')->paginate(25);
        $total_peminjaman = $query->count();

        // agar query ikut terbawa di pagination
        $peminjaman->appends($request->only('q', 'today', 'start_date', 'end_date', 'status'));

        return view('admin.peminjaman.index', compact('peminjaman', 'siswa', 'buku', 'total_peminjaman'));
    }

    public function simpan_peminjaman(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'siswa_id'         => 'required|exists:siswa,id',
            'barcodes'         => 'required',
            'tanggal_kembali'  => 'required|date|after_or_equal:today',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $daftarBarcode = json_decode($request->barcodes, true);
        if (empty($daftarBarcode)) {
            return back()->with('toast_error', 'Tidak ada buku yang discan!');
        }

        DB::beginTransaction();

        try {
            // simpan data utama peminjaman
            $peminjaman = Peminjaman::create([
                'siswa_id'        => $request->siswa_id,
                'tanggal_pinjam'  => now(),
                'tanggal_kembali' => $request->tanggal_kembali,
                'status'          => 'Dipinjam',
            ]);

            // simpan setiap buku di detail
            foreach ($daftarBarcode as $b) {
                $kode = $b['kode'];
                $qty  = isset($b['qty']) && $b['qty'] > 0 ? (int)$b['qty'] : 1;
                $buku = Buku::where('no_barcode', $kode)->first();

                if (!$buku) {
                    DB::rollBack();
                    return back()->with('toast_error', "Buku dengan barcode {$kode} tidak ditemukan!");
                }

                if ($buku->stok < 1) {
                    DB::rollBack();
                    return back()->with('toast_error', "Stok buku '{$buku->judul}' habis!");
                }

                // kurangi stok
                $buku->decrement('stok', $qty);
                //update buku
                $buku->refresh();
                //cek jika stok 0
                if ($buku->stok <= 0) {
                    $buku->update(['status' => 'Dipinjam']);
                }


                // simpan ke tabel detail
                DetailPeminjaman::create([
                    'id_peminjaman' => $peminjaman->id,
                    'buku_id'       => $buku->id,
                    'qty'           => $qty,
                    'status'          => 'Dipinjam',
                ]);
            }

            DB::commit();

            return redirect()->route('admin.peminjaman')
                ->with('toast_success', 'Peminjaman berhasil disimpan!');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('toast_error', 'Terjadi kesalahan: ' . $e->getMessage());
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
        return redirect()->route('admin.peminjaman')
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
        $query = Peminjaman::with(['detail.buku', 'siswa']);

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

    public function show($kode)
    {
        $buku = Buku::where('no_barcode', $kode)->first();

        if ($buku) {
            return response()->json([
                'judul' => $buku->judul,
                'pengarang' => $buku->pengarang ?? null,
                'tahun' => $buku->tahun_terbit ?? null,
                'stok' => $buku->stok ?? null,
            ]);
        }

        return response()->json([
            'error' => 'Buku tidak ditemukan'
        ], 404);
    }

    public function coba()
    {
        return view('admin.peminjaman.tambah');
    }

}
