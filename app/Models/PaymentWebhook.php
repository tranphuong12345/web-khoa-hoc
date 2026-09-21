<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentWebhook extends Model
{
    protected $table = 'payment_webhooks';

    protected $fillable = [
        'provider',
        'event_type',
        'transaction_id',
        'reference_code',
        'amount',
        'payload',
        'signature',
        'status',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payload' => 'array',
        'processed_at' => 'datetime',
    ];
}