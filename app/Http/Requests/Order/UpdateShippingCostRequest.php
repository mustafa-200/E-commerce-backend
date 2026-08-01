<?php
namespace App\Http\Requests\Order;
use Illuminate\Foundation\Http\FormRequest;
class UpdateShippingCostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // الصلاحية متحققة بالفعل عبر middleware الأدمن
    }
    public function rules(): array
    {
        return [
            'shipping_cost' => ['required', 'numeric', 'min:0'],
        ];
    }
    public function messages(): array
    {
        return [
            'shipping_cost.required' => 'قيمة الشحن مطلوبة.',
            'shipping_cost.numeric' => 'قيمة الشحن لازم تكون رقم.',
            'shipping_cost.min' => 'قيمة الشحن لا يمكن أن تكون سالبة.',
        ];
    }
}