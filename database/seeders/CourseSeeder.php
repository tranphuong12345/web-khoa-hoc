<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Category;
use App\Models\Course;
use Illuminate\Support\Facades\Schema;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Lấy user đầu tiên vừa seed (hoặc tự tạo nếu chưa có)
        $user = User::first();
        
        // Xác định tên khóa chính của User (id hoặc user_id)
        $sellerId = $user->user_id ?? $user->id;

        // 2. Lấy hoặc tạo Danh mục mẫu
        $catIT = Category::firstOrCreate(
            ['slug' => 'lap-trinh-web'],
            ['category_name' => 'Lập trình Web', 'description' => 'Khóa học lập trình web']
        );

        $catDesign = Category::firstOrCreate(
            ['slug' => 'thiet-ke-do-hoa'],
            ['category_name' => 'Thiết kế Đồ họa', 'description' => 'Khóa học thiết kế']
        );

        $categoryIdIT = $catIT->category_id ?? $catIT->id;
        $categoryIdDesign = $catDesign->category_id ?? $catDesign->id;

        // 3. Chèn các khóa học mẫu
        $courses = [
            [
                'seller_id' => $sellerId,
                'category_id' => $categoryIdIT,
                'course_name' => 'Lập trình Next.js 14 & Laravel API từ A-Z',
                'slug' => 'lap-trinh-nextjs-14-laravel-api',
                'description' => 'Xây dựng website thực tế với Next.js và Laravel RESTful API.',
                'image' => 'images/banners/ha-lan-1718090513.jpg',
                'price' => 1200000,
                'sale_price' => 890000,
                'commission_rate' => 10,
                'level' => 'intermediate',
                'duration' => 360,
                'status' => 'approved',
                'approved_at' => now(),
            ],
            [
                'seller_id' => $sellerId,
                'category_id' => $categoryIdIT,
                'course_name' => 'Master React.js và Tailwind CSS cơ bản',
                'slug' => 'master-reactjs-tailwind-css',
                'description' => 'Học làm giao diện web hiện đại responsive với Tailwind CSS.',
                'image' => 'images/banners/ha-lan-1718090513.jpg',
                'price' => 500000,
                'sale_price' => 0,
                'commission_rate' => 10,
                'level' => 'beginner',
                'duration' => 180,
                'status' => 'approved',
                'approved_at' => now(),
            ],
            [
                'seller_id' => $sellerId,
                'category_id' => $categoryIdDesign,
                'course_name' => 'Thiết kế UI/UX Chuyên nghiệp với Figma',
                'slug' => 'thiet-ke-ui-ux-voi-figma',
                'description' => 'Thiết kế ứng dụng web/mobile chuẩn quy trình UI/UX.',
                'image' => 'images/banners/ha-lan-1718090513.jpg',
                'price' => 950000,
                'sale_price' => 650000,
                'commission_rate' => 15,
                'level' => 'advanced',
                'duration' => 420,
                'status' => 'approved',
                'approved_at' => now(),
            ],
        ];

        foreach ($courses as $courseData) {
            Course::updateOrCreate(
                ['slug' => $courseData['slug']],
                $courseData
            );
        }
    }
}