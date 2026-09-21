<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    protected $table = 'wallet_transactions';

    protected $fillable = [
        'wallet_id',
        'seller_id',
        'transaction_type',
        'reference_type',
        'reference_id',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_before' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function wallet()
    {
        return $this->belongsTo(
            SellerWallet::class,
            'wallet_id'
        );
    }

    public function seller()
    {
        return $this->belongsTo(
            User::class,
            'seller_id'
        );
    }
}