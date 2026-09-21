<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $table = 'withdrawal_requests';

    const CREATED_AT = 'requested_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'wallet_id',
        'seller_id',
        'amount',
        'bank_name',
        'bank_account_number',
        'account_holder_name',
        'status',
        'transfer_reference',
        'admin_note',
        'requested_at',
        'processed_at',
        'completed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
        'completed_at' => 'datetime',
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