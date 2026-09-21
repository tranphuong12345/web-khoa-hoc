<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LessonProgress extends Model
{
    protected $table = 'lesson_progress';

    protected $fillable = [
        'enrollment_id',
        'lesson_id',
        'progress_percent',
        'is_completed',
        'video_position',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'progress_percent' => 'decimal:2',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(
            Enrollment::class,
            'enrollment_id'
        );
    }

    public function lesson()
    {
        return $this->belongsTo(
            Lesson::class,
            'lesson_id'
        );
    }
}