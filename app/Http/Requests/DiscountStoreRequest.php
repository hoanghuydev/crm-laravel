<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DiscountStoreRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:50|unique:discounts,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed_amount',
            'discount_category' => 'required|in:product,payment,customer,seasonal,promotion',
            'value' => 'required|numeric|min:0',
            'min_order_amount' => 'required|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'can_stack' => 'required|boolean',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'required|boolean',
        ];
    }

    /**
     * Get custom validation messages
     */
    public function messages(): array
    {
        return [
            'code.unique' => 'Mã giảm giá đã tồn tại.',
            'type.in' => 'Loại giảm giá phải là phần trăm hoặc số tiền cố định.',
            'discount_category.in' => 'Danh mục giảm giá không hợp lệ.',
            'value.min' => 'Giá trị giảm giá phải lớn hơn 0.',
            'min_order_amount.min' => 'Số tiền đơn hàng tối thiểu phải lớn hơn hoặc bằng 0.',
            'max_discount_amount.min' => 'Số tiền giảm giá tối đa phải lớn hơn hoặc bằng 0.',
            'usage_limit.min' => 'Số lần sử dụng tối đa phải lớn hơn 0.',
            'start_date.after_or_equal' => 'Ngày bắt đầu phải từ hôm nay trở đi.',
            'end_date.after' => 'Ngày kết thúc phải sau ngày bắt đầu.',
        ];
    }

    /**
     * Get custom attribute names
     */
    public function attributes(): array
    {
        return [
            'code' => 'mã giảm giá',
            'name' => 'tên chương trình',
            'description' => 'mô tả',
            'type' => 'loại giảm giá',
            'discount_category' => 'danh mục giảm giá',
            'value' => 'giá trị giảm',
            'min_order_amount' => 'số tiền đơn hàng tối thiểu',
            'max_discount_amount' => 'số tiền giảm giá tối đa',
            'usage_limit' => 'số lần sử dụng tối đa',
            'can_stack' => 'có thể chồng discount',
            'start_date' => 'ngày bắt đầu',
            'end_date' => 'ngày kết thúc',
            'is_active' => 'trạng thái hoạt động',
        ];
    }
}
