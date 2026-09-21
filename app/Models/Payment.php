<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payments';

    protected $fillable = [
        'order_id',
        'transaction_code',
        'provider',
        'payment_method',
        'amount',
        'currency',
        'status',
        'provider_transaction_id',
        'provider_reference',
        'payment_content',
        'paid_at',
        'expired_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(
            Order::class,
            'order_id'
        );
    }
}