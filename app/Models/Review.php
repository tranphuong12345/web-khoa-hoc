<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $table = 'reviews';

    protected $fillable = [
        'student_id',
        'course_id',
        'rating',
        'comment',
        'status',
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
}