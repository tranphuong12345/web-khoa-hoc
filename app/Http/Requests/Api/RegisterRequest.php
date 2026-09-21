<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'full_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',

            'role' => 'required|in:student,seller,user',

            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',

            // Thông tin ngân hàng dành cho Seller
            'bank_account_number' => 'required_if:role,seller|nullable|string|max:50',
            'bank_name' => 'required_if:role,seller|nullable|string|max:100',
            'account_holder_name' => 'required_if:role,seller|nullable|string|max:255',

            // Thông tin bằng cấp dành cho Seller
            'qualification' => 'required_if:role,seller|nullable|string|max:255',
            'qualification_image' => 'required_if:role,seller|nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }
}