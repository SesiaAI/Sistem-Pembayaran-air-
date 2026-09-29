<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'order_id',
        'payment_type',
        'gross_amount',
        'transaction_status',
        'snap_token',
        'payment_method_detail',
        'paid_at',
        'midtrans_response',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'midtrans_response' => 'array',
    ];

    public function bill()
    {
        return $this->belongsTo(Bill::class);
    }
}
