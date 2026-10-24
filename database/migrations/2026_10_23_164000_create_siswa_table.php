<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSiswaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('siswa', function (Blueprint $table) {
            $table->id();
            $table->string('nisn', 12);
            $table->string('nama', 100);
            $table->string('kelas', 100);
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->date('tanggal_lahir')->nullable();
            $table->string('alamat', 100)->nullable();
            $table->string('no_hp', 100)->nullable();
            $table->enum('status', ['Aktif', 'Tidak Aktif']);
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
        Schema::dropIfExists('siswa');
    }
}
