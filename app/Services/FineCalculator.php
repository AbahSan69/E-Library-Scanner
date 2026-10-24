<?php

namespace App\Services;

use App\Models\Denda;
use App\Models\DetailPeminjaman;
use App\Models\HariLibur;
use App\Models\SystemSetting;
use Carbon\Carbon;

class FineCalculator
{
    /**
     * Hari terlambat = hari sejak H+1 jatuh tempo sampai tanggal akhir,
     * dikurangi hari yang dikecualikan dan masa toleransi, minimal 0.
     * Berbasis tanggal (jam diabaikan).
     */
    public function hitungHariTerlambat(Carbon $jatuhTempo, Carbon $akhir, ?SystemSetting $s = null): int
    {
        $s ??= SystemSetting::current();

        $mulai = $jatuhTempo->copy()->startOfDay()->addDay();
        $akhir = $akhir->copy()->startOfDay();

        if ($akhir->lt($mulai)) {
            return 0;
        }

        $libur = [];
        if ($s->exclude_holidays) {
            $libur = HariLibur::whereBetween('tanggal', [$mulai->toDateString(), $akhir->toDateString()])
                ->pluck('tanggal')
                ->map(fn ($d) => Carbon::parse($d)->toDateString())
                ->all();
        }

        $hari = 0;
        for ($d = $mulai->copy(); $d->lte($akhir); $d->addDay()) {
            if ($s->exclude_saturday && $d->isSaturday()) continue;
            if ($s->exclude_sunday && $d->isSunday()) continue;
            if (in_array($d->toDateString(), $libur, true)) continue;
            $hari++;
        }

        return max(0, $hari - (int) $s->grace_period_days);
    }

    /**
     * Catat denda FINAL untuk $qty eksemplar yang dikembalikan pada $waktu.
     * Panggil saat petugas memindai pengembalian. Mengembalikan null jika tidak telat.
     */
    public function catatPengembalian(DetailPeminjaman $detail, int $qty, ?Carbon $waktu = null): ?Denda
    {
        $detail->loadMissing('peminjaman');
        $waktu ??= now();
        $s = SystemSetting::current();

        // peminjaman.tanggal_kembali dipakai sebagai tanggal jatuh tempo
        $jatuhTempo = Carbon::parse($detail->peminjaman->tanggal_kembali);
        $hari = $this->hitungHariTerlambat($jatuhTempo, $waktu, $s);

        if ($hari === 0) {
            return null;
        }

        $tarif = (int) $s->fine_rate_per_day;

        return Denda::create([
            'detail_peminjaman_id' => $detail->id,
            'siswa_id'             => $detail->peminjaman->siswa_id,
            'is_final'             => true,
            'tanggal_dikembalikan' => $waktu,
            'qty_terlambat'        => $qty,
            'hari_terlambat'       => $hari,
            'tarif_per_hari'       => $tarif,
            'total_denda'          => $hari * $tarif * $qty,
            'status'               => Denda::UNPAID,
        ]);
    }

    /**
     * Hitung ulang denda BERJALAN untuk sisa eksemplar yang belum dikembalikan.
     * Dipanggil cron harian dan setelah pengembalian parsial.
     */
    public function hitungBerjalan(DetailPeminjaman $detail): ?Denda
    {
        $detail->loadMissing('peminjaman');
        $s = SystemSetting::current();

        $berjalan = Denda::where('detail_peminjaman_id', $detail->id)
            ->where('is_final', false)
            ->first();

        $jatuhTempo = Carbon::parse($detail->peminjaman->tanggal_kembali);
        $hari = $this->hitungHariTerlambat($jatuhTempo, now(), $s);

        if ($detail->status !== 'Dipinjam' || $detail->qty < 1 || $hari === 0) {
            $berjalan?->delete();
            return null;
        }

        $qty   = (int) $detail->qty;
        $tarif = (int) $s->fine_rate_per_day;

        return Denda::updateOrCreate(
            ['detail_peminjaman_id' => $detail->id, 'is_final' => false],
            [
                'siswa_id'       => $detail->peminjaman->siswa_id,
                'qty_terlambat'  => $qty,
                'hari_terlambat' => $hari,
                'tarif_per_hari' => $tarif,
                'total_denda'    => $hari * $tarif * $qty,
                'status'         => Denda::UNPAID,
            ]
        );
    }
}