<?php

namespace App\Http\Requests\Admin;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CourseRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
   public function rules(): array
    {
        return [
            'category_id' => 'required|exists:categories,category_id', // Kiểm tra danh mục có tồn tại
            'course_name' => 'required|string|max:255',
            'description' => 'required|string',
            'price'       => 'required|numeric|min:0',
            'sale_price'  => 'nullable|numeric|min:0|lt:price', // Giá khuyến mãi phải nhỏ hơn giá gốc (nếu có)
            'level'       => 'required|in:beginner,intermediate,advanced',
            'duration'    => 'nullable|integer|min:0',
            'image'       => 'required|image|mimes:jpeg,png,jpg,webp|max:2048', // Bắt buộc có ảnh đại diện
            'sections'    => 'required|string', // Chuỗi JSON chứa danh sách chương & bài học từ Frontend
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Vui lòng chọn danh mục khóa học.',
            'category_id.exists'   => 'Danh mục không tồn tại trong hệ thống.',
            'course_name.required' => 'Tên khóa học không được để trống.',
            'description.required' => 'Vui lòng nhập mô tả chi tiết cho khóa học.',
            'price.required'       => 'Vui lòng nhập giá học phí.',
            'price.numeric'        => 'Giá học phí phải là định dạng số.',
            'sale_price.lt'        => 'Giá khuyến mãi phải nhỏ hơn giá gốc.',
            'image.required'       => 'Vui lòng tải lên ảnh đại diện cho khóa học.',
            'image.image'          => 'File tải lên phải là hình ảnh.',
            'image.mimes'          => 'Ảnh đại diện phải có định dạng: jpeg, png, jpg, webp.',
            'image.max'            => 'Dung lượng ảnh đại diện không được vượt quá 2MB.',
            'sections.required'    => 'Khóa học phải có ít nhất một chương và bài học.',
        ];
    }
}
