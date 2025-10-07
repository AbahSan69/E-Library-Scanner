<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Buku extends Model
{
    protected $table = 'buku';
    protected $fillable = ['no_barcode', 'isbn', 'judul','pengarang','penerbit','tahun_terbit','lokasi_rak','type','stok','status'];

    public function peminjaman()
    {
        return $this->hasMany(DetailPeminjaman::class);
    }

    protected static function booted()
    {
        static::created(function ($book) {
            // Generate barcode pakai ID (terjamin unik)
            $book->no_barcode = generateEAN13($book->id);
            $book->save();
        });
    }
}
