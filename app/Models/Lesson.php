<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lesson extends Model
{
    protected $table = 'lessons';

    protected $fillable = [
        'course_id',
        'lesson_name',
        'content',
        'video_url',
        'duration',
        'sort_order',
        'is_preview',
        'status',
    ];

    public function course()
    {
        return $this->belongsTo(
            Course::class,
            'course_id'
        );
    }

    public function progress()
    {
        return $this->hasMany(
            LessonProgress::class,
            'lesson_id'
        );
    }
}