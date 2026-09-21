<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';

    protected $fillable = [
        'buyer_id',
        'total_amount',
        'currency',
        'status',
        'note',
        'paid_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function buyer()
    {
        return $this->belongsTo(
            User::class,
            'buyer_id'
        );
    }

    public function details()
    {
        return $this->hasMany(
            OrderDetail::class,
            'order_id'
        );
    }

    public function payments()
    {
        return $this->hasMany(
            Payment::class,
            'order_id'
        );
    }

    public function enrollments()
    {
        return $this->hasMany(
            Enrollment::class,
            'order_id'
        );
    }
}