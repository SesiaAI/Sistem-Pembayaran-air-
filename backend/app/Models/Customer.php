<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_no',
        'meter_number',
        'address',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function meterReadings()
    {
        return $this->hasMany(MeterReading::class);
    }

    public function bills()
    {
        return $this->hasMany(Bill::class);
    }

    public function latestMeterReading()
    {
        return $this->hasOne(MeterReading::class)->latestOfMany();
    }
}
