<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SellerWallet extends Model
{
    protected $table = 'seller_wallets';

    protected $fillable = [
        'seller_id',
        'available_balance',
        'pending_balance',
        'total_earned',
        'total_withdrawn',
        'currency',
        'status',
    ];

    protected $casts = [
        'available_balance' => 'decimal:2',
        'pending_balance' => 'decimal:2',
        'total_earned' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
    ];

    public function seller()
    {
        return $this->belongsTo(
            User::class,
            'seller_id'
        );
    }

    public function transactions()
    {
        return $this->hasMany(
            WalletTransaction::class,
            'wallet_id'
        );
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(
            WithdrawalRequest::class,
            'wallet_id'
        );
    }
}