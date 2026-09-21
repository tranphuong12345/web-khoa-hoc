<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Requests\Admin\UserRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $limit = $request->limit ?? 10;
            $search = $request->search;
            $role = $request->role;
            $status = $request->status;

            $query = User::query();

            // 1. Lọc theo role ngay tại Database (Nếu truyền role=student)
            if ($role) {
                if ($role === 'student') {
                    // Lấy tất cả user là student, learner, user hoặc chưa gán role
                    $query->where(function ($q) {
                        $q->whereIn('role', ['student', 'learner', 'user'])
                            ->orWhereNull('role');
                    });
                } else {
                    $query->where('role', $role);
                }
            }

            // 2. Lọc theo status (nếu có)
            if ($status && $status !== 'all') {
                $query->where('status', $status);
            }

            // 3. Tìm kiếm theo Tên, Email hoặc Số điện thoại (nếu có)
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            }

            // 4. Thực hiện chia trang SAU KHI ĐÃ LỌC
            $users = $query->orderByDesc('user_id')->paginate($limit);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách người dùng thành công',
                'currentPage' => $users->currentPage(),
                'totalPage' => $users->lastPage(),
                'totalItems' => $users->total(),
                'limit' => $users->perPage(),
                'data' => $users->items()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lấy danh sách người dùng thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(UserRequest $request)
    {
        DB::beginTransaction();

        try {

            $user = new User();

            $user->full_name = $request->full_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            // Xử lý upload ảnh Avatar
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                $avatarName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/avatars'), $avatarName);
                $user->avatar = $avatarName;
            } else {
                $user->avatar = $request->avatar; // Dành cho trường hợp truyền chuỗi tên/đường dẫn ảnh
            }
            $user->address = $request->address;
            $user->password = Hash::make(
                $request->password
            );

            $user->role = $request->role ?? 'student';
            $user->status = $request->status ?? 1;

            $user->bank_account_number =
                $request->bank_account_number;

            $user->bank_name =
                $request->bank_name;

            $user->account_holder_name =
                $request->account_holder_name;

            // Nếu Admin đang đăng nhập và tạo user
            if (auth()->check()) {
                $user->created_by = auth()->id();
            }

            // Nếu tự đăng ký thì created_by = NULL

            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Thêm người dùng thành công',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Thêm người dùng thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {

            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin người dùng thành công',
                'data' => $user
            ], 200);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Lấy thông tin người dùng thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UserRequest $request, $id)
    {
        DB::beginTransaction();

        try {

            $user = User::findOrFail($id);

            $user->full_name = $request->full_name;
            $user->email = $request->email;
            $user->phone = $request->phone;
            // Xử lý cập nhật ảnh Avatar mới (nếu có)
            if ($request->hasFile('avatar')) {
                // Xóa ảnh cũ nếu có
                if ($user->avatar && file_exists(public_path('uploads/avatars/' . $user->avatar))) {
                    @unlink(public_path('uploads/avatars/' . $user->avatar));
                }

                $file = $request->file('avatar');
                $avatarName = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/avatars'), $avatarName);
                $user->avatar = $avatarName;
            } elseif ($request->filled('avatar')) {
                $user->avatar = $request->avatar;
            }
            $user->address = $request->address;


            if ($request->filled('password')) {
                $user->password = Hash::make(
                    $request->password
                );
            }

            $user->role = $request->role ?? $user->role;
            $user->status = $request->status ?? $user->status;

            $user->bank_account_number =
                $request->bank_account_number;

            $user->bank_name =
                $request->bank_name;

            $user->account_holder_name =
                $request->account_holder_name;

            // Người cập nhật
            if (auth()->check()) {
                $user->updated_by = auth()->id();
            }

            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật người dùng thành công',
                'data' => $user
            ]);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Cập nhật người dùng thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DB::beginTransaction();

        try {

            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng'
                ], 404);
            }

            $user->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Xóa người dùng thành công'
            ], 200);
        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Xóa người dùng thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bật / Tắt trạng thái hoạt động của người dùng (Khóa / Mở khóa)
     */
    public function toggleStatus($id)
    {
        try {
            $user = User::find($id);

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy người dùng'
                ], 404);
            }

            // Đảo trạng thái: nếu là 1 (Active) thì đổi thành 0 (Inactive) và ngược lại
            $user->status = ($user->status == 1) ? 0 : 1;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => $user->status == 1 ? 'Đã kích hoạt tài khoản' : 'Đã khóa tài khoản',
                'data' => [
                    'user_id' => $user->user_id ?? $user->id,
                    'status' => $user->status
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Cập nhật trạng thái thất bại',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
