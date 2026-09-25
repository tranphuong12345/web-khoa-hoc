<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Admin\CourseRequest;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

    public function store(CourseRequest $request)
    {
        DB::beginTransaction();

        try {
            // 1. Lưu thông tin vào bảng 1: courses
            $course = new Course();
            $slug = Str::of($request->course_name)->slug('-');

            $course->category_id = $request->category_id;
            $course->seller_id = $request->user()->user_id;
            $course->course_name = $request->course_name;
            $course->slug        = $slug;
            $course->description = $request->description;
            $course->price       = $request->price;
            $course->sale_price  = $request->sale_price ?? 0;
            $course->level       = $request->level ?? 'beginner';
            $course->duration    = $request->duration ?? 0;
            $course->status = 'pending';

            // Xử lý upload ảnh đại diện (giống cách xử lý thumbnail sản phẩm của bạn)
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                // Cần lưu tạm course trước hoặc dùng thời gian để đặt tên nếu chưa có ID, 
                // tuy nhiên sau khi new Course() thì Laravel chưa có ID ngay nếu chưa save.
                // Giải pháp: Save trước để lấy ID, hoặc dùng thời gian. 
                // Ở đây mình save luôn để lấy $course->course_id:
            }

            $course->save(); // Lưu trước để có $course->course_id

            // Xử lý cập nhật lại đường dẫn ảnh có gắn ID (nếu muốn chuẩn format giống đoạn code sản phẩm của bạn)
            if ($request->hasFile('image')) {
                $imgFile = $request->file('image');

                $imageName = $course->course_id
                    . '_course_'
                    . time()
                    . '.'
                    . $imgFile->getClientOriginalExtension();

                $imgFile->move(
                    public_path('uploads/courses'),
                    $imageName
                );

                $course->image = $imageName;
                $course->save();
            }

            // 2. Giải mã mảng sections từ JSON gửi lên để lưu bảng 2 và bảng 3
            $sections = json_decode($request->sections, true);

            if (!empty($sections) && is_array($sections)) {
                foreach ($sections as $secIndex => $secData) {

                    // Lưu vào bảng 2: course_sections (Chương học)
                    $section = new Section();
                    $section->course_id    = $course->course_id;
                    $section->section_name = $secData['section_name'] ?? ('Chương ' . ($secIndex + 1));
                    $section->sort_order   = $secData['sort_order'] ?? ($secIndex + 1);
                    $section->save();

                    // Kiểm tra và lưu bài học trong chương
                    if (!empty($secData['lessons']) && is_array($secData['lessons'])) {
                        foreach ($secData['lessons'] as $lesIndex => $lesData) {

                            // Lưu vào bảng 3: course_lessons (Bài học)
                            $lesson = new Lesson();
                            $lesson->section_id  = $section->section_id;
                            $lesson->lesson_name = $lesData['lesson_name'] ?? ('Bài ' . ($lesIndex + 1));
                            $lesson->content     = $lesData['content'] ?? null;
                            $lesson->video_url   = $lesData['video_url'] ?? null;
                            $lesson->duration    = $lesData['duration'] ?? 0;
                            $lesson->sort_order  = $lesData['sort_order'] ?? ($lesIndex + 1);
                            $lesson->is_preview  = $lesData['is_preview'] ?? 0;
                            $lesson->save();
                        }
                    }
                }
            }

            // Nếu mọi thứ thành công tuyệt đối -> Commit
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Tạo khóa học thành công và đang chờ Admin xét duyệt!',
                'data'    => $course
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Lỗi tạo khóa học',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
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
                'course_name',
                'price',
                'sale_price',
                'category_id',
                'level',
                'description',
                'status'
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
