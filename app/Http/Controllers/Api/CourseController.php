<?php

namespace App\Http\Controllers\Api;

use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController
{
    // 1. API lấy danh sách khóa học (Dùng chung cho cả Admin và Trang User)
    public function index(Request $request)
    {
        try {
            $query = Course::with(['category', 'seller'])->orderByDesc('created_at');

            // Nếu là trang User gọi (không truyền status) thì mặc định chỉ lấy khóa học 'approved'
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            } elseif (!$request->has('admin')) {
                // Nếu không phải trang admin gửi param admin=1, chỉ hiển thị khóa đã duyệt lên giao diện người dùng
                $query->where('status', 'approved');
            }

            // Tìm kiếm theo từ khóa
            if ($request->filled('search')) {
                $query->where('course_name', 'like', '%' . $request->search . '%');
            }

            $limit = $request->get('limit', 12);
            $courses = $query->paginate($limit);

            return response()->json($courses, 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi máy chủ: ' . $e->getMessage()
            ], 500);
        }
    }

    // 2. Cập nhật thông tin khóa học
    public function update(Request $request, $id)
    {
        try {
            $course = Course::find($id);

            if (!$course) {
                return response()->json(['success' => false, 'message' => 'Khóa học không tồn tại'], 404);
            }

            $course->update($request->only([
                'course_name', 'price', 'sale_price', 'category_id', 'level', 'description', 'status'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật khóa học thành công',
                'data' => $course
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // 3. API Upload & Cập nhật ảnh đại diện khóa học
    public function updateImage(Request $request, $id)
    {
        try {
            $course = Course::find($id);

            if (!$course) {
                return response()->json(['success' => false, 'message' => 'Khóa học không tồn tại'], 404);
            }

            // Nếu người dùng nhập URL ảnh
            if ($request->filled('image_url')) {
                $course->image = $request->image_url;
                $course->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Cập nhật URL ảnh thành công',
                    'data' => $course
                ], 200);
            }

            // Nếu người dùng upload File từ máy
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                
                // Lưu vào thư mục public/uploads/courses
                $file->move(public_path('uploads/courses'), $filename);

                $course->image = $filename;
                $course->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Tải ảnh lên thành công',
                    'data' => $course
                ], 200);
            }

            return response()->json(['success' => false, 'message' => 'Vui lòng chọn file hoặc nhập URL ảnh'], 400);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Lỗi server: ' . $e->getMessage()], 500);
        }
    }
}