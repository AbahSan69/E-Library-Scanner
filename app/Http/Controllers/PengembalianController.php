<?php

namespace App\Http\Controllers;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\DetailPeminjaman;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Exception;

class PengembalianController extends Controller
{
    public function index()
    {
        return view('admin.pengembalian.index');
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
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'barcodes' => 'required',
        ]);

        $barcodes = json_decode($request->barcodes, true);
        $siswaId = $request->siswa_id;

        $gagal = [];  // tampung buku yang gagal diproses

        DB::beginTransaction();

        try {
            foreach ($barcodes as $item) {
                $kode = $item['kode'];
                $qty  = isset($item['qty']) && $item['qty'] > 0 ? (int)$item['qty'] : 1;

                // ✅ Cari buku
                $buku = Buku::where('no_barcode', $kode)->first();
                if (!$buku) {
                    $gagal[] = "Barcode {$kode} tidak ditemukan di database buku";
                    continue;
                }

                // ✅ Cari detail peminjaman aktif untuk siswa ini dan buku ini
                $detail = DetailPeminjaman::where('buku_id', $buku->id)
                    ->where('status', 'Dipinjam')
                    ->whereHas('peminjaman', function ($q) use ($siswaId) {
                        $q->where('siswa_id', $siswaId)
                        ->where('status', 'Dipinjam');
                    })
                    ->first();

                if (!$detail) {
                    $gagal[] = "{$buku->judul} ❌ tidak ditemukan pada peminjaman aktif siswa ini";
                    continue;
                }

                // ✅ Pastikan qty yang dikembalikan tidak melebihi yang dipinjam
                if ($qty > $detail->qty) {
                    $gagal[] = "Qty pengembalian untuk '{$buku->judul}' melebihi qty yang dipinjam ({$detail->qty})";
                    continue;
                }

                // ✅ Tambahkan stok kembali
                $buku->increment('stok', $qty);
                //update buku
                $buku->refresh();

                // ✅ Update status buku berdasarkan stok
                if ($buku->stok > 0) {
                    $buku->update(['status' => 'Tersedia']);
                } else {
                    $buku->update(['status' => 'Tidak tersedia']);
                }

                // ✅ Update detail peminjaman
                if ($qty == $detail->qty) {
                    // semua qty dikembalikan → ubah status ke Kembali
                    $detail->update([
                        'status' => 'Kembali',
                        'tanggal_kembali' => now(),
                    ]);
                } else {
                    // hanya sebagian dikembalikan → kurangi qty
                    $detail->decrement('qty', $qty);
                }

                // ✅ Cek apakah semua detail dalam peminjaman ini sudah kembali
                $peminjaman = $detail->peminjaman;
                $masihDipinjam = $peminjaman->detail()
                    ->where('status', 'Dipinjam')
                    ->count();

                if ($masihDipinjam === 0) {
                    $peminjaman->update(['status' => 'Kembali']);
                }
            }

            DB::commit();

            if (count($gagal) > 0) {
                return redirect()->route('admin.pengembalian')
                    ->with('toast_success', 'Sebagian pengembalian berhasil ✅')
                    ->with('toast_warning', implode('<br>', $gagal));
            }

            return redirect()->route('admin.pengembalian')
                ->with('toast_success', 'Semua buku berhasil dikembalikan ✅');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('toast_error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function show($kode)
    {
        $buku = Buku::where('no_barcode', $kode)->first();

        if ($buku) {
            return response()->json([
                'judul' => $buku->judul,
                'pengarang' => $buku->pengarang ?? null,
                'tahun' => $buku->tahun_terbit ?? null,
            ]);
        }

        return response()->json([
            'error' => 'Buku tidak ditemukan'
        ], 404);
    }

}
