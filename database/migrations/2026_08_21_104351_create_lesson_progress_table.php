<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->id();

            $table->foreignId('enrollment_id')
                ->constrained('enrollments')
                ->cascadeOnDelete();

            $table->foreignId('lesson_id')
                ->constrained('lessons')
                ->cascadeOnDelete();

            $table->decimal('progress_percent', 5, 2)
                ->default(0);

            $table->tinyInteger('is_completed')
                ->default(0);

            // Vị trí video đang xem, tính bằng giây
            $table->unsignedInteger('video_position')
                ->default(0);

            $table->dateTime('started_at')->nullable();

            $table->dateTime('completed_at')->nullable();

            $table->timestamps();

            // Một enrollment chỉ có 1 record cho 1 lesson
            $table->unique([
                'enrollment_id',
                'lesson_id'
            ]);

            $table->index('enrollment_id');
            $table->index('lesson_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};