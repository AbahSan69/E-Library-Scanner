<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDendaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('denda', function (Blueprint $t) {
            $t->id();
            $t->foreignId('detail_peminjaman_id')
              ->constrained('detail_peminjaman')->cascadeOnDelete();
            $t->foreignId('siswa_id')->constrained('siswa');   // sesuaikan nama tabel siswa
            $t->boolean('is_final')->default(false);           // false = berjalan, true = sudah dikembalikan
            $t->dateTime('tanggal_dikembalikan')->nullable();
            $t->unsignedInteger('qty_terlambat');
            $t->unsignedInteger('hari_terlambat')->default(0);
            $t->unsignedInteger('tarif_per_hari');
            $t->decimal('total_denda', 12, 2)->default(0);
            $t->string('status', 20)->default('unpaid');       // unpaid | paid | waived
            $t->dateTime('dibayar_pada')->nullable();
            $t->foreignId('diterima_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->text('alasan_dispensasi')->nullable();
            $t->foreignId('dispensasi_oleh')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->index(['siswa_id', 'status']);
            $t->index(['detail_peminjaman_id', 'is_final']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('denda');
    }
}
