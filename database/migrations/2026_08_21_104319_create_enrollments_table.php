<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->restrictOnDelete();

            $table->foreignId('order_id')
                ->constrained('orders')
                ->restrictOnDelete();

            $table->dateTime('enrolled_at')->useCurrent();

            $table->dateTime('completed_at')->nullable();

            $table->enum('status', [
                'active',
                'completed',
                'cancelled'
            ])->default('active');

            $table->timestamps();

            // Một Student không mua một khóa học 2 lần
            $table->unique([
                'student_id',
                'course_id'
            ]);

            $table->index('student_id');
            $table->index('course_id');
            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};