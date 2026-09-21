<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $table = 'enrollments';

    protected $fillable = [
        'student_id',
        'course_id',
        'order_id',
        'enrolled_at',
        'completed_at',
        'status',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(
            User::class,
            'student_id'
        );
    }

    public function course()
    {
        return $this->belongsTo(
            Course::class,
            'course_id'
        );
    }

    public function order()
    {
        return $this->belongsTo(
            Order::class,
            'order_id'
        );
    }

    public function lessonProgress()
    {
        return $this->hasMany(
            LessonProgress::class,
            'enrollment_id'
        );
    }
}