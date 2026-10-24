<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusAnggotaToSiswaTable extends Migration
{
    public function up()
    {
        Schema::table('siswa', function (Blueprint $t) {
            if (!Schema::hasColumn('siswa', 'status')) {
                // Semua siswa yang sudah ada otomatis berstatus "active".
                $t->enum('status', ['active', 'graduated', 'transferred', 'inactive'])
                    ->default('active')->index();
            }
            if (!Schema::hasColumn('siswa', 'status_changed_at')) {
                $t->timestamp('status_changed_at')->nullable();
            }
            if (!Schema::hasColumn('siswa', 'status_note')) {
                $t->string('status_note')->nullable();
            }
            if (!Schema::hasColumn('siswa', 'tahun_lulus')) {
                $t->unsignedSmallInteger('tahun_lulus')->nullable();
            }
        });

        // Jejak audit: siapa mengubah apa. Sengaja tanpa foreign key cascade
        // supaya log tidak ikut hilang pada kondisi apa pun.
        if (!Schema::hasTable('siswa_status_log')) {
            Schema::create('siswa_status_log', function (Blueprint $t) {
                $t->bigIncrements('id');
                $t->unsignedBigInteger('siswa_id')->index();
                $t->char('batch_id', 36)->nullable()->index();
                $t->string('tipe', 30)->index(); // naik_kelas, lulus, ubah_status, ubah_kelas, bebas_pustaka
                $t->string('dari_kelas')->nullable();
                $t->string('ke_kelas')->nullable();
                $t->string('dari_status', 20)->nullable();
                $t->string('ke_status', 20)->nullable();
                $t->string('alasan')->nullable();
                $t->string('nomor_surat', 60)->nullable();
                $t->unsignedBigInteger('user_id')->nullable();
                $t->timestamps();
            });
        }
    }

    public function down()
    {
        // Sengaja tidak menghapus apa pun: data riwayat harus tetap utuh.
    }
}