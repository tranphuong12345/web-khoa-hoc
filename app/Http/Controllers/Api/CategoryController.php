<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Category;
use App\Http\Requests\Admin\CategoryRequest;
use Illuminate\Support\Facades\DB;

class CategoryController
{
    /**
     * Display a listing of the resource with pagination.
     */
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = Category::orderByDesc('category_id');

            // 1. Nếu Frontend cần lọc danh mục đang hoạt động (ví dụ status = 1)
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // 2. Kiểm tra xem Frontend có yêu cầu phân trang hay lấy tất cả (cho Navbar Dropdown)
            if ($request->boolean('all') || $request->all === 'true') {
                $categories = $query->get();

                return response()->json([
                    'success' => true,
                    'message' => 'Lấy toàn bộ danh mục thành công',
                    'data' => $categories
                ]);
            }

            // 3. Phân trang mặc định cho trang Quản lý Admin
            $limit = $request->limit ?? 10;
            $categories = $query->paginate($limit);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách danh mục thành công',
                'currentPage' => $categories->currentPage(),
                'totalPage' => $categories->lastPage(),
                'totalItems' => $categories->total(),
                'limit' => $categories->perPage(),
                'data' => $categories->items()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lấy danh sách danh mục thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        DB::beginTransaction();

        try {
            $category = new Category();

            $category->category_name = $request->category_name;
            $category->slug = $request->slug;
            $category->description = $request->description;
            $category->image = $request->image;
            $category->status = $request->status ?? 1;

            $category->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thêm danh mục thành công',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Thêm danh mục thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        // Tìm danh mục theo slug
        $category = Category::where('slug', $slug)->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục'
            ], 404);
        }

        // Lấy danh sách khóa học
        $courses = $category->courses()->with(['seller'])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'category' => $category,
                'courses' => $courses
            ]
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, string $id)
    {
        $category = Category::find($id);

        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy danh mục'
            ], 404);
        }

        DB::beginTransaction();

        try {
            if ($request->has('category_name')) {
                $category->category_name = $request->category_name;
            }

            if ($request->has('slug')) {
                $category->slug = $request->slug;
            }

            if ($request->has('description')) {
                $category->description = $request->description;
            }

            if ($request->has('image')) {
                $category->image = $request->image;
            }

            if ($request->has('status')) {
                $category->status = $request->status;
            }

            $category->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật danh mục thành công',
                'data' => $category
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Cập nhật danh mục thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $category = Category::find($id);

            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy danh mục'
                ], 404);
            }

            // Check if there are related courses to prevent breaking foreign keys
            if ($category->courses()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa danh mục này vì đã có khóa học thuộc danh mục'
                ], 400);
            }

            $category->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa danh mục thành công'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Xóa danh mục thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
