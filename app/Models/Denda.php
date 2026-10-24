<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Denda extends Model
{
    public const UNPAID = 'unpaid';
    public const PAID   = 'paid';
    public const WAIVED = 'waived';

    protected $table = 'denda';

    protected $fillable = [
        'detail_peminjaman_id', 'siswa_id', 'is_final', 'tanggal_dikembalikan',
        'qty_terlambat', 'hari_terlambat', 'tarif_per_hari', 'total_denda',
        'status', 'dibayar_pada', 'diterima_oleh', 'alasan_dispensasi', 'dispensasi_oleh',
    ];

    protected $casts = [
        'is_final'             => 'boolean',
        'tanggal_dikembalikan' => 'datetime',
        'dibayar_pada'         => 'datetime',
        'total_denda'          => 'decimal:2',
    ];

    public function detail()   { return $this->belongsTo(DetailPeminjaman::class, 'detail_peminjaman_id'); }
    public function siswa()    { return $this->belongsTo(Siswa::class); }
    public function penerima() { return $this->belongsTo(User::class, 'diterima_oleh'); }
    public function pemberiDispensasi() { return $this->belongsTo(User::class, 'dispensasi_oleh'); }

    public function scopeUnpaid($q)
    {
        return $q->where('status', self::UNPAID);
    }

    /** Denda yang masih bisa dibayar atau didispensasi (bukan yang berjalan). */
    public function bisaDiproses(): bool
    {
        return $this->is_final && $this->status === self::UNPAID;
    }

    public static function punyaTunggakan(int $siswaId): bool
    {
        return static::where('siswa_id', $siswaId)->unpaid()->exists();
    }

    public static function totalTunggakan(int $siswaId): float
    {
        return (float) static::where('siswa_id', $siswaId)->unpaid()->sum('total_denda');
    }
}