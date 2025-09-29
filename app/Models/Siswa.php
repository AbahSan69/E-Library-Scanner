<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    protected $table = 'siswa';
    protected $fillable = ['nisn', 'nama', 'kelas','jenis_kelamin','tanggal_lahir','alamat','no_hp','status'];

    public function peminjaman()
    {
        return $this->hasMany(Peminjaman::class);
    }
}
