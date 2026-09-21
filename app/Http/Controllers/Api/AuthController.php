<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\RegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController
{
    /**
     * Xử lý Đăng ký (Có hỗ trợ Upload Avatar)
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $avatarName = null;
            $qualificationImageName = null;

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');

                $avatarName = time() . '_' . $file->getClientOriginalName();

                $file->move(
                    public_path('uploads/avatars'),
                    $avatarName
                );
            }

            if ($request->hasFile('qualification_image')) {
                $file = $request->file('qualification_image');

                $qualificationImageName = time() . '_' . $file->getClientOriginalName();

                $file->move(
                    public_path('uploads/qualifications'),
                    $qualificationImageName
                );
            }

            $user = User::create([
                'full_name' => $request->full_name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'avatar' => $avatarName,
                'status' => $request->role === 'seller' ? 2 : 1,
                'phone' => $request->phone ?? '',
                'address' => $request->address ?? 'Chưa cập nhật',

                'bank_account_number' => $request->bank_account_number ?? null,
                'bank_name' => $request->bank_name ?? null,
                'account_holder_name' => $request->account_holder_name ?? null,

                'qualification' => $request->role === 'seller'
                    ? $request->qualification
                    : null,

                'qualification_image' => $request->role === 'seller'
                    ? $qualificationImageName
                    : null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đăng ký tài khoản thành công!',
                'data' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Xử lý Đăng nhập
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
return response()->json([
                'success' => false,
                'message' => 'Email hoặc mật khẩu không chính xác.',
            ], 401);
        }

        if ($user->status != 1) {
            return response()->json([
                'success' => false,
                'message' => 'Tài khoản của bạn đã bị khóa.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'Đăng nhập thành công!',
            'data' => $user
        ], 200);
    }

    /**
     * Upload / Thay đổi Avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|exists:users,user_id', // Đổi 'id' thành tên cột khóa chính của bạn (ví dụ: user_id)
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user = User::where('user_id', $request->id)->first(); // Đổi 'user_id' đúng với tên cột trong DB

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = time() . '_' . $file->getClientOriginalName();

            // Tự tạo thư mục nếu chưa tồn tại
            $destinationPath = public_path('uploads/avatars');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            $file->move($destinationPath, $filename);

            if ($user->avatar && file_exists(public_path('uploads/avatars/' . $user->avatar))) {
                @unlink(public_path('uploads/avatars/' . $user->avatar));
            }

            $user->avatar = $filename;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật avatar thành công!',
                'data' => $user
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không tìm thấy file ảnh.'
        ], 400);
    }

    /**
     * Cập nhật thông tin cá nhân
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|exists:users,user_id', // Đổi 'id' thành tên cột khóa chính của bạn
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
        ]);
        $user = User::where('user_id', $request->id)->first();
        $user->full_name = $request->full_name;
        $user->phone = $request->phone;
        $user->address = $request->address;

        if ($user->role === 'seller') {
            $user->bank_account_number = $request->bank_account_number;
            $user->bank_name = $request->bank_name;
            $user->account_holder_name = $request->account_holder_name;
        }
$user->save();

        return response()->json([
            'success' => true,
            'message' => 'Cập nhật thông tin thành công!',
            'data' => $user
        ]);
    }

    /**
     * Đổi mật khẩu
     */
    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'id' => 'required|exists:users,user_id', // Đổi 'id' thành tên cột khóa chính của bạn
            'current_password' => 'required',
            'new_password' => 'required|min:6',
        ]);

        $user = User::where('user_id', $request->id)->first();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Mật khẩu hiện tại không đúng.'
            ], 400);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Đổi mật khẩu thành công!'
        ]);
    }

    public function getPendingSellers(Request $request): JsonResponse
    {
        try {
            $limit = $request->input('limit', 10);

            $users = User::where('status', 2)
                ->whereIn('role', ['student', 'seller'])
                ->orderBy('created_at', 'desc')
                ->paginate($limit);

            return response()->json([
                'success' => true,
                'message' => 'Lấy danh sách hồ sơ giảng viên chờ duyệt thành công',
                'currentPage' => $users->currentPage(),
                'totalPage' => $users->lastPage(),
                'totalItems' => $users->total(),
                'limit' => $users->perPage(),
                'data' => $users->items(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getPendingSellerById($id): JsonResponse
    {
        try {
            $user = User::where('user_id', $id)
                ->where('status', 2)
                ->whereIn('role', ['student', 'seller'])
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy hồ sơ đang chờ duyệt',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Lấy thông tin hồ sơ thành công',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
            ], 500);
        }
    }
public function approveSeller($id): JsonResponse
    {
        try {
            $user = User::where('user_id', $id)
                ->where('status', 2)
                ->whereIn('role', ['student', 'seller'])
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy hồ sơ đang chờ duyệt',
                ], 404);
            }

            $user->update([
                'role' => 'seller',
                'status' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Phê duyệt giảng viên thành công',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function rejectSeller($id): JsonResponse
    {
        try {
            $user = User::where('user_id', $id)
                ->where('status', 2)
                ->whereIn('role', ['student', 'seller'])
                ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không tìm thấy hồ sơ đang chờ duyệt',
                ], 404);
            }

            $user->update([
                'role' => 'student',
                'status' => 1,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Đã từ chối hồ sơ giảng viên',
                'data' => $user,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi hệ thống: ' . $e->getMessage(),
            ], 500);
        }
    }
}