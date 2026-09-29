<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tariff extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate_per_m3',
        'abodemen',
        'effective_date',
        'is_active',
    ];

    protected $casts = [
        'rate_per_m3' => 'decimal:2',
        'abodemen' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_date' => 'date',
    ];

    public static function getActiveTariff()
    {
        return static::where('is_active', true)->latest('effective_date')->first() ?? (object)[
            'rate_per_m3' => 5000.00,
            'abodemen' => 5000.00,
        ];
    }
}
