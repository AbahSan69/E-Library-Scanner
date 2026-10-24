<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBukuTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buku', function (Blueprint $table) {
            $table->id();
            $table->string('no_barcode', 100)->nullable();
            $table->string('isbn', 100)->nullable();
            $table->string('judul', 100);
            $table->string('pengarang', 100)->nullable();
            $table->string('penerbit', 100)->nullable();
            $table->year('tahun_terbit')->nullable();
            $table->string('lokasi_rak', 100)->nullable();
            $table->integer('stok')->nullable();
            $table->enum('status', ['Tersedia', 'Dipinjam'])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('buku');
    }
}
