<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';
    protected $guarded = [];

    protected $attributes = [
        'fine_rate_per_day' => 1000,
        'grace_period_days' => 0,
        'exclude_saturday'  => false,
        'exclude_sunday'    => false,
        'exclude_holidays'  => false,
    ];

    protected $casts = [
        'fine_rate_per_day' => 'integer',
        'grace_period_days' => 'integer',
        'exclude_saturday'  => 'boolean',
        'exclude_sunday'    => 'boolean',
        'exclude_holidays'  => 'boolean',
    ];

    /** Ambil baris pengaturan; kalau belum ada, kembalikan default (belum disimpan). */
    public static function current()
    {
        $row = static::query()->first();

        return $row ? $row : new static();
    }
}