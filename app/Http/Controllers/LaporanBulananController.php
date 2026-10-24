<?php

namespace App\Http\Controllers;

use App\Services\LaporanBulananService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class LaporanBulananController extends Controller
{
    protected $svc;

    public function __construct(LaporanBulananService $svc)
    {
        $this->svc = $svc;
    }

    public function index(Request $request)
    {
        $data         = $this->svc->susun($request->all());
        $data['opsi'] = $this->svc->opsi();

        return view('admin.laporan.index', $data);
    }

    /**
     * PDF resmi (kop surat + tanda tangan).
     * Memakai barryvdh/laravel-dompdf. Kalau paketnya belum terpasang,
     * halaman yang sama ditampilkan sebagai HTML siap cetak (Ctrl+P).
     */
    public function pdf(Request $request)
    {
        $data        = $this->svc->susun($request->all());
        $data['cfg'] = config('laporan');
        $nama        = $this->namaFile($data['f'], 'pdf');

        $logoPath = public_path(config('laporan.logo'));

        if (app()->bound('dompdf.wrapper')) {
            $data['logo']     = is_file($logoPath) ? $logoPath : null;
            $data['htmlMode'] = false;

            return app('dompdf.wrapper')
                ->loadView('admin.laporan.pdf', $data)
                ->setPaper('a4', 'landscape')
                ->stream($nama);
        }

        $data['logo']     = is_file($logoPath) ? asset(config('laporan.logo')) : null;
        $data['htmlMode'] = true;

        return view('admin.laporan.pdf', $data);
    }

    /**
     * Ekspor CSV (UTF-8 dengan BOM, pemisah ";") yang langsung terbaca rapi di Excel.
     */
    public function csv(Request $request)
    {
        $data = $this->svc->susun($request->all());
        $baris = $this->barisCsv($data);

        return response()->streamDownload(function () use ($baris) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($baris as $b) {
                fputcsv($out, $b, ';');
            }
            fclose($out);
        }, $this->namaFile($data['f'], 'csv'), ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    // ------------------------------------------------------------------

    protected function namaFile(array $f, $ext)
    {
        return 'laporan-perpustakaan-' . $f['tahun'] . '-' . str_pad($f['bulan'], 2, '0', STR_PAD_LEFT) . '.' . $ext;
    }

    protected function tgl($v, $format = 'd/m/Y')
    {
        return $v ? Carbon::parse($v)->format($format) : '';
    }

    protected function barisCsv(array $d)
    {
        $f   = $d['f'];
        $sir = $d['sirkulasi'];
        $keu = $d['keuangan'];
        $st  = $d['statistik'];
        $r   = [];

        $r[] = ['LAPORAN BULANAN PERPUSTAKAAN'];
        foreach ($d['label'] as $k => $v) {
            $r[] = [$k, $v];
        }
        $r[] = ['Dicetak', Carbon::now()->format('d/m/Y H:i')];
        $r[] = [];

        // --- 1. Sirkulasi
        $r[] = ['1. REKAPITULASI SIRKULASI'];
        $r[] = ['Total transaksi peminjaman', $sir['pinjam']['transaksi']];
        $r[] = ['Total judul dipinjam', $sir['pinjam']['judul']];
        $r[] = ['Total eksemplar dipinjam', $sir['pinjam']['eksemplar']];
        $r[] = ['Dikembalikan tepat waktu', $sir['tepat']];
        $r[] = ['Dikembalikan terlambat', $sir['terlambat']];
        $r[] = ['Belum kembali (outstanding)', $sir['outstanding']];
        $r[] = ['  di antaranya sudah lewat jatuh tempo', $sir['outstanding_telat']];
        $r[] = [];

        $r[] = ['Daftar pengembalian'];
        $r[] = ['No', 'Tgl Kembali', 'Jatuh Tempo', 'NISN', 'Nama', 'Kelas', 'Judul Buku', 'Status', 'Terlambat (hari)'];
        foreach ($sir['daftar_kembali'] as $i => $x) {
            $r[] = [
                $i + 1, $this->tgl($x->tgl_dikembalikan), $this->tgl($x->jatuh_tempo),
                $x->nisn, $x->nama, $x->kelas, $x->judul,
                LaporanBulananService::STATUS[$x->klasifikasi], $x->hari_telat,
            ];
        }
        $r[] = [];

        $r[] = ['Daftar buku belum kembali'];
        $r[] = ['No', 'Tgl Pinjam', 'Jatuh Tempo', 'NISN', 'Nama', 'Kelas', 'Judul Buku', 'Qty', 'Terlambat per hari ini (hari)'];
        foreach ($sir['daftar_outstanding'] as $i => $x) {
            $r[] = [
                $i + 1, $this->tgl($x->tanggal_pinjam), $this->tgl($x->jatuh_tempo),
                $x->nisn, $x->nama, $x->kelas, $x->judul, $x->qty, $x->hari_telat,
            ];
        }
        $r[] = [];

        // --- 2. Keuangan
        $r[] = ['2. REKAPITULASI KEUANGAN (DENDA)'];
        $r[] = ['Keterangan', 'Jumlah Denda', 'Nominal (Rp)'];
        $r[] = ['Denda terhitung bulan ini', $keu['terhitung']['n'], $keu['terhitung']['total']];
        $r[] = ['Denda terbayar / kas masuk bulan ini', $keu['terbayar']['n'], $keu['terbayar']['total']];
        $r[] = ['Dispensasi (dibebaskan) dari denda bulan ini', $keu['dispensasi']['n'], $keu['dispensasi']['total']];
        $r[] = ['Piutang denda belum dibayar (s.d. akhir bulan)', $keu['piutang']['n'], $keu['piutang']['total']];
        $r[] = ['Denda berjalan (estimasi, buku belum kembali)', $keu['berjalan']['n'], $keu['berjalan']['total']];
        $r[] = [];

        $r[] = ['Rincian denda terhitung bulan ini'];
        $r[] = ['No', 'Tgl Kembali', 'NISN', 'Nama', 'Kelas', 'Judul Buku', 'Qty', 'Hari', 'Tarif/Hari', 'Total', 'Status', 'Tgl Bayar'];
        foreach ($keu['rincian'] as $i => $x) {
            $r[] = [
                $i + 1, $this->tgl($x->tanggal_dikembalikan), $x->nisn, $x->nama, $x->kelas, $x->judul,
                $x->qty_terlambat, $x->hari_terlambat, $x->tarif_per_hari, $x->total_denda,
                $this->svc->labelStatusDenda($x->status), $this->tgl($x->dibayar_pada),
            ];
        }
        $r[] = [];

        // --- 3. Statistik
        $r[] = ['3. STATISTIK & TREN'];
        $r[] = ['10 buku paling sering dipinjam'];
        $r[] = ['Peringkat', 'Judul', 'Pengarang', 'Frekuensi', 'Eksemplar'];
        foreach ($st['top_buku'] as $i => $x) {
            $r[] = [$i + 1, $x->judul, $x->pengarang, $x->frekuensi, $x->eksemplar];
        }
        $r[] = [];

        $r[] = ['10 anggota paling aktif'];
        $r[] = ['Peringkat', 'NISN', 'Nama', 'Kelas', 'Frekuensi'];
        foreach ($st['top_anggota'] as $i => $x) {
            $r[] = [$i + 1, $x->nisn, $x->nama, $x->kelas, $x->frekuensi];
        }
        $r[] = [];

        $r[] = ['Kelas dengan peminjaman tertinggi'];
        $r[] = ['Peringkat', 'Kelas', 'Frekuensi', 'Jumlah Anggota Meminjam'];
        foreach ($st['top_kelas'] as $i => $x) {
            $r[] = [$i + 1, $x->kelas, $x->frekuensi, $x->anggota];
        }

        return $r;
    }
}