<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'categories';

    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category_name',
        'slug',
        'description',
        'image',
        'status',
        'created_at',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class, 'category_id', 'category_id');
    }
}
