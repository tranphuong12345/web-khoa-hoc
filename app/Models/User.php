<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens,Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'user_id';

   protected $fillable = [
        'full_name',
        'email',
        'phone',
        'avatar',
        'address',
        'created_by',
        'updated_by',
        'password',
        'role',
        'status',
        'bank_account_number',
        'bank_name',
        'account_holder_name',
        'qualification',
        'qualification_image',
        'specialization',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function createdBy()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function updatedBy()
    {
        return $this->belongsTo(
            User::class,
            'updated_by'
        );
    }

    public function sellers()
    {
        return $this->hasMany(
            Course::class,
            'seller_id'
        );
    }

    public function approvedCourses()
    {
        return $this->hasMany(
            Course::class,
            'approved_by'
        );
    }

    public function orders()
    {
        return $this->hasMany(
            Order::class,
            'buyer_id'
        );
    }

    public function orderDetails()
    {
        return $this->hasMany(
            OrderDetail::class,
            'seller_id'
        );
    }

    public function wallet()
    {
        return $this->hasOne(
            SellerWallet::class,
            'seller_id'
        );
    }

    public function walletTransactions()
    {
        return $this->hasMany(
            WalletTransaction::class,
            'seller_id'
        );
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(
            WithdrawalRequest::class,
            'seller_id'
        );
    }

    public function enrollments()
    {
        return $this->hasMany(
            Enrollment::class,
            'student_id'
        );
    }

    public function reviews()
    {
        return $this->hasMany(
            Review::class,
            'student_id'
        );
    }

    public function notifications()
    {
        return $this->hasMany(
            Notification::class,
            'user_id'
        );
    }
}