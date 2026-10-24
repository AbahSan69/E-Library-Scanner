<?php

namespace Database\Seeders;

use App\Models\HariLibur;
use Illuminate\Database\Seeder;

class HariLiburSeeder extends Seeder
{
    public function run(): void
    {
        // Hanya hari libur bertanggal tetap. Tambahkan yang bergeser tiap tahun
        // (Idulfitri, Nyepi, dst.) sesuai SKB 3 Menteri lewat halaman pengaturan.
        $libur = [
            '2026-01-01' => 'Tahun Baru Masehi',
            '2026-05-01' => 'Hari Buruh Internasional',
            '2026-06-01' => 'Hari Lahir Pancasila',
            '2026-08-17' => 'Hari Kemerdekaan RI',
            '2026-12-25' => 'Hari Raya Natal',
        ];

        foreach ($libur as $tanggal => $ket) {
            HariLibur::updateOrCreate(['tanggal' => $tanggal], ['keterangan' => $ket]);
        }
    }
}