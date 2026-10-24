<?php

namespace App\Models\Concerns;

use App\Models\Siswa;
use Illuminate\Validation\ValidationException;

/**
 * Pasang di model Peminjaman:  use \App\Models\Concerns\BlokirAnggotaNonaktif;
 *
 * Pengaman terakhir di level data: transaksi peminjaman BARU untuk siswa yang
 * statusnya bukan "active" selalu ditolak, apa pun controller yang memanggilnya.
 * Pengembalian buku dan pembayaran denda tidak terpengaruh.
 *
 * Anotasi di bawah hanya untuk editor (Intelephense) agar tidak menandai
 * static::creating() sebagai "Undefined method". Runtime tidak terpengaruh.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 * @method static void creating(\Closure|string $callback)
 */
trait BlokirAnggotaNonaktif
{
    protected static function bootBlokirAnggotaNonaktif()
    {
        static::creating(function ($model) {
            $siswa = Siswa::find($model->siswa_id);

            if ($siswa && !$siswa->bolehMeminjam()) {
                throw ValidationException::withMessages([
                    'siswa_id' => 'Anggota sudah tidak aktif/lulus',
                ]);
            }
        });
    }
}