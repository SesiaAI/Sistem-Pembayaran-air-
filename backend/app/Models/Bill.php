<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bill extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_no',
        'customer_id',
        'meter_reading_id',
        'period_month',
        'period_year',
        'rate_per_m3',
        'total_usage',
        'usage_cost',
        'abodemen_cost',
        'total_amount',
        'due_date',
        'status',
    ];

    protected $casts = [
        'rate_per_m3' => 'decimal:2',
        'total_usage' => 'integer',
        'usage_cost' => 'decimal:2',
        'abodemen_cost' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function meterReading()
    {
        return $this->belongsTo(MeterReading::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }
}
