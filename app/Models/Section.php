<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
   use HasFactory;

    protected $table = 'sections'; // Tên bảng thực tế trong Database
    protected $primaryKey = 'section_id'; // Khóa chính của bảng

    protected $fillable = [
        'course_id',
        'section_name',
        'sort_order',
    ];

    // Quan hệ thuộc về 1 Khóa học
    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    // Quan hệ 1 Chương có nhiều Bài học (Lessons)
    public function lessons()
    {
        return $this->hasMany(Lesson::class, 'section_id', 'section_id');
    }
}
