<?php

namespace App\Console\Commands;

use App\Models\DetailPeminjaman;
use App\Services\FineCalculator;
use Illuminate\Console\Command;

class UpdateDenda extends Command
{
    protected $signature = 'denda:update';
    protected $description = 'Perbarui denda berjalan untuk buku yang terlambat dan belum dikembalikan';

    public function handle(FineCalculator $calc): int
    {
        $jumlah = 0;

        DetailPeminjaman::with('peminjaman')
            ->where('status', 'Dipinjam')
            ->whereHas('peminjaman', fn ($q) => $q
                ->where('status', 'Dipinjam')
                ->whereDate('tanggal_kembali', '<', today()))
            ->chunkById(200, function ($rows) use ($calc, &$jumlah) {
                foreach ($rows as $detail) {
                    $calc->hitungBerjalan($detail);
                    $jumlah++;
                }
            });

        $this->info("Denda berjalan diperbarui untuk {$jumlah} item.");

        return self::SUCCESS;
    }
}