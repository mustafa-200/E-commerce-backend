<?php

namespace App\Http\Requests\Brand;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRequest extends FormRequest
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
            'name'          => ['required', 'string', 'max:255'],
            'slug'          => ['nullable', 'string'],
            'logo'          => ['nullable', 'image', 'max:2048'],
            'description'   => ['nullable', 'string'],
            'is_active'     => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'اسم الماركة مطلوب.',
            'logo.image'    => 'الملف المرفوع لازم يكون صورة.',
            'logo.max'      => 'حجم الصورة أكبر من المسموح (2 ميجا).',
        ];
    }   
}