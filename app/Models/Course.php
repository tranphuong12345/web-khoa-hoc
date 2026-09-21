<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $primaryKey = 'course_id'; // Khai báo khóa chính nếu không phải 'id'

    protected $fillable = [
        'course_name',
        'category_id',
        'seller_id',
        'price',
        'sale_price',
        'commission_rate',
        'level',
        'duration',
        'description',
        'status',
        'rejection_reason'
    ];

    // Mối quan hệ với Danh mục (Categories)
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    // Mối quan hệ với Người bán (Users/Sellers)
    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id', 'user_id'); // Chỉnh lại 'user_id' theo đúng tên cột khóa chính bảng users
    }

    // Mối quan hệ với Bài học
    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'course_id', 'course_id');
    }
}