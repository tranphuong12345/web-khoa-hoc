<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Validation rules.
     */
    public function rules(): array
    {
       $categoryId = $this->route('category');

        return [
            'category_name' => [
                'required',
                'string',
                'max:100',
            ],

            'slug' => [
                'required',
                'string',
                'max:150',
                Rule::unique('categories', 'slug')
                    ->ignore($categoryId, 'category_id'),
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'image' => [
                'nullable',
                'string',
                'max:255',
            ],

            'status' => [
                'nullable',
                'integer',
                'in:0,1',
            ],
        ];
    }

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [
            'required' => ':attribute không được để trống',

            'string' => ':attribute phải là chuỗi ký tự',

            'unique' => ':attribute đã tồn tại',

            'max' => ':attribute không được vượt quá :max ký tự',

            'in' => ':attribute không hợp lệ',

            'integer' => ':attribute phải là số',
        ];
    }

    /**
     * Custom attribute names.
     */
    public function attributes(): array
    {
        return [
            'category_name' => 'Tên danh mục',

            'slug' => 'Slug',

            'description' => 'Mô tả',

            'image' => 'Hình ảnh',

            'status' => 'Trạng thái',
        ];
    }
}