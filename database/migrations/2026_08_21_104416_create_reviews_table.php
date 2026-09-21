<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->restrictOnDelete();

            $table->unsignedTinyInteger('rating');

            $table->text('comment')->nullable();

            $table->tinyInteger('status')->default(1);

            $table->timestamps();

            // Mỗi Student chỉ review 1 lần / khóa học
            $table->unique([
                'student_id',
                'course_id'
            ]);

            $table->index('student_id');
            $table->index('course_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};