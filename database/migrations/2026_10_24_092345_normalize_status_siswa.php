<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Tabel siswa sudah punya kolom "status" sebelum fitur keanggotaan dibuat
 * (nilainya "Aktif"), jadi migration pertama melewatinya. Migration ini
 * menyeragamkan nilai lama ke nilai baru, lalu mengunci kolom jadi ENUM.
 *
 * Tidak ada baris yang dihapus. Hanya nilai kolom status yang diseragamkan.
 * Khusus MySQL/MariaDB (memakai ALTER TABLE ... MODIFY).
 */
class NormalizeStatusSiswa extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('siswa', 'status')) {
            return;
        }

        // 1) Longgarkan tipe kolom supaya nilai lama dan baru bisa ada bersamaan.
        DB::statement("ALTER TABLE siswa MODIFY status VARCHAR(30) NOT NULL DEFAULT 'active'");

        // 2) Petakan nilai lama (tanpa membedakan huruf besar/kecil).
        $peta = [
            'active'      => ['aktif', 'active'],
            'inactive'    => ['nonaktif', 'non aktif', 'non-aktif', 'tidak aktif', 'inactive'],
            'graduated'   => ['lulus', 'alumni', 'graduated'],
            'transferred' => ['pindah', 'mutasi', 'mutasi keluar', 'transferred'],
        ];

        foreach ($peta as $baru => $lama) {
            $in = implode(',', array_fill(0, count($lama), '?'));
            DB::table('siswa')
                ->whereRaw("LOWER(TRIM(status)) IN ($in)", $lama)
                ->update(['status' => $baru]);
        }

        // 3) Nilai yang tidak dikenali dianggap aktif agar tidak ada siswa yang
        //    terkunci tanpa sengaja. Cek hasilnya dengan: SELECT status, COUNT(*) FROM siswa GROUP BY status
        DB::table('siswa')
            ->whereNotIn('status', ['active', 'graduated', 'transferred', 'inactive'])
            ->update(['status' => 'active']);

        // 4) Kunci jadi ENUM sesuai spesifikasi.
        DB::statement("ALTER TABLE siswa MODIFY status ENUM('active','graduated','transferred','inactive') NOT NULL DEFAULT 'active'");
    }

    public function down()
    {
        // Sengaja kosong: data tidak dikembalikan ke format lama.
    }
}