<?php

namespace App\Services;

use App\Models\Denda;
use App\Models\DetailPeminjaman;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Prinsip: TIDAK ADA penghapusan data siswa. Semua perubahan hanya mengubah
 * kolom status/kelas dan dicatat di tabel siswa_status_log (jejak audit).
 */
class KeanggotaanService
{
    const NAMA_BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    const BULAN_ROMAWI = [
        1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
        7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
    ];

    /** Urutan tingkat, dipakai agar kenaikan diproses dari tingkat tertinggi dulu */
    const URUT_TINGKAT = ['X' => 1, 'XI' => 2, 'XII' => 3];

    protected $tSiswa;
    protected $tPem;
    protected $tDet;
    protected $tBuku;
    protected $tDenda;
    protected $siswaPunyaUpdatedAt;

    public function __construct()
    {
        $this->tSiswa = (new Siswa)->getTable();
        $this->tPem   = (new Peminjaman)->getTable();
        $this->tDet   = (new DetailPeminjaman)->getTable();
        $this->tBuku  = (new Buku)->getTable();
        $this->tDenda = (new Denda)->getTable();

        $this->siswaPunyaUpdatedAt = Schema::hasColumn($this->tSiswa, 'updated_at');
    }

    // ------------------------------------------------------------------
    // Pembacaan tingkat & saran kelas tujuan
    // ------------------------------------------------------------------

    /** "XI IPA 2" => "XI", "10 MIPA 1" => "X", selain itu null */
    public static function tingkatDari($kelas)
    {
        if (!preg_match('/^\s*(XII|XI|X|12|11|10)(?=[\s\-\._]|$)/i', (string) $kelas, $m)) {
            return null;
        }

        $t   = strtoupper($m[1]);
        $map = ['10' => 'X', '11' => 'XI', '12' => 'XII'];

        return isset($map[$t]) ? $map[$t] : $t;
    }

    /** "X IPA 1" => "XI IPA 1", "11 IPS 2" => "12 IPS 2", kelas akhir => null */
    public static function sarankanKelasNaik($kelas)
    {
        if (!preg_match('/^(\s*)(XII|XI|X|12|11|10)(?=[\s\-\._]|$)(.*)$/is', (string) $kelas, $m)) {
            return null;
        }

        $next = ['X' => 'XI', 'XI' => 'XII', '10' => '11', '11' => '12'];
        $tok  = strtoupper($m[2]);

        if (!isset($next[$tok])) {
            return null; // XII / 12: bukan naik kelas, tapi kelulusan
        }

        return $m[1] . $next[$tok] . $m[3];
    }

    // ------------------------------------------------------------------
    // Validasi tanggungan (Bebas Pustaka)
    // ------------------------------------------------------------------

    /**
     * @return array [siswa_id => ['buku' => [..], 'denda_n' => int, 'denda_total' => int]]
     */
    public function tanggungan(array $ids): array
    {
        $out = [];
        foreach ($ids as $id) {
            $out[$id] = ['buku' => [], 'denda_n' => 0, 'denda_total' => 0];
        }
        if (!$ids) {
            return $out;
        }

        foreach (array_chunk(array_values($ids), 500) as $chunk) {
            $buku = DB::table($this->tDet . ' as d')
                ->join($this->tPem . ' as p', 'p.id', '=', 'd.id_peminjaman')
                ->join($this->tBuku . ' as b', 'b.id', '=', 'd.buku_id')
                ->whereIn('p.siswa_id', $chunk)
                ->where('d.status', 'Dipinjam')
                ->select('p.siswa_id', 'b.judul', 'd.qty', 'p.tanggal_kembali as jatuh_tempo')
                ->orderBy('p.tanggal_kembali')
                ->get();

            foreach ($buku as $b) {
                $out[$b->siswa_id]['buku'][] = $b;
            }

            $denda = DB::table($this->tDenda)
                ->whereIn('siswa_id', $chunk)
                ->where('is_final', 1)
                ->where('status', Denda::UNPAID)
                ->groupBy('siswa_id')
                ->selectRaw('siswa_id, COUNT(*) as n, COALESCE(SUM(total_denda), 0) as total')
                ->get();

            foreach ($denda as $d) {
                $out[$d->siswa_id]['denda_n']     = (int) $d->n;
                $out[$d->siswa_id]['denda_total'] = (int) $d->total;
            }
        }

        return $out;
    }

    public static function adaTanggungan(array $t): bool
    {
        return count($t['buku']) > 0 || $t['denda_n'] > 0;
    }

    // ------------------------------------------------------------------
    // Pratinjau rombel aktif
    // ------------------------------------------------------------------

    public function rombelAktif(): array
    {
        $siswa = DB::table($this->tSiswa)
            ->where('status', 'active')
            ->select('id', 'nisn', 'nama', 'kelas')
            ->orderBy('kelas')->orderBy('nama')
            ->get();

        $hasil = ['X' => [], 'XI' => [], 'XII' => [], 'lain' => [], 'tanggungan_xii' => []];
        $hitung = [];
        $idXii  = [];

        foreach ($siswa as $s) {
            $t   = self::tingkatDari($s->kelas);
            $key = $t ?: 'lain';
            $k   = (string) $s->kelas;

            if (!isset($hitung[$key][$k])) {
                $hitung[$key][$k] = ['kelas' => $k, 'jumlah' => 0, 'tanggungan' => 0, 'ids' => []];
            }
            $hitung[$key][$k]['jumlah']++;
            $hitung[$key][$k]['ids'][] = $s->id;

            if ($t === 'XII') {
                $idXii[] = $s->id;
            }
        }

        $tg = $this->tanggungan($idXii);

        foreach (['X', 'XI', 'XII', 'lain'] as $key) {
            if (empty($hitung[$key])) {
                continue;
            }
            foreach ($hitung[$key] as $row) {
                if ($key === 'XII') {
                    foreach ($row['ids'] as $id) {
                        if (self::adaTanggungan($tg[$id])) {
                            $row['tanggungan']++;
                        }
                    }
                }
                $row['saran'] = ($key === 'X' || $key === 'XI') ? self::sarankanKelasNaik($row['kelas']) : null;
                unset($row['ids']);
                $hasil[$key][] = $row;
            }
        }

        foreach ($siswa as $s) {
            if (self::tingkatDari($s->kelas) === 'XII' && self::adaTanggungan($tg[$s->id])) {
                $hasil['tanggungan_xii'][] = (object) [
                    'nama'        => $s->nama,
                    'nisn'        => $s->nisn,
                    'kelas'       => $s->kelas,
                    'buku'        => count($tg[$s->id]['buku']),
                    'denda_n'     => $tg[$s->id]['denda_n'],
                    'denda_total' => $tg[$s->id]['denda_total'],
                ];
            }
        }

        return $hasil;
    }

    // ------------------------------------------------------------------
    // Kenaikan kelas massal
    // ------------------------------------------------------------------

    /**
     * @param array $mapping [kelas_asal => kelas_tujuan]
     */
    public function naikKelas(array $mapping, $userId): array
    {
        $items = [];
        foreach ($mapping as $src => $tgt) {
            $tgt = trim((string) $tgt);
            $src = (string) $src;
            $t   = self::tingkatDari($src);

            if ($tgt === '' || $tgt === $src || !in_array($t, ['X', 'XI'], true)) {
                continue;
            }
            $items[] = ['src' => $src, 'tgt' => $tgt, 'tingkat' => $t];
        }

        if (!$items) {
            return ['ok' => false, 'pesan' => 'Tidak ada rombel yang dipilih atau kelas tujuan kosong.'];
        }

        // Tingkat tertinggi diproses dulu supaya nama rombel tidak bertabrakan.
        usort($items, function ($a, $b) {
            return self::URUT_TINGKAT[$b['tingkat']] <=> self::URUT_TINGKAT[$a['tingkat']];
        });

        // XI -> XII hanya boleh jika kelas XII lama sudah diluluskan.
        $adaXi = false;
        foreach ($items as $it) {
            if ($it['tingkat'] === 'XI') {
                $adaXi = true;
            }
        }
        if ($adaXi) {
            $sisaXii = DB::table($this->tSiswa)->where('status', 'active')->get(['kelas'])
                ->filter(function ($s) {
                    return self::tingkatDari($s->kelas) === 'XII';
                })->count();

            if ($sisaXii > 0) {
                return [
                    'ok'    => false,
                    'pesan' => "Masih ada {$sisaXii} siswa aktif kelas XII. Luluskan kelas XII lebih dulu sebelum menaikkan kelas XI ke XII.",
                ];
            }
        }

        $batch = (string) Str::uuid();
        $total = 0;
        $now   = Carbon::now();

        DB::transaction(function () use ($items, $batch, $userId, $now, &$total) {
            foreach ($items as $it) {
                $ids = DB::table($this->tSiswa)
                    ->where('status', 'active')->where('kelas', $it['src'])
                    ->pluck('id')->all();

                if (!$ids) {
                    continue;
                }

                $upd = ['kelas' => $it['tgt']];
                if ($this->siswaPunyaUpdatedAt) {
                    $upd['updated_at'] = $now;
                }
                DB::table($this->tSiswa)->whereIn('id', $ids)->update($upd);

                $log = [];
                foreach ($ids as $id) {
                    $log[] = $this->barisLog($id, 'naik_kelas', $batch, $userId, [
                        'dari_kelas' => $it['src'], 'ke_kelas' => $it['tgt'],
                        'dari_status' => 'active', 'ke_status' => 'active',
                        'alasan' => 'Kenaikan kelas tahun ajaran baru',
                    ]);
                }
                foreach (array_chunk($log, 200) as $c) {
                    DB::table('siswa_status_log')->insert($c);
                }

                $total += count($ids);
            }
        });

        return ['ok' => true, 'siswa' => $total, 'rombel' => count($items), 'batch' => $batch];
    }

    // ------------------------------------------------------------------
    // Kelulusan massal
    // ------------------------------------------------------------------

    /**
     * @param array $rombel daftar nama kelas tingkat XII
     * @param bool  $paksa  tetap luluskan siswa yang masih punya tanggungan
     */
    public function luluskan(array $rombel, $tahun, $paksa, $userId): array
    {
        $rombel = array_values(array_filter($rombel, function ($k) {
            return self::tingkatDari($k) === 'XII';
        }));

        if (!$rombel) {
            return ['ok' => false, 'pesan' => 'Pilih minimal satu rombel kelas XII.'];
        }

        $siswa = DB::table($this->tSiswa)
            ->where('status', 'active')->whereIn('kelas', $rombel)
            ->select('id', 'nisn', 'nama', 'kelas')->get();

        $tg     = $this->tanggungan($siswa->pluck('id')->all());
        $lulus  = [];
        $ditahan = [];

        foreach ($siswa as $s) {
            if (self::adaTanggungan($tg[$s->id]) && !$paksa) {
                $ditahan[] = (object) [
                    'nama'        => $s->nama,
                    'nisn'        => $s->nisn,
                    'kelas'       => $s->kelas,
                    'buku'        => count($tg[$s->id]['buku']),
                    'denda_n'     => $tg[$s->id]['denda_n'],
                    'denda_total' => $tg[$s->id]['denda_total'],
                ];
                continue;
            }
            $lulus[] = $s;
        }

        $batch = (string) Str::uuid();
        $now   = Carbon::now();

        DB::transaction(function () use ($lulus, $tahun, $batch, $userId, $now, $tg) {
            $ids = array_map(function ($s) {
                return $s->id;
            }, $lulus);

            foreach (array_chunk($ids, 500) as $chunk) {
                $upd = [
                    'status'            => 'graduated',
                    'status_changed_at' => $now,
                    'status_note'       => 'Lulus tahun ' . $tahun,
                    'tahun_lulus'       => $tahun,
                ];
                if ($this->siswaPunyaUpdatedAt) {
                    $upd['updated_at'] = $now;
                }
                DB::table($this->tSiswa)->whereIn('id', $chunk)->update($upd);
            }

            $log = [];
            foreach ($lulus as $s) {
                $log[] = $this->barisLog($s->id, 'lulus', $batch, $userId, [
                    'dari_kelas' => $s->kelas, 'ke_kelas' => $s->kelas,
                    'dari_status' => 'active', 'ke_status' => 'graduated',
                    'alasan' => 'Lulus tahun ' . $tahun
                        . (self::adaTanggungan($tg[$s->id]) ? ' (diluluskan dengan tanggungan)' : ''),
                ]);
            }
            foreach (array_chunk($log, 200) as $c) {
                DB::table('siswa_status_log')->insert($c);
            }
        });

        return [
            'ok'      => true,
            'lulus'   => count($lulus),
            'ditahan' => $ditahan,
            'batch'   => $batch,
        ];
    }

    // ------------------------------------------------------------------
    // Penyesuaian individual
    // ------------------------------------------------------------------

    public function ubahStatus(Siswa $siswa, $status, $alasan, $paksa, $userId): array
    {
        $daftar = Siswa::statusList();
        if (!isset($daftar[$status])) {
            return ['ok' => false, 'pesan' => 'Status tidak dikenal.'];
        }

        $lama = $siswa->getAttribute('status') ?: 'active';
        if ($lama === $status) {
            return ['ok' => false, 'pesan' => 'Status anggota sudah ' . $daftar[$status] . '.'];
        }

        // Validasi tanggungan berlaku untuk setiap perpindahan ke status non-aktif.
        if ($status !== 'active') {
            $tg = $this->tanggungan([$siswa->id])[$siswa->id];
            if (self::adaTanggungan($tg) && !$paksa) {
                return ['ok' => false, 'pesan' => 'Anggota masih memiliki tanggungan.', 'tanggungan' => $tg];
            }
        }

        $now = Carbon::now();

        DB::transaction(function () use ($siswa, $status, $alasan, $lama, $userId, $now) {
            $upd = [
                'status'            => $status,
                'status_changed_at' => $now,
                'status_note'       => $alasan ?: null,
            ];
            if ($status === 'active') {
                $upd['tahun_lulus'] = null;
            }
            if ($this->siswaPunyaUpdatedAt) {
                $upd['updated_at'] = $now;
            }
            DB::table($this->tSiswa)->where('id', $siswa->id)->update($upd);

            DB::table('siswa_status_log')->insert($this->barisLog($siswa->id, 'ubah_status', null, $userId, [
                'dari_kelas' => $siswa->kelas, 'ke_kelas' => $siswa->kelas,
                'dari_status' => $lama, 'ke_status' => $status,
                'alasan' => $alasan ?: null,
            ]));
        });

        return ['ok' => true];
    }

    /** Tinggal kelas / koreksi kelas manual */
    public function ubahKelas(Siswa $siswa, $kelasBaru, $alasan, $userId): array
    {
        $kelasBaru = trim((string) $kelasBaru);
        if ($kelasBaru === '') {
            return ['ok' => false, 'pesan' => 'Kelas baru wajib diisi.'];
        }
        if ($kelasBaru === (string) $siswa->kelas) {
            return ['ok' => false, 'pesan' => 'Kelas baru sama dengan kelas saat ini.'];
        }

        $now = Carbon::now();

        DB::transaction(function () use ($siswa, $kelasBaru, $alasan, $userId, $now) {
            $upd = ['kelas' => $kelasBaru];
            if ($this->siswaPunyaUpdatedAt) {
                $upd['updated_at'] = $now;
            }
            DB::table($this->tSiswa)->where('id', $siswa->id)->update($upd);

            DB::table('siswa_status_log')->insert($this->barisLog($siswa->id, 'ubah_kelas', null, $userId, [
                'dari_kelas' => $siswa->kelas, 'ke_kelas' => $kelasBaru,
                'dari_status' => $siswa->getAttribute('status'), 'ke_status' => $siswa->getAttribute('status'),
                'alasan' => $alasan ?: 'Penyesuaian kelas manual',
            ]));
        });

        return ['ok' => true];
    }

    // ------------------------------------------------------------------
    // Surat Keterangan Bebas Pustaka
    // ------------------------------------------------------------------

    /**
     * Mengembalikan nomor & tanggal surat, atau null kalau masih ada tanggungan.
     * Cetak ulang di hari yang sama memakai nomor yang sama.
     */
    public function terbitkanBebasPustaka(Siswa $siswa, $userId)
    {
        $tg = $this->tanggungan([$siswa->id])[$siswa->id];
        if (self::adaTanggungan($tg)) {
            return null;
        }

        $hariIni = Carbon::today();

        $log = DB::table('siswa_status_log')
            ->where('siswa_id', $siswa->id)->where('tipe', 'bebas_pustaka')
            ->whereRaw('DATE(created_at) = ?', [$hariIni->toDateString()])
            ->orderBy('id', 'desc')->first();

        if ($log && $log->nomor_surat) {
            return ['nomor' => $log->nomor_surat, 'tanggal' => $hariIni];
        }

        $id = DB::table('siswa_status_log')->insertGetId($this->barisLog($siswa->id, 'bebas_pustaka', null, $userId, [
            'dari_kelas' => $siswa->kelas, 'ke_kelas' => $siswa->kelas,
            'dari_status' => $siswa->getAttribute('status'), 'ke_status' => $siswa->getAttribute('status'),
            'alasan' => 'Penerbitan Surat Keterangan Bebas Pustaka',
        ]));

        $nomor = sprintf('%03d/BP/PERPUS/%s/%d', $id, self::BULAN_ROMAWI[$hariIni->month], $hariIni->year);
        DB::table('siswa_status_log')->where('id', $id)->update(['nomor_surat' => $nomor]);

        return ['nomor' => $nomor, 'tanggal' => $hariIni];
    }

    // ------------------------------------------------------------------
    // Log
    // ------------------------------------------------------------------

    protected function barisLog($siswaId, $tipe, $batch, $userId, array $x): array
    {
        $now = Carbon::now();

        return [
            'siswa_id'    => $siswaId,
            'batch_id'    => $batch,
            'tipe'        => $tipe,
            'dari_kelas'  => isset($x['dari_kelas']) ? $x['dari_kelas'] : null,
            'ke_kelas'    => isset($x['ke_kelas']) ? $x['ke_kelas'] : null,
            'dari_status' => isset($x['dari_status']) ? $x['dari_status'] : null,
            'ke_status'   => isset($x['ke_status']) ? $x['ke_status'] : null,
            'alasan'      => isset($x['alasan']) ? $x['alasan'] : null,
            'nomor_surat' => null,
            'user_id'     => $userId,
            'created_at'  => $now,
            'updated_at'  => $now,
        ];
    }

    /** Riwayat terakhir; operasi massal dikelompokkan per batch. */
    public function logTerakhir($limit = 15)
    {
        $rows = DB::table('siswa_status_log')
            ->selectRaw("COALESCE(batch_id, CONCAT('s', id)) as grp, tipe, MAX(created_at) as waktu, COUNT(*) as jumlah,
                         MAX(siswa_id) as siswa_id, MIN(dari_kelas) as dari_kelas, MAX(ke_kelas) as ke_kelas,
                         MIN(dari_status) as dari_status, MAX(ke_status) as ke_status, MAX(alasan) as alasan,
                         MAX(nomor_surat) as nomor_surat")
            ->groupBy('grp', 'tipe')
            ->orderBy('waktu', 'desc')
            ->limit($limit)->get();

        $ids   = $rows->where('jumlah', 1)->pluck('siswa_id')->all();
        $nama  = $ids ? DB::table($this->tSiswa)->whereIn('id', $ids)->pluck('nama', 'id')->all() : [];

        return $rows->map(function ($r) use ($nama) {
            $r->nama = ($r->jumlah == 1 && isset($nama[$r->siswa_id])) ? $nama[$r->siswa_id] : null;
            return $r;
        });
    }
}