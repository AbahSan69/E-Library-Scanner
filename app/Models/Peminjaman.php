<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Peminjaman extends Model
{
    protected $table = 'peminjaman';
    protected $fillable = ['siswa_id', 'buku_id', 'tanggal_pinjam','tanggal_kembali','status'];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function buku()
    {
        return $this->belongsTo(Buku::class);
    }

    protected $appends = ['status_aktif'];

    public function getStatusAktifAttribute()
    {
        $tanggalKembali = Carbon::parse($this->tanggal_kembali);

        if ($this->status === 'Kembali') {
            return 'Kembali';
        }

        if (Carbon::now()->gt($tanggalKembali) && $this->status === 'Dipinjam') {
            return 'Terlambat';
        }

        return $this->status;
    }
}
