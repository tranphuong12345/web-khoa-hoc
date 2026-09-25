<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    use HasFactory;

    protected $table = 'lessons'; // Hoặc 'course_lessons' tùy tên bảng thực tế của bạn
    protected $primaryKey = 'lesson_id';

    protected $fillable = [
        'section_id',
        'lesson_name',
        'content',
        'video_url',
        'duration',
        'sort_order',
        'is_preview',
    ];

    // Quan hệ thuộc về 1 Chương
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id', 'section_id');
    }

    public function progress()
    {
        return $this->hasMany(
            LessonProgress::class,
            'lesson_id'
        );
    }
}