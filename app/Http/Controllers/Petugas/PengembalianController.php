<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Exception;

class PengembalianController extends Controller
{
    public function index()
    {
        return view('petugas.pengembalian.index');
    }

    public function cariBarcode($barcode)
    {
        $peminjaman = Peminjaman::with(['siswa','buku'])
            ->whereHas('buku', function($q) use ($barcode) {
                $q->where('no_barcode', $barcode);
            })
            ->where('status', 'Dipinjam')
            ->first();

        if (!$peminjaman) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan atau sudah dikembalikan']);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id'          => $peminjaman->id,
                'nisn'        => $peminjaman->siswa->nisn,
                'nama'        => $peminjaman->siswa->nama,
                'isbn'        => $peminjaman->buku->isbn,
                'judul'       => $peminjaman->buku->judul,
                'tanggal_pinjam'  => $peminjaman->tanggal_pinjam,
                'tanggal_kembali' => $peminjaman->tanggal_kembali,
                'status' => $peminjaman->status,
            ]
        ]);
    }

    public function simpan_pengembalian(Request $request)
    {
        try {
            DB::beginTransaction();
            // ✅ Ambil data peminjaman
            $pinjam = Peminjaman::find($request->id);

            if (!$pinjam) {
                return back()->with('toast_error', 'Data peminjaman tidak ditemukan!');
            }

            // ✅ Ambil data buku berdasarkan peminjaman
            $buku = Buku::find($pinjam->buku_id);

            if (!$buku) {
                return back()->with('toast_error', 'Data buku tidak ditemukan!');
            }

            // ✅ Cek status
            if ($pinjam->status !== 'Dipinjam') {
                return back()->with('toast_error', 'Buku ini tidak dalam status dipinjam!');
            }

            // ✅ Update status peminjaman & buku
            $pinjam->update([
                'status'          => 'Dikembalikan',
            ]);

            $buku->update([
                'status' => 'Tersedia',
            ]);

            DB::commit();

            return redirect()->route('petugas.peminjaman') // pastikan route sesuai
                         ->with('toast_success', 'Pengembalian berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                         ->with('toast_error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
