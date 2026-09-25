<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use App\Models\User;
use Illuminate\Http\Request;

class AdminApprovalController
{
    public function getPendingSellers(Request $request)
    {
        $limit = $request->limit ?? 10;

        $sellers = User::where('role', 'seller')
            ->where('status', 2)
            ->orderBy('user_id', 'desc')
            ->paginate($limit);

        return response()->json([
            'success' => true,
            'message' => 'Lấy danh sách giảng viên chờ duyệt thành công.',
            'currentPage' => $sellers->currentPage(),
            'totalPage' => $sellers->lastPage(),
            'totalItems' => $sellers->total(),
            'limit' => $sellers->perPage(),
            'data' => $sellers->items(),
        ]);
    }

    public function getPendingSellerById($id)
    {
        $seller = User::where('user_id', $id)
            ->where('role', 'seller')
            ->where('status', 2)
            ->first();

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy giảng viên đang chờ duyệt.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Lấy thông tin giảng viên thành công.',
            'data' => $seller
        ]);
    }

    public function approveSeller($id)
    {
        $seller = User::where('user_id', $id)
            ->where('role', 'seller')
            ->where('status', 2)
            ->first();

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy giảng viên đang chờ duyệt.'
            ], 404);
        }

        $seller->status = 1;
        $seller->save();

        return response()->json([
            'success' => true,
            'message' => 'Phê duyệt giảng viên thành công.',
            'data' => $seller
        ]);
    }

    public function rejectSeller($id)
    {
        $seller = User::where('user_id', $id)
            ->where('role', 'seller')
            ->where('status', 2)
            ->first();

        if (!$seller) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy giảng viên đang chờ duyệt.'
            ], 404);
        }

        $seller->role = 'student';
        $seller->status = 1;
        $seller->save();

        return response()->json([
            'success' => true,
            'message' => 'Đã từ chối hồ sơ giảng viên.',
            'data' => $seller
        ]);
    }

    public function getPendingCourses(Request $request)
    {
        $limit = $request->limit ?? 10;

        $courses = Course::with([
            'seller',
            'category',
            'sections.lessons',
        ])
            ->where('status', 'pending')
            ->orderByDesc('course_id')
            ->paginate($limit);

        $data = $courses->getCollection()->map(function ($course) {

            return [
                'course_id' => $course->course_id,

                // Thông tin khóa học
                'course_name' => $course->course_name,
                'slug' => $course->slug,
                'description' => $course->description,

                // Ảnh khóa học
                'image' => $course->image
                    ? asset('uploads/courses/' . $course->image)
                    : null,

                // Giá
                'price' => $course->price,
                'sale_price' => $course->sale_price,

                // Thông tin khác
                'level' => $course->level,
                'duration' => $course->duration,
                'status' => $course->status,

                // Danh mục
                'category' => $course->category
                    ? [
                        'category_id' => $course->category->category_id,
                        'name' => $course->category->name,
                    ]
                    : null,

                // Giảng viên
                'seller' => $course->seller
                    ? [
                        'user_id' => $course->seller->user_id,
                        'full_name' => $course->seller->full_name,

                        'avatar' => $course->seller->avatar
                            ? asset(
                                'uploads/avatars/' .
                                    $course->seller->avatar
                            )
                            : null,

                        'specialization' =>
                        $course->seller->specialization,
                    ]
                    : null,

                // Tổng số chương
                'total_sections' =>
                $course->sections->count(),

                // Tổng số bài học
                'total_lessons' =>
                $course->sections->sum(function ($section) {
                    return $section->lessons->count();
                }),
            ];
        });

        return response()->json([
            'success' => true,
            'message' =>
            'Lấy danh sách khóa học chờ duyệt thành công.',

            'currentPage' =>
            $courses->currentPage(),

            'totalPage' =>
            $courses->lastPage(),

            'totalItems' =>
            $courses->total(),

            'limit' =>
            $courses->perPage(),

            'data' =>
            $data->values(),
        ]);
    }

    public function getPendingCourseById($id)
    {
        $course = Course::with([
            'seller',
            'category',
            'sections.lessons',
        ])
            ->where('course_id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' =>
                'Không tìm thấy khóa học đang chờ duyệt.'
            ], 404);
        }

        $data = [
            // Thông tin khóa học
            'course_id' => $course->course_id,
            'course_name' => $course->course_name,
            'slug' => $course->slug,
            'description' => $course->description,

            // Ảnh khóa học
            'image' => $course->image
                ? asset('uploads/courses/' . $course->image)
                : null,

            // Giá
            'price' => $course->price,
            'sale_price' => $course->sale_price,

            // Thông tin khác
            'level' => $course->level,
            'duration' => $course->duration,
            'status' => $course->status,

            // Danh mục
            'category' => $course->category
                ? [
                    'category_id' =>
                    $course->category->category_id,

                    'name' =>
                    $course->category->name,
                ]
                : null,

            // Giảng viên
            'seller' => $course->seller
                ? [
                    'user_id' =>
                    $course->seller->user_id,

                    'full_name' =>
                    $course->seller->full_name,

                    'avatar' => $course->seller->avatar
                        ? asset(
                            'uploads/avatars/' .
                                $course->seller->avatar
                        )
                        : null,

                    'specialization' =>
                    $course->seller->specialization,
                ]
                : null,

            // Tổng số chương
            'total_sections' =>
            $course->sections->count(),

            // Tổng số bài học
            'total_lessons' =>
            $course->sections->sum(function ($section) {
                return $section->lessons->count();
            }),

            // Danh sách chương
            'sections' => $course->sections
                ->sortBy('sort_order')
                ->values()
                ->map(function ($section) {

                    return [
                        'section_id' =>
                        $section->section_id,

                        'section_name' =>
                        $section->section_name,

                        'sort_order' =>
                        $section->sort_order,

                        // Danh sách bài học
                        'lessons' => $section->lessons
                            ->sortBy('sort_order')
                            ->values()
                            ->map(function ($lesson) {

                                return [
                                    'lesson_id' =>
                                    $lesson->lesson_id,

                                    'lesson_name' =>
                                    $lesson->lesson_name,

                                    'content' =>
                                    $lesson->content,

                                    'video_url' =>
                                    $lesson->video_url,

                                    'duration' =>
                                    $lesson->duration,

                                    'sort_order' =>
                                    $lesson->sort_order,

                                    'is_preview' =>
                                    $lesson->is_preview,
                                ];
                            })
                            ->values(),
                    ];
                })
                ->values(),
        ];

        return response()->json([
            'success' => true,
            'message' =>
            'Lấy thông tin khóa học thành công.',
            'data' => $data,
        ]);
    }

    public function approveCourse($id)
    {
        $course = Course::where('course_id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' =>
                'Không tìm thấy khóa học đang chờ duyệt.'
            ], 404);
        }

        $course->status = 'approved';

        if (auth()->check()) {
            $course->approved_by = auth()->id();
        }

        $course->approved_at = now();

        $course->save();

        return response()->json([
            'success' => true,
            'message' =>
            'Phê duyệt khóa học thành công.',
            'data' => $course,
        ]);
    }
    public function rejectCourse($id)
    {
        $course = Course::where('course_id', $id)
            ->where('status', 'pending')
            ->first();

        if (!$course) {
            return response()->json([
                'success' => false,
                'message' =>
                'Không tìm thấy khóa học đang chờ duyệt.'
            ], 404);
        }

        $course->status = 'rejected';

        $course->save();

        return response()->json([
            'success' => true,
            'message' =>
            'Đã từ chối khóa học.',
            'data' => $course,
        ]);
    }
}
