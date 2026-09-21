<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules()
    {
        // Lấy ID người dùng từ route (dùng cho hàm update)
        $userId = $this->route('id') ?? $this->route('user');

        // Bắt đầu kiểm tra điều kiện
        $rules = [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $userId,
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable', // Chấp nhận cả file upload lẫn chuỗi URL/tên file
            'address' => 'nullable|string|max:255',
            'role' => 'nullable|string',
            'status' => 'nullable',
            'bank_account_number' => 'nullable|string',
            'bank_name' => 'nullable|string',
            'account_holder_name' => 'nullable|string',
        ];

        // Nếu là Thêm mới (POST) thì yêu cầu nhập Password
        if ($this->isMethod('post') && !$this->has('_method')) {
            $rules['password'] = 'required|string|min:6';
        } else {
            // Nếu là Cập nhật (PUT/PATCH) thì Password không bắt buộc
            $rules['password'] = 'nullable|string|min:6';
        }

        return $rules;
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute không được để trống',

            'unique' => ':attribute đã tồn tại',

            'email.email' => ':attribute không đúng định dạng email',

            'full_name.min' => ':attribute phải có độ dài tối thiểu :min ký tự',
            'full_name.max' => ':attribute phải có độ dài tối đa :max ký tự',

            'email.max' => ':attribute phải có độ dài tối đa :max ký tự',

            'email.max' => ':attribute phải có độ dài tối đa :max ký tự',

            'password.min' => ':attribute phải có độ dài tối thiểu :min ký tự',
            'password.max' => ':attribute phải có độ dài tối đa :max ký tự',

            'phone.max' => ':attribute phải có độ dài tối đa :max ký tự',

            'address.max' => ':attribute phải có độ dài tối đa :max ký tự',

            'role.in' => ':attribute không hợp lệ',

            'status.in' => ':attribute chỉ được là 0 hoặc 1',

            'avatar.image' => 'File phải là hình ảnh',

            'avatar.mimes' => 'Chỉ chấp nhận jpg, jpeg, png, webp',

            'avatar.max' => 'Ảnh tối đa 2MB',
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'Họ và tên',



            'email' => 'Email',

            'phone' => 'Số điện thoại',

            'avatar' => 'Ảnh đại diện',

            'address' => 'Địa chỉ',

            'password' => 'Mật khẩu',

            'role' => 'Vai trò',

            'status' => 'Trạng thái',

            'bank_account_number' => 'Số tài khoản ngân hàng',

            'bank_name' => 'Tên ngân hàng',

            'account_holder_name' => 'Tên chủ tài khoản',
        ];
    }
}
