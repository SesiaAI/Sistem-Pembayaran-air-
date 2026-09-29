<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MeterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'admin_id',
        'period_month',
        'period_year',
        'initial_meter',
        'final_meter',
        'total_usage',
        'photo_url',
        'reading_date',
        'notes',
    ];

    protected $casts = [
        'period_month' => 'integer',
        'period_year' => 'integer',
        'initial_meter' => 'integer',
        'final_meter' => 'integer',
        'total_usage' => 'integer',
        'reading_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function bill()
    {
        return $this->hasOne(Bill::class);
    }
}
