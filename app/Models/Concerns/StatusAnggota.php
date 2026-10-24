<?php

namespace App\Models\Concerns;

/**
 * Pasang di model Siswa:  use \App\Models\Concerns\StatusAnggota;
 *
 * - Menyediakan daftar status, scope ->aktif(), dan bolehMeminjam().
 * - Memblokir penghapusan lewat model (delete()/forceDelete()) supaya data
 *   anggota tetap utuh. Pakai perubahan status, bukan hapus.
 *   Catatan: Siswa::where(...)->delete() (query langsung) tidak lewat event model.
 *
 * Trait ini dipakai oleh model Eloquent, jadi method model tersedia saat runtime.
 * Anotasi di bawah hanya untuk editor (Intelephense) agar tidak menandai
 * static::deleting() sebagai "Undefined method".
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 * @property array $attributes
 * @method static void deleting(\Closure|string $callback)
 */
trait StatusAnggota
{
    public static function statusList(): array
    {
        return [
            'active'      => 'Aktif',
            'graduated'   => 'Lulus / Alumni',
            'transferred' => 'Mutasi Keluar',
            'inactive'    => 'Nonaktif',
        ];
    }

    /**
     * Menyeragamkan nilai status lama/bebas ("Aktif", "Tidak Aktif", "Lulus", ...)
     * ke nilai ENUM. Nilai yang tidak dikenali dianggap "active".
     */
    public static function normalisasiStatus($nilai): string
    {
        $v = strtolower(trim((string) $nilai));

        $peta = [
            'active'        => 'active',
            'aktif'         => 'active',
            'inactive'      => 'inactive',
            'nonaktif'      => 'inactive',
            'non aktif'     => 'inactive',
            'non-aktif'     => 'inactive',
            'tidak aktif'   => 'inactive',
            'graduated'     => 'graduated',
            'lulus'         => 'graduated',
            'alumni'        => 'graduated',
            'transferred'   => 'transferred',
            'pindah'        => 'transferred',
            'mutasi'        => 'transferred',
            'mutasi keluar' => 'transferred',
        ];

        return isset($peta[$v]) ? $peta[$v] : 'active';
    }

    /**
     * Berlaku untuk semua simpan lewat Eloquent (form siswa, import Excel, dll),
     * sehingga kode lama yang masih mengirim "Aktif" tidak memicu error ENUM.
     */
    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = static::normalisasiStatus($value);
    }

    protected static function bootStatusAnggota()
    {
        static::deleting(function () {
            throw new \RuntimeException(
                'Data anggota tidak boleh dihapus permanen. Ubah status keanggotaannya (lulus, mutasi, atau nonaktif).'
            );
        });
    }

    public function scopeAktif($query)
    {
        return $query->where($query->getModel()->getTable() . '.status', 'active');
    }

    public function bolehMeminjam(): bool
    {
        $s = data_get($this, 'status');

        return $s === null || $s === 'active';
    }

    public function getStatusLabelAttribute()
    {
        $list = static::statusList();
        $s = data_get($this, 'status') ?: 'active';

        return isset($list[$s]) ? $list[$s] : $s;
    }
}