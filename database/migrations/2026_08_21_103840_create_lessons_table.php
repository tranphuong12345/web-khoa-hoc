<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')
                ->constrained('courses')
                ->cascadeOnDelete();

            $table->string('lesson_name');

            $table->longText('content')->nullable();

            $table->string('video_url', 500)->nullable();

            $table->unsignedInteger('duration')->default(0);

            $table->unsignedInteger('sort_order')->default(1);

            $table->tinyInteger('is_preview')->default(0);

            $table->tinyInteger('status')->default(1);

            $table->timestamps();

            $table->index('course_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};