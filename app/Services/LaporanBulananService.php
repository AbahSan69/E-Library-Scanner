<?php

namespace App\Services;

use App\Models\Buku;
use App\Models\Denda;
use App\Models\DetailPeminjaman;
use App\Models\Peminjaman;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Menyusun data Laporan Bulanan Perpustakaan.
 *
 * Definisi yang dipakai:
 *  - Peminjaman bulan ini : peminjaman.tanggal_pinjam jatuh di bulan terpilih.
 *  - Pengembalian         : detail_peminjaman berstatus "Kembali" dan tanggal_kembali (aktual)
 *                           jatuh di bulan terpilih. Tepat waktu bila tanggal aktual <= jatuh tempo
 *                           (peminjaman.tanggal_kembali), selebihnya terlambat.
 *  - Belum kembali        : detail_peminjaman berstatus "Dipinjam" yang dipinjam s.d. akhir bulan.
 *  - Denda terhitung      : denda final yang buku-nya dikembalikan di bulan terpilih.
 *  - Denda terbayar       : denda lunas yang dibayar (dibayar_pada) di bulan terpilih (basis kas).
 *  - Piutang denda        : denda final berstatus unpaid s.d. akhir bulan (posisi saat ini).
 */
class LaporanBulananService
{
    const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    const STATUS = [
        'tepat_waktu' => 'Tepat Waktu',
        'terlambat'   => 'Terlambat',
        'outstanding' => 'Masih Dipinjam',
    ];

    /** Tingkat kelas => penulisan yang dikenali di kolom siswa.kelas */
    const TINGKAT = [
        'X'   => ['X', '10'],
        'XI'  => ['XI', '11'],
        'XII' => ['XII', '12'],
    ];

    protected $tPem;
    protected $tDet;
    protected $tBuku;
    protected $tSiswa;
    protected $tDenda;

    protected $kolomDdc;
    protected $kolomJurusan;

    public function __construct()
    {
        $this->tPem   = (new Peminjaman)->getTable();
        $this->tDet   = (new DetailPeminjaman)->getTable();
        $this->tBuku  = (new Buku)->getTable();
        $this->tSiswa = (new Siswa)->getTable();
        $this->tDenda = (new Denda)->getTable();

        // Kolom opsional: dideteksi otomatis. Kalau tidak ada, filternya dinonaktifkan.
        $this->kolomDdc     = $this->cariKolom($this->tBuku, ['ddc', 'klasifikasi_ddc', 'klasifikasi', 'kategori']);
        $this->kolomJurusan = $this->cariKolom($this->tSiswa, ['jurusan', 'program_keahlian', 'peminatan']);
    }

    protected function cariKolom($tabel, array $kandidat)
    {
        foreach ($kandidat as $k) {
            if (Schema::hasColumn($tabel, $k)) {
                return $k;
            }
        }
        return null;
    }

    // ------------------------------------------------------------------
    // Filter
    // ------------------------------------------------------------------

    public function filter(array $in): array
    {
        $now = Carbon::now();

        $bulan = (int) (isset($in['bulan']) ? $in['bulan'] : $now->month);
        if ($bulan < 1 || $bulan > 12) {
            $bulan = $now->month;
        }

        $tahun = (int) (isset($in['tahun']) ? $in['tahun'] : $now->year);
        if ($tahun < 2000 || $tahun > 2100) {
            $tahun = $now->year;
        }

        $awal  = Carbon::create($tahun, $bulan, 1)->startOfDay();
        $akhir = $awal->copy()->endOfMonth()->startOfDay();

        $tingkat = isset($in['tingkat']) ? (string) $in['tingkat'] : '';
        if (!isset(self::TINGKAT[$tingkat])) {
            $tingkat = '';
        }

        $status = isset($in['status']) ? (string) $in['status'] : '';
        if (!isset(self::STATUS[$status])) {
            $status = '';
        }

        return [
            'bulan'   => $bulan,
            'tahun'   => $tahun,
            'awal'    => $awal,
            'akhir'   => $akhir,
            'ddc'     => $this->kolomDdc ? trim((string) (isset($in['ddc']) ? $in['ddc'] : '')) : '',
            'tingkat' => $tingkat,
            'jurusan' => trim((string) (isset($in['jurusan']) ? $in['jurusan'] : '')),
            'status'  => $status,
        ];
    }

    public function opsi(): array
    {
        $ddc = [];
        if ($this->kolomDdc) {
            $c   = $this->kolomDdc;
            $ddc = DB::table($this->tBuku)->whereNotNull($c)->where($c, '!=', '')
                ->distinct()->orderBy($c)->pluck($c)->all();
        }

        $jurusan = [];
        if ($this->kolomJurusan) {
            $c       = $this->kolomJurusan;
            $jurusan = DB::table($this->tSiswa)->whereNotNull($c)->where($c, '!=', '')
                ->distinct()->orderBy($c)->pluck($c)->all();
        }

        $tahunIni = (int) date('Y');

        return [
            'ddc'          => $ddc,
            'punya_ddc'    => (bool) $this->kolomDdc,
            'jurusan'      => $jurusan,
            'punya_jurusan' => (bool) $this->kolomJurusan,
            'tahun'        => range($tahunIni, $tahunIni - 5),
        ];
    }

    protected function labelFilter(array $f): array
    {
        $l = ['Periode' => self::BULAN[$f['bulan']] . ' ' . $f['tahun']];

        if ($f['ddc'] !== '') {
            $l['Klasifikasi DDC'] = $f['ddc'];
        }
        if ($f['tingkat'] !== '') {
            $l['Tingkat kelas'] = $f['tingkat'];
        }
        if ($f['jurusan'] !== '') {
            $l['Jurusan'] = $f['jurusan'];
        }
        if ($f['status'] !== '') {
            $l['Status transaksi'] = self::STATUS[$f['status']];
        }

        return $l;
    }

    public function labelStatusDenda($status): string
    {
        if ($status === Denda::PAID) {
            return 'Lunas';
        }
        if ($status === Denda::WAIVED) {
            return 'Dispensasi';
        }
        return 'Belum dibayar';
    }

    // ------------------------------------------------------------------
    // Entry point
    // ------------------------------------------------------------------

    public function susun(array $input): array
    {
        $f = $this->filter($input);

        return [
            'f'         => $f,
            'label'     => $this->labelFilter($f),
            'sirkulasi' => $this->sirkulasi($f),
            'keuangan'  => $this->keuangan($f),
            'statistik' => $this->statistik($f),
        ];
    }

    // ------------------------------------------------------------------
    // Query dasar
    // ------------------------------------------------------------------

    protected function terapkanFilter($q, array $f)
    {
        if ($f['ddc'] !== '' && $this->kolomDdc) {
            $q->where('b.' . $this->kolomDdc, $f['ddc']);
        }

        if ($f['tingkat'] !== '') {
            $alias = self::TINGKAT[$f['tingkat']];
            $q->where(function ($w) use ($alias) {
                foreach ($alias as $a) {
                    $w->orWhere('s.kelas', $a)
                      ->orWhere('s.kelas', 'like', $a . ' %')
                      ->orWhere('s.kelas', 'like', $a . '-%')
                      ->orWhere('s.kelas', 'like', $a . '.%');
                }
            });
        }

        if ($f['jurusan'] !== '') {
            if ($this->kolomJurusan) {
                $q->where('s.' . $this->kolomJurusan, $f['jurusan']);
            } else {
                // tidak ada kolom jurusan: cari di teks kelas (mis. "XI IPA 2")
                $q->where('s.kelas', 'like', '%' . $f['jurusan'] . '%');
            }
        }

        return $q;
    }

    protected function dasarTransaksi(array $f)
    {
        $q = DB::table($this->tDet . ' as d')
            ->join($this->tPem . ' as p', 'p.id', '=', 'd.id_peminjaman')
            ->join($this->tSiswa . ' as s', 's.id', '=', 'p.siswa_id')
            ->join($this->tBuku . ' as b', 'b.id', '=', 'd.buku_id');

        return $this->terapkanFilter($q, $f);
    }

    protected function dasarKembali(array $f)
    {
        return $this->dasarTransaksi($f)
            ->where('d.status', 'Kembali')
            ->whereRaw('DATE(d.tanggal_kembali) BETWEEN ? AND ?', [
                $f['awal']->toDateString(),
                $f['akhir']->toDateString(),
            ]);
    }

    protected function dasarDenda(array $f)
    {
        $q = DB::table($this->tDenda . ' as dn')
            ->join($this->tDet . ' as d', 'd.id', '=', 'dn.detail_peminjaman_id')
            ->join($this->tPem . ' as p', 'p.id', '=', 'd.id_peminjaman')
            ->join($this->tSiswa . ' as s', 's.id', '=', 'p.siswa_id')
            ->join($this->tBuku . ' as b', 'b.id', '=', 'd.buku_id');

        return $this->terapkanFilter($q, $f);
    }

    protected function kolomTransaksi(): array
    {
        $c = [
            'd.id',
            'd.qty',
            'p.tanggal_pinjam',
            'p.tanggal_kembali as jatuh_tempo',
            'd.tanggal_kembali as tgl_dikembalikan',
            's.nisn',
            's.nama',
            's.kelas',
            'b.judul',
            'b.no_barcode',
        ];

        if ($this->kolomJurusan) {
            $c[] = 's.' . $this->kolomJurusan . ' as jurusan';
        }
        if ($this->kolomDdc) {
            $c[] = 'b.' . $this->kolomDdc . ' as ddc';
        }

        return $c;
    }

    // ------------------------------------------------------------------
    // 1. Rekapitulasi sirkulasi
    // ------------------------------------------------------------------

    public function sirkulasi(array $f): array
    {
        $a        = $f['awal']->toDateString();
        $z        = $f['akhir']->toDateString();
        $hariIni  = Carbon::today();
        $kolom    = $this->kolomTransaksi();

        $pinjam = $this->dasarTransaksi($f)
            ->whereRaw('DATE(p.tanggal_pinjam) BETWEEN ? AND ?', [$a, $z])
            ->selectRaw('COUNT(DISTINCT p.id) as transaksi, COUNT(d.id) as judul, COALESCE(SUM(d.qty), 0) as eksemplar')
            ->first();

        $tepat = $this->dasarKembali($f)
            ->whereRaw('DATE(d.tanggal_kembali) <= DATE(p.tanggal_kembali)')
            ->select($kolom)->orderBy('d.tanggal_kembali')->get()
            ->map(function ($r) {
                $r->klasifikasi = 'tepat_waktu';
                $r->hari_telat  = 0;
                return $r;
            });

        $telat = $this->dasarKembali($f)
            ->whereRaw('DATE(d.tanggal_kembali) > DATE(p.tanggal_kembali)')
            ->select($kolom)->orderBy('d.tanggal_kembali')->get()
            ->map(function ($r) {
                $r->klasifikasi = 'terlambat';
                $r->hari_telat  = Carbon::parse($r->jatuh_tempo)->startOfDay()
                    ->diffInDays(Carbon::parse($r->tgl_dikembalikan)->startOfDay());
                return $r;
            });

        $belum = $this->dasarTransaksi($f)
            ->where('d.status', 'Dipinjam')
            ->whereRaw('DATE(p.tanggal_pinjam) <= ?', [$z])
            ->select($kolom)->orderBy('p.tanggal_kembali')->get()
            ->map(function ($r) use ($hariIni) {
                $jt = Carbon::parse($r->jatuh_tempo)->startOfDay();
                $r->klasifikasi = 'outstanding';
                $r->hari_telat  = $jt->lt($hariIni) ? $jt->diffInDays($hariIni) : 0;
                return $r;
            });

        // Filter status transaksi menentukan daftar mana yang ditampilkan.
        $daftarKembali = collect();
        if ($f['status'] === '' || $f['status'] === 'tepat_waktu') {
            $daftarKembali = $daftarKembali->concat($tepat);
        }
        if ($f['status'] === '' || $f['status'] === 'terlambat') {
            $daftarKembali = $daftarKembali->concat($telat);
        }
        $daftarKembali = $daftarKembali->sortBy('tgl_dikembalikan')->values();

        $daftarBelum = ($f['status'] === '' || $f['status'] === 'outstanding') ? $belum : collect();

        return [
            'pinjam' => [
                'transaksi' => (int) $pinjam->transaksi,
                'judul'     => (int) $pinjam->judul,
                'eksemplar' => (int) $pinjam->eksemplar,
            ],
            'tepat'              => $tepat->count(),
            'terlambat'          => $telat->count(),
            'outstanding'        => $belum->count(),
            'outstanding_telat'  => $belum->where('hari_telat', '>', 0)->count(),
            'daftar_kembali'     => $daftarKembali,
            'daftar_outstanding' => $daftarBelum,
        ];
    }

    // ------------------------------------------------------------------
    // 2. Rekapitulasi keuangan (denda)
    // ------------------------------------------------------------------

    public function keuangan(array $f): array
    {
        $a = $f['awal']->toDateString();
        $z = $f['akhir']->toDateString();

        $agg = function ($q) {
            $r = $q->selectRaw('COUNT(dn.id) as n, COALESCE(SUM(dn.total_denda), 0) as total')->first();
            return ['n' => (int) $r->n, 'total' => (int) $r->total];
        };

        $terhitung = $agg(
            $this->dasarDenda($f)->where('dn.is_final', 1)
                ->whereRaw('DATE(dn.tanggal_dikembalikan) BETWEEN ? AND ?', [$a, $z])
        );

        $terbayar = $agg(
            $this->dasarDenda($f)->where('dn.status', Denda::PAID)
                ->whereRaw('DATE(dn.dibayar_pada) BETWEEN ? AND ?', [$a, $z])
        );

        $piutang = $agg(
            $this->dasarDenda($f)->where('dn.is_final', 1)->where('dn.status', Denda::UNPAID)
                ->whereRaw('DATE(dn.tanggal_dikembalikan) <= ?', [$z])
        );

        $dispensasi = $agg(
            $this->dasarDenda($f)->where('dn.is_final', 1)->where('dn.status', Denda::WAIVED)
                ->whereRaw('DATE(dn.tanggal_dikembalikan) BETWEEN ? AND ?', [$a, $z])
        );

        $berjalan = $agg($this->dasarDenda($f)->where('dn.is_final', 0));

        $rincian = $this->dasarDenda($f)
            ->where('dn.is_final', 1)
            ->whereRaw('DATE(dn.tanggal_dikembalikan) BETWEEN ? AND ?', [$a, $z])
            ->orderBy('dn.tanggal_dikembalikan')
            ->select(
                's.nisn', 's.nama', 's.kelas', 'b.judul',
                'dn.qty_terlambat', 'dn.hari_terlambat', 'dn.tarif_per_hari',
                'dn.total_denda', 'dn.status', 'dn.tanggal_dikembalikan', 'dn.dibayar_pada'
            )
            ->get();

        return [
            'terhitung'  => $terhitung,
            'terbayar'   => $terbayar,
            'piutang'    => $piutang,
            'dispensasi' => $dispensasi,
            'berjalan'   => $berjalan,
            'rincian'    => $rincian,
        ];
    }

    // ------------------------------------------------------------------
    // 3. Statistik & tren
    // ------------------------------------------------------------------

    public function statistik(array $f): array
    {
        $a = $f['awal']->toDateString();
        $z = $f['akhir']->toDateString();

        $dasar = function () use ($f, $a, $z) {
            return $this->dasarTransaksi($f)
                ->whereRaw('DATE(p.tanggal_pinjam) BETWEEN ? AND ?', [$a, $z]);
        };

        $topBuku = $dasar()
            ->groupBy('b.id', 'b.judul', 'b.pengarang')
            ->selectRaw('b.judul, b.pengarang, COUNT(d.id) as frekuensi, COALESCE(SUM(d.qty), 0) as eksemplar')
            ->orderBy('frekuensi', 'desc')->orderBy('b.judul')
            ->limit(10)->get();

        $topAnggota = $dasar()
            ->groupBy('s.id', 's.nisn', 's.nama', 's.kelas')
            ->selectRaw('s.nisn, s.nama, s.kelas, COUNT(d.id) as frekuensi')
            ->orderBy('frekuensi', 'desc')->orderBy('s.nama')
            ->limit(10)->get();

        $topKelas = $dasar()
            ->groupBy('s.kelas')
            ->selectRaw('s.kelas, COUNT(d.id) as frekuensi, COUNT(DISTINCT s.id) as anggota')
            ->orderBy('frekuensi', 'desc')->orderBy('s.kelas')
            ->limit(10)->get();

        return [
            'top_buku'    => $topBuku,
            'top_anggota' => $topAnggota,
            'top_kelas'   => $topKelas,
        ];
    }
}