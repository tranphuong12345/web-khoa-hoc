<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_details';

    protected $fillable = [
        'order_id',
        'course_id',
        'seller_id',
        'course_name',
        'original_price',
        'sale_price',
        'commission_rate',
        'commission_amount',
        'seller_amount',
        'seller_amount_status',
    ];

    protected $casts = [
        'original_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'commission_rate' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'seller_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(
            Order::class,
            'order_id'
        );
    }

    public function course()
    {
        return $this->belongsTo(
            Course::class,
            'course_id'
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