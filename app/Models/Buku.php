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

        parent::boot();

        static::saving(function ($model) {
            // Cek duplikat kombinasi judul + pengarang
            $exists = self::where('judul', $model->judul)
                ->where('pengarang', $model->pengarang)
                ->when($model->exists, fn($q) => $q->where('id', '!=', $model->id))
                ->exists();

            if ($exists) {
                throw new \Exception("Buku dengan judul '{$model->judul}' dan pengarang '{$model->pengarang}' sudah ada.");
            }
        });
    }
}
