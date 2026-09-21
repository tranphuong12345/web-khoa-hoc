<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('seller_id')
                ->constrained('users')
                ->restrictOnDelete();

            $table->foreignId('category_id')
                ->constrained('categories')
                ->restrictOnDelete();

            $table->string('course_name');
            $table->string('slug')->unique();

            $table->text('description')->nullable();
            $table->string('image')->nullable();

            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('sale_price', 15, 2)->nullable();

            // Phần trăm hoa hồng Admin
            $table->decimal('commission_rate', 5, 2)->default(10);

            $table->enum('level', [
                'beginner',
                'intermediate',
                'advanced'
            ])->default('beginner');

            // Thời lượng khóa học, tính bằng phút
            $table->unsignedInteger('duration')->default(0);

            $table->enum('status', [
                'draft',
                'pending',
                'approved',
                'rejected',
                'suspended'
            ])->default('draft');

            $table->text('rejection_reason')->nullable();

            // Admin duyệt khóa học
            $table->foreignId('approved_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('approved_at')->nullable();

            $table->timestamps();

            $table->index('seller_id');
            $table->index('category_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};